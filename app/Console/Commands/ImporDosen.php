<?php

namespace App\Console\Commands;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Membuat akun dosen untuk dua program studi yang menjadi lingkup penggunaan
 * SIMAMA: Teknik Informatika (D3) dan Teknologi Rekayasa Komputer (D4).
 *
 * Daftar nama & NIP disalin apa adanya dari halaman resmi Jurusan Teknik
 * Elektro Polines (https://elektro.polines.ac.id/dosen-elektro), kolom PRODI
 * "IK" dan "TI". NIP dipakai sebagai username, sejalan dengan NIM untuk
 * mahasiswa.
 *
 * Idempoten: NIP yang sudah punya akun dilewati, tidak ditimpa.
 */
class ImporDosen extends Command
{
    protected $signature = 'simama:impor-dosen
        {--prodi=semua : ik | ti | semua}
        {--password= : Samakan password semua akun (default: diacak per akun lalu dicetak sekali)}
        {--mudah : Password acak yang mudah diketik & disalin tangan (kata-kata-angka), tetap unik per dosen}
        {--setel-ulang : Setel ulang password akun yang SUDAH ada (bukan hanya membuat yang baru)}
        {--pratinjau : Tampilkan daftar saja, tanpa membuat akun}';

    protected $description = 'Buat akun dosen prodi Teknik Informatika (D3) & Teknologi Rekayasa Komputer (D4)';

    /** [NIP, Nama] — disalin apa adanya dari laman resmi jurusan. */
    private const DOSEN = [
        'ik' => [
            ['199004112019031014', 'AFANDI NUR AZIZ THOHARI, S.T., M.Cs'],
            ['198605292019032009', 'AISYATUL KARIMA, S.Kom., MCS'],
            ['198810142019031007', 'AMRAN YOBIOKTABERA, M. KOM'],
            ['199202052019031009', 'ANGGA WAHYU WIBOWO, S.Kom., M.Eng'],
            ['197711192008012013', 'IDHAWATI H., S.Kom., M.Kom.'],
            ['198404202015041003', 'LILIEK TRIYONO, S.T., M.Kom'],
            ['196008221988031001', 'PARSUMO RAHARDJO, Drs., M.Kom.'],
            ['199401272019032036', 'SIRLI FAHRIAH, S.Kom, M.Kom.'],
            ['197501302001121001', 'SLAMET HANDOKO, S.Kom., M.Kom.'],
            ['197101172003121001', 'SUKAMTO, S.Kom., M.T.'],
            ['197704012005011001', 'WAHYU SULISTIYO, S.T., M.Kom.'],
        ],
        'ti' => [
            ['197904262003122002', 'KURNIANINGSIH, S.T., M.T., Dr.'],
            ['198407192019031008', 'KUWAT SANTOSO, M. KOM'],
            ['197403112000121001', 'MARDIYONO, S.Kom., M.Sc.'],
            ['199001072019031020', 'MUHAMMAD IRWAN YANWARI, S.Kom., M.Eng.'],
            ['199107302019031010', 'NURSENO BAYU AJI, S.Kom., M.Kom.'],
            ['198504102014041002', 'PRAYITNO, S.ST., M.T.'],
            ['196810252000121001', 'TRI RAHARJO YUDANTORO, S.Kom., M.Kom.'],
            // TMT-nya 201903, bukan 201902 — dicocokkan ke profil resmi di
            // web.polines.ac.id/id/sdm. NIP dipakai sebagai username login, jadi
            // satu digit meleset membuat dosennya tak bisa masuk.
            ['198703272019032012', 'WIKTASARI, S.T., M.Kom'],
        ],
    ];

    private const LABEL = [
        'ik' => 'Teknik Informatika (D3)',
        'ti' => 'Teknologi Rekayasa Komputer (D4)',
    ];

    /** Nilai yang DISIMPAN di lecturers.study_program (label di atas untuk layar). */
    private const PRODI = [
        'ik' => 'Teknik Informatika',
        'ti' => 'Teknologi Rekayasa Komputer',
    ];

    /**
     * Kata-kata pendek tanpa ejaan rancu, dipakai menyusun password yang mudah
     * ditulis tangan dan diketik ulang. Sengaja tidak memakai huruf/angka yang
     * gampang tertukar (l, 1, I, O, 0) seperti pada password acak biasa.
     */
    private const KATA = [
        'biru', 'hijau', 'merah', 'kuning', 'ungu', 'jingga', 'putih', 'hitam',
        'batu', 'kayu', 'besi', 'kaca', 'awan', 'hujan', 'angin', 'embun',
        'gunung', 'pantai', 'sungai', 'danau', 'hutan', 'taman', 'kebun', 'sawah',
        'kuda', 'rusa', 'merpati', 'elang', 'kupu', 'lebah', 'harimau', 'panda',
        'mangga', 'jambu', 'melon', 'anggur', 'kelapa', 'pisang', 'jeruk', 'salak',
    ];

    private function passwordAcak(): string
    {
        if (! $this->option('mudah')) {
            return Str::random(12);
        }

        $a = self::KATA[random_int(0, count(self::KATA) - 1)];
        $b = self::KATA[random_int(0, count(self::KATA) - 1)];

        return $a . '-' . $b . '-' . random_int(23, 98);
    }

    public function handle(): int
    {
        $pilihan = strtolower((string) $this->option('prodi'));

        if (! in_array($pilihan, ['ik', 'ti', 'semua'], true)) {
            $this->error("Prodi '{$pilihan}' tidak dikenal. Pilih: ik | ti | semua");

            return self::FAILURE;
        }

        $kelompok = $pilihan === 'semua' ? ['ik', 'ti'] : [$pilihan];
        $seragam  = $this->option('password') ?: null;

        if ($seragam !== null && strlen($seragam) < 6) {
            $this->error('Password minimal 8 karakter (mengikuti aturan form Tambah Dosen).');

            return self::FAILURE;
        }

        $baris = [];
        $dibuat = $dilewati = $disetel = 0;

        foreach ($kelompok as $kode) {
            foreach (self::DOSEN[$kode] as [$nip, $nama]) {
                $akun = User::where('username', $nip)->first();

                if ($this->option('pratinjau')) {
                    $rencana = $akun
                        ? ($this->option('setel-ulang') ? 'password disetel ulang' : 'sudah ada')
                        : 'akan dibuat';
                    $baris[] = [$nip, $nama, self::LABEL[$kode], $rencana, '—'];
                    continue;
                }

                // Akun sudah ada: setel ulang passwordnya bila diminta, kalau tidak lewati.
                if ($akun) {
                    // Prodi TETAP diselaraskan meski akunnya dilewati. Kolomnya
                    // baru ada belakangan, jadi akun lama lahir tanpa prodi —
                    // menjalankan ulang perintah ini adalah cara mengisinya
                    // tanpa SQL manual.
                    if ($akun->lecturer) {
                        $akun->lecturer->update(['study_program' => self::PRODI[$kode]]);
                    }

                    if (! $this->option('setel-ulang')) {
                        $baris[] = [$nip, $nama, self::LABEL[$kode], 'dilewati (sudah ada)', '—'];
                        $dilewati++;
                        continue;
                    }

                    $password = $seragam ?: $this->passwordAcak();
                    $akun->update(['password' => Hash::make($password)]);

                    $baris[] = [$nip, $nama, self::LABEL[$kode], 'password disetel ulang', $password];
                    $disetel++;
                    continue;
                }

                $password = $seragam ?: Str::random(12);

                DB::transaction(function () use ($nip, $nama, $password, $kode) {
                    $user = User::create([
                        'name'         => $nama,
                        'username'     => $nip,
                        // Email asli belum diketahui. Placeholder dipakai agar akun tetap
                        // bisa dibuat; selama masih placeholder, reset password mandiri
                        // TIDAK akan sampai — Kaprodi yang mereset lewat portal.
                        'email'        => $nip . '@simama.local',
                        'password'     => Hash::make($password),
                        'role'         => 'lecturer',
                        'is_activated' => true,
                    ]);

                    Lecturer::create([
                        'user_id'       => $user->id,
                        'study_program' => self::PRODI[$kode],
                    ]);
                });

                $baris[] = [$nip, $nama, self::LABEL[$kode], 'dibuat', $password];
                $dibuat++;
            }
        }

        $this->newLine();
        $this->table(['NIP (username)', 'Nama', 'Program Studi', 'Status', 'Password'], $baris);

        if ($this->option('pratinjau')) {
            $this->newLine();
            $this->info('Mode pratinjau — tidak ada akun yang dibuat.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("Selesai. {$dibuat} akun dibuat, {$disetel} password disetel ulang, {$dilewati} dilewati.");

        if (($dibuat > 0 || $disetel > 0) && ! $seragam) {
            $this->warn('Catat kolom Password sekarang — tidak ditampilkan lagi setelah ini.');
        }

        $this->newLine();
        $this->line('Catatan: alamat email masih placeholder @simama.local, jadi fitur lupa password');
        $this->line('belum bisa dipakai dosen. Isi email asli lewat portal Kaprodi bila sudah tersedia,');
        $this->line('atau reset password dosen dari menu Data Dosen.');

        return self::SUCCESS;
    }
}
