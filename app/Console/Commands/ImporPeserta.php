<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Impor peserta magang dari CSV plotting resmi prodi.
 *
 * Kaprodi tak punya layar untuk membuat akun mahasiswa — satu-satunya jalur
 * adalah mahasiswa mendaftar sendiri lalu disetujui. Untuk uji coba dengan
 * puluhan peserta sekaligus itu tak praktis, dan daftar plotting dosen
 * pembimbingnya sudah ditetapkan prodi, bukan hasil pendaftaran.
 *
 * Perintah ini membuat semuanya sekaligus: akun mahasiswa (langsung aktif,
 * dospem sudah terplot), akun dosen yang belum ada, dan data magang bagi yang
 * tempatnya sudah pasti.
 *
 * SENGAJA TIDAK mengirim email apa pun. Pembimbing industri di daftar ini orang
 * sungguhan yang belum tentu tahu namanya tercatat; mengirimi mereka undangan
 * otomatis bukan keputusan yang boleh diambil sebuah skrip impor.
 *
 * Idempoten: dijalankan ulang memperbarui data, tidak menggandakan. Kata sandi
 * hanya dibuat saat akun pertama kali lahir — menjalankan ulang tak mengacak
 * ulang sandi yang sudah dibagikan.
 *
 * Format CSV (header wajib persis):
 *   nim,nama,kelas,dospem,perusahaan,kota,mulai,selesai,bidang,pembimbing_industri
 * Kolom mulai/selesai berformat YYYY-MM-DD dan boleh kosong — bila kosong,
 * magangnya tidak dicatat dan mahasiswa mengajukan sendiri lewat aplikasi.
 */
class ImporPeserta extends Command
{
    protected $signature = 'simama:impor-peserta
        {--berkas= : Jalur berkas CSV (wajib)}
        {--prodi=Teknologi Rekayasa Komputer : Program studi untuk semua mahasiswa}
        {--jurusan=Teknik Elektro : Jurusan}
        {--tahun=2026/2027 : Tahun akademik}
        {--password= : Samakan kata sandi semua akun baru (default: acak per akun)}
        {--email-kampus= : Susun email kampus dari nama+NIM, mis. mhs.polines.ac.id}
        {--pratinjau : Tampilkan rencananya saja, tidak menulis apa pun}';

    protected $description = 'Impor peserta magang dari CSV plotting prodi (akun mahasiswa + dosen + data magang)';

    private const KOLOM = [
        'nim', 'nama', 'kelas', 'dospem', 'perusahaan',
        'kota', 'mulai', 'selesai', 'bidang', 'pembimbing_industri',
    ];

    /** Gelar & singkatan yang dibuang saat mencocokkan nama dosen. */
    private const GELAR = [
        'DR', 'IR', 'DRS', 'PHD', 'SKOM', 'MKOM', 'ST', 'MT', 'MENG', 'MCS',
        'MSC', 'SST', 'SH', 'MH', 'S', 'M', 'KOM', 'ENG', 'CS', 'SC', 'PH', 'D',
    ];

    /** @var array<string, int> nama ternormalisasi => lecturers.id */
    private array $cacheDosen = [];

    private array $ringkas = [
        'mhs_baru' => 0, 'mhs_diperbarui' => 0,
        'dosen_baru' => 0, 'industri_baru' => 0,
        'magang' => 0, 'tanpa_magang' => 0,
    ];

    public function handle(): int
    {
        $berkas = (string) $this->option('berkas');

        if ($berkas === '' || ! is_readable($berkas)) {
            $this->error("Berkas CSV tidak ditemukan atau tak bisa dibaca: '{$berkas}'");
            $this->line('Contoh: php artisan simama:impor-peserta --berkas=storage/app/peserta.csv');

            return self::FAILURE;
        }

        $baris = $this->bacaCsv($berkas);
        if ($baris === null) {
            return self::FAILURE;
        }

        $this->info(count($baris) . ' baris terbaca dari ' . basename($berkas) . '.');

        if ($this->option('pratinjau')) {
            return $this->pratinjau($baris);
        }

        $kredensial = [];

        foreach ($baris as $row) {
            DB::transaction(function () use ($row, &$kredensial) {
                $dosenId = $this->pastikanDosen($row['dospem'], 'lecturer');
                $sandi   = $this->pastikanMahasiswa($row, $dosenId);

                if ($sandi !== null) {
                    $kredensial[] = [$row['nim'], $row['nama'], $row['kelas'], $sandi];
                }

                $this->catatMagang($row, $dosenId);
            });
        }

        $this->laporkan($kredensial);

        return self::SUCCESS;
    }

