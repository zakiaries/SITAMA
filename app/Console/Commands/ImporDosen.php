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
        {--prodi=semua : ik | tI | semua}
        {--password= : Samakan password semua akun (default: diacak per akun lalu dicetak sekali)}
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
            ['198703272019022012', 'WIKTASARI, S.T., M.Kom'],
        ],
    ];

    private const LABEL = [
        'ik' => 'Teknik Informatika (D3)',
        'ti' => 'Teknologi Rekayasa Komputer (D4)',
    ];

    public function handle(): int
    {
        $pilihan = strtolower((string) $this->option('prodi'));

        if (! in_array($pilihan, ['ik', 'ti', 'semua'], true)) {
            $this->error("Prodi '{$pilihan}' tidak dikenal. Pilih: ik | ti | semua");

            return self::FAILURE;
        }

        $kelompok = $pilihan === 'semua' ? ['ik', 'ti'] : [$pilihan];
        $seragam  = $this->option('password') ?: null;

        $baris = [];
        $dibuat = $dilewati = 0;

        foreach ($kelompok as $kode) {
            foreach (self::DOSEN[$kode] as [$nip, $nama]) {
                $adaAkun = User::where('username', $nip)->exists();

                if ($this->option('pratinjau')) {
                    $baris[] = [$nip, $nama, self::LABEL[$kode], $adaAkun ? 'sudah ada' : 'akan dibuat', '—'];
                    continue;
                }

                if ($adaAkun) {
                    $baris[] = [$nip, $nama, self::LABEL[$kode], 'dilewati (sudah ada)', '—'];
                    $dilewati++;
                    continue;
                }

                $password = $seragam ?: Str::random(12);

                DB::transaction(function () use ($nip, $nama, $password) {
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

                    Lecturer::create(['user_id' => $user->id]);
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
        $this->info("Selesai. {$dibuat} akun dibuat, {$dilewati} dilewati.");

        if ($dibuat > 0 && ! $seragam) {
            $this->warn('Catat kolom Password sekarang — tidak ditampilkan lagi setelah ini.');
        }

        $this->newLine();
        $this->line('Catatan: alamat email masih placeholder @simama.local, jadi fitur lupa password');
        $this->line('belum bisa dipakai dosen. Isi email asli lewat portal Kaprodi bila sudah tersedia,');
        $this->line('atau reset password dosen dari menu Data Dosen.');

        return self::SUCCESS;
    }
}