    /* ─────────────────────────── membaca berkas ─────────────────────────── */

    /** @return array<int, array<string, string>>|null */
    private function bacaCsv(string $berkas): ?array
    {
        $f = fopen($berkas, 'r');
        $header = fgetcsv($f);

        if ($header === false) {
            $this->error('Berkas CSV kosong.');
            fclose($f);

            return null;
        }

        $header = array_map(fn ($h) => strtolower(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF")), $header);
        $kurang = array_diff(self::KOLOM, $header);

        if ($kurang !== []) {
            $this->error('Kolom wajib tidak ada: ' . implode(', ', $kurang));
            $this->line('Header yang dibutuhkan: ' . implode(',', self::KOLOM));
            fclose($f);

            return null;
        }

        $baris = [];
        while (($data = fgetcsv($f)) !== false) {
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $i => $nama) {
                $row[$nama] = trim((string) ($data[$i] ?? ''));
            }

            if ($row['nim'] === '' || $row['nama'] === '') {
                $this->warn('Baris tanpa NIM/nama dilewati.');
                continue;
            }

            $baris[] = $row;
        }
        fclose($f);

        return $baris;
    }

    /* ──────────────────────────── pencocokan ────────────────────────────── */

    /**
     * Kunci pembanding nama: huruf besar, tanpa tanda baca dan gelar.
     * "DR. SUKAMTO, S.KOM., M.T." dan "SUKAMTO, S.Kom., M.T." jadi sama.
     *
     * @return array<int, string>
     */
    private function kunciNama(string $nama): array
    {
        $bersih = preg_replace('/[^A-Z ]/', ' ', strtoupper($nama));
        $kata   = preg_split('/\s+/', trim($bersih), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(
            $kata,
            fn ($k) => strlen($k) >= 3 && ! in_array($k, self::GELAR, true)
        )));
    }

    /**
     * Dua nama dianggap orang yang sama bila salah satu himpunan katanya
     * termuat di yang lain, atau berbagi minimal dua kata. Ambang dua kata
     * mencegah "ANGGA WAHYU WIBOWO" tertukar dengan "WAHYU SULISTIYO" yang
     * hanya berbagi satu kata.
     */
    private function orangSama(array $a, array $b): bool
    {
        if ($a === [] || $b === []) {
            return false;
        }

        $sama = count(array_intersect($a, $b));

        return $sama === count($a) || $sama === count($b) || $sama >= 2;
    }

    /** Cari dosen yang sudah ada berdasarkan nama; buat bila belum ada. */
    private function pastikanDosen(string $nama, string $peran): ?int
    {
        $nama = trim($nama);
        if ($nama === '') {
            return null;
        }

        $kunci = implode(' ', $this->kunciNama($nama)) . '|' . $peran;
        if (isset($this->cacheDosen[$kunci])) {
            return $this->cacheDosen[$kunci];
        }

        $target = $this->kunciNama($nama);

        foreach (Lecturer::with('user')->get() as $l) {
            if ($l->user && $l->user->role === $peran && $this->orangSama($target, $this->kunciNama($l->user->name))) {
                return $this->cacheDosen[$kunci] = $l->id;
            }
        }

        $username = $this->usernameUnik($target, $peran === 'lecturer' ? '' : 'pic.');

        $user = User::create([
            'name'     => $nama,
            'username' => $username,
            // Email asli tak ada di daftar plotting. Placeholder dipakai agar akun
            // tetap bisa dibuat; selama masih placeholder, reset mandiri lewat
            // email TIDAK akan sampai — Kaprodi yang mereset lewat portal.
            'email'    => $username . '@simama.local',
            'password' => Hash::make($this->option('password') ?: Str::random(14)),
            'role'     => $peran,
            'is_activated' => true,
        ]);

        $this->ringkas[$peran === 'lecturer' ? 'dosen_baru' : 'industri_baru']++;

        return $this->cacheDosen[$kunci] = Lecturer::create(['user_id' => $user->id])->id;
    }

    private function usernameUnik(array $kataNama, string $awalan): string
    {
        $dasar = $awalan . Str::slug(implode(' ', array_slice($kataNama, 0, 3)), '.');
        $dasar = $dasar !== $awalan ? $dasar : $awalan . 'dosen';

        $username = $dasar;
        $n = 2;
        while (User::where('username', $username)->exists()) {
            $username = $dasar . $n++;
        }

        return $username;
    }

    /* ───────────────────────────── penulisan ────────────────────────────── */

    /** @return string|null kata sandi bila akunnya baru dibuat */
    private function pastikanMahasiswa(array $row, ?int $dosenId): ?string
    {
        $user = User::where('username', $row['nim'])->first();

        if ($user) {
            $user->update(['name' => $row['nama']]);
            $sandi = null;
            $this->ringkas['mhs_diperbarui']++;
        } else {
            $sandi = $this->option('password') ?: $this->sandiMudah();
            $user  = User::create([
                'name'         => $row['nama'],
                'username'     => $row['nim'],
                'email'        => $this->email($row),
                'password'     => Hash::make($sandi),
                'role'         => 'student',
                'is_activated' => true,
            ]);
            $this->ringkas['mhs_baru']++;
        }

        Student::updateOrCreate(
            ['user_id' => $user->id],
            [
                'the_class'     => $row['kelas'] ?: null,
                'study_program' => (string) $this->option('prodi'),
                'major'         => (string) $this->option('jurusan'),
                'academic_year' => (string) $this->option('tahun'),
                // Langsung aktif: plotting dari prodi, bukan pendaftaran yang
                // masih perlu ditimbang Kaprodi.
                'status'        => 'active',
                'lecturer_id'   => $dosenId,
            ]
        );

        return $sandi;
    }

    private function catatMagang(array $row, ?int $dosenId): void
    {
        $student = Student::whereHas('user', fn ($q) => $q->where('username', $row['nim']))->first();

        if (! $student || $row['mulai'] === '' || $row['selesai'] === '' || $row['perusahaan'] === '') {
            $this->ringkas['tanpa_magang']++;

            return;
        }

        $company = Company::firstOrCreate(
            ['name' => $row['perusahaan']],
            ['address' => $row['kota'] ?: null, 'verification_status' => 'verified']
        );

        // Pembimbing industri boleh kosong — banyak mahasiswa belum menerima
        // namanya dari perusahaan. Magangnya tetap dicatat; Kaprodi menugaskan
        // pembimbingnya belakangan lewat portal.
        $industriId = $row['pembimbing_industri'] !== ''
            ? $this->pastikanDosen($row['pembimbing_industri'], 'lecturer_industry')
            : null;

        Internship::updateOrCreate(
            ['student_id' => $student->id],
            [
                'lecturer_id'          => $dosenId,
                'company_id'           => $company->id,
                'lecturer_industry_id' => $industriId,
                'position'             => $row['bidang'] ?: null,
                'start_date'           => $row['mulai'],
                'end_date'             => $row['selesai'],
                'is_finished'          => false,
                'finish_requested'     => false,
            ]
        );

        $this->ringkas['magang']++;
    }

    /**
     * Alamat email akun mahasiswa.
     *
     * Daftar plotting prodi hanya memuat nomor HP, tak ada satu pun email, jadi
     * alamatnya harus disusun sendiri.
     *
     * Bawaannya placeholder @simama.local: pengiriman ke sana mustahil, jadi tak
     * mungkin ada surat nyasar. Konsekuensinya "Lupa Password" tak berfungsi dan
     * Kaprodi yang mereset.
     *
     * Dengan --email-kampus, alamatnya disusun mengikuti pola kampus
     * "namadepan.nimtanpatitik@domain" (mis. lanang.33423212@mhs.polines.ac.id)
     * sehingga reset mandiri lewat email bisa dipakai. Ini AMAN meski polanya
     * ternyata meleset: NIM ikut di dalam alamat dan NIM itu unik, jadi alamat
     * yang salah paling banter memantul — ia tak mungkin jatuh ke kotak masuk
     * mahasiswa lain.
     */
    private function email(array $row): string
    {
        $nim = str_replace('.', '', $row['nim']);
        $domain = trim((string) $this->option('email-kampus'));

        if ($domain === '') {
            return $nim . '@simama.local';
        }

        return $this->namaDepan($row['nama']) . '.' . $nim . '@' . ltrim($domain, '@');
    }

    /**
     * Nama depan untuk alamat email. Inisial dilewati — "M. MUCHLAS HUDAWAN"
     * memberi "muchlas", bukan "m", karena alamat kampus tak memakai inisial.
     */
    private function namaDepan(string $nama): string
    {
        foreach (preg_split('/\s+/', trim($nama), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $kata) {
            $bersih = preg_replace('/[^a-z]/', '', strtolower($kata));

            if (strlen($bersih) >= 2) {
                return $bersih;
            }
        }

        return 'mahasiswa';
    }

    /** Kata sandi yang mudah ditulis tangan di kertas dan diketik ulang. */
    private function sandiMudah(): string
    {
        $kata = ['biru', 'hijau', 'merah', 'kuning', 'ungu', 'batu', 'kayu', 'besi',
                 'awan', 'hujan', 'angin', 'gunung', 'pantai', 'sungai', 'hutan',
                 'taman', 'kebun', 'rusa', 'elang', 'kupu', 'panda', 'mangga',
                 'melon', 'anggur', 'kelapa', 'pisang', 'jeruk', 'salak'];

        return $kata[random_int(0, count($kata) - 1)] . '-'
             . $kata[random_int(0, count($kata) - 1)] . '-'
             . random_int(23, 98);
    }

    /* ───────────────────────────── keluaran ─────────────────────────────── */

    private function pratinjau(array $baris): int
    {
        $dosen = [];
        $magang = 0;

        foreach ($baris as $row) {
            $dosen[$row['dospem']] = ($dosen[$row['dospem']] ?? 0) + 1;
            if ($row['mulai'] !== '' && $row['perusahaan'] !== '') {
                $magang++;
            }
        }

        $this->newLine();
        $this->table(['Dosen pembimbing', 'Mahasiswa'],
            collect($dosen)->map(fn ($n, $d) => [$d, $n])->values()->all());

        $this->newLine();
        $this->line('Akan dibuat/diperbarui : ' . count($baris) . ' akun mahasiswa');
        $this->line('Magang dicatat         : ' . $magang);
        $this->line('Tanpa magang           : ' . (count($baris) - $magang) . ' (mengajukan sendiri lewat aplikasi)');
        $this->line('Program studi          : ' . $this->option('prodi') . ' / ' . $this->option('jurusan'));
        $this->line('Tahun akademik         : ' . $this->option('tahun'));
        $this->newLine();
        $this->info('Mode pratinjau — tidak ada yang ditulis ke basis data.');

        return self::SUCCESS;
    }

    private function laporkan(array $kredensial): void
    {
        $this->newLine();

        if ($kredensial !== []) {
            $this->table(['NIM (username)', 'Nama', 'Kelas', 'Kata sandi'], $kredensial);
            $this->warn('Kata sandi di atas HANYA ditampilkan sekali. Simpan sebelum menutup terminal.');
        }

        $this->newLine();
        $this->table(['Hasil', 'Jumlah'], [
            ['Akun mahasiswa baru',        $this->ringkas['mhs_baru']],
            ['Akun mahasiswa diperbarui',  $this->ringkas['mhs_diperbarui']],
            ['Akun dosen baru',            $this->ringkas['dosen_baru']],
            ['Akun pembimbing industri',   $this->ringkas['industri_baru']],
            ['Magang tercatat',            $this->ringkas['magang']],
            ['Tanpa magang',               $this->ringkas['tanpa_magang']],
        ]);

        if ($this->ringkas['industri_baru'] > 0) {
            $this->newLine();
            $this->line('Akun pembimbing industri dibuat TANPA mengirim email undangan.');
            $this->line('Bagikan kredensialnya sendiri, atau reset lewat portal Kaprodi.');
        }

        $this->newLine();

        if (trim((string) $this->option('email-kampus')) !== '') {
            $this->line('Email disusun mengikuti pola kampus, jadi "Lupa Password" bisa dipakai.');
            $this->line('Bila ada alamat yang ternyata meleset, suratnya memantul (tak nyasar ke');
            $this->line('orang lain karena NIM ikut di dalam alamat) — mahasiswa tinggal');
            $this->line('membetulkannya sendiri di menu Profil, atau minta Kaprodi mereset.');

            return;
        }

        $this->warn('Daftar plotting tak memuat email, jadi semua akun memakai alamat');
        $this->warn('placeholder @simama.local. Akibatnya "Lupa Password" TIDAK akan sampai.');
        $this->line('Dua jalan yang tersedia:');
        $this->line('  1. Kaprodi mereset kata sandi lewat Data Mahasiswa (tanpa perlu email).');
        $this->line('  2. Mahasiswa mengisi email aslinya sendiri di menu Profil setelah masuk,');
        $this->line('     setelah itu reset mandiri lewat email berfungsi seperti biasa.');
        $this->line('Pakai --email-kampus=mhs.polines.ac.id bila ingin alamat kampus disusun otomatis.');
    }
}
