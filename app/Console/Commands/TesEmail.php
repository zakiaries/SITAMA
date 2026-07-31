<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Uji konfigurasi SMTP tanpa harus menjalankan alur reset password.
 * Mencetak konfigurasi yang SEDANG dipakai (password disamarkan), mengirim
 * satu email uji, lalu menerjemahkan error yang umum jadi langkah perbaikan.
 */
class TesEmail extends Command
{
    protected $signature = 'simama:tes-email {email : Alamat tujuan email uji}';

    protected $description = 'Kirim email uji untuk memastikan konfigurasi SMTP sudah benar';

    public function handle(): int
    {
        $tujuan = (string) $this->argument('email');

        $mailer = config('mail.default');
        $smtp   = config('mail.mailers.smtp');

        $this->info('Konfigurasi email yang sedang dipakai:');
        $this->table(['Pengaturan', 'Nilai'], [
            ['MAIL_MAILER', $mailer],
            ['MAIL_HOST', $smtp['host'] ?? '-'],
            ['MAIL_PORT', $smtp['port'] ?? '-'],
            ['MAIL_ENCRYPTION', $smtp['encryption'] ?? '-'],
            ['MAIL_USERNAME', $smtp['username'] ?: '(kosong)'],
            ['MAIL_PASSWORD', $smtp['password'] ? str_repeat('*', 8) . ' (terisi)' : '(KOSONG)'],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
            ['MAIL_FROM_NAME', config('mail.from.name')],
        ]);

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER masih "log" — email TIDAK dikirim ke internet, hanya ditulis ke storage/logs/laravel.log.');
            $this->line('Isi kredensial SMTP di .env lalu jalankan: php artisan config:clear');

            return self::FAILURE;
        }

        if ($mailer === 'smtp' && (empty($smtp['username']) || empty($smtp['password']))) {
            $this->error('MAIL_USERNAME atau MAIL_PASSWORD masih kosong di .env.');

            return self::FAILURE;
        }

        $this->line("Mengirim email uji ke {$tujuan} ...");

        try {
            Mail::raw(
                "Ini email uji dari SIMAMA.\n\n"
                . "Kalau kamu menerima pesan ini, konfigurasi SMTP sudah benar dan fitur\n"
                . "reset password serta aktivasi akun pembimbing industri sudah bisa dipakai.\n\n"
                . 'Dikirim: ' . now()->translatedFormat('d F Y H:i') . ' WIB',
                fn ($m) => $m->to($tujuan)->subject('Uji Konfigurasi Email SIMAMA')
            );
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('GAGAL mengirim email.');
            $this->line($e->getMessage());
            $this->newLine();
            $this->diagnosa($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Email uji terkirim ke {$tujuan}.");
        $this->line('Cek kotak masuk (dan folder Spam). Kalau tidak sampai dalam beberapa menit,');
        $this->line('biasanya penyedia email menolak diam-diam — coba MAIL_FROM_ADDRESS yang sama persis dengan MAIL_USERNAME.');

        return self::SUCCESS;
    }

    /** Terjemahkan error SMTP yang umum menjadi langkah perbaikan. */
    private function diagnosa(string $pesan): void
    {
        $pesan = strtolower($pesan);

        $petunjuk = match (true) {
            str_contains($pesan, 'username and password not accepted'),
            str_contains($pesan, 'authentication failed'),
            str_contains($pesan, '535') => [
                'Kredensial ditolak server email.',
                '• Gmail: WAJIB pakai App Password 16 digit, bukan password akun biasa.',
                '• App Password hanya bisa dibuat setelah Verifikasi 2 Langkah aktif.',
                '• Tulis tanpa spasi, dan bungkus dengan kutip di .env bila ada karakter khusus.',
            ],
            str_contains($pesan, 'connection could not be established'),
            str_contains($pesan, 'connection timed out'),
            str_contains($pesan, 'timed out') => [
                'Tidak bisa menghubungi server SMTP.',
                '• Port keluar mungkin diblokir penyedia VPS — coba port 587 (TLS) atau 465 (SSL).',
                '• Pastikan MAIL_HOST benar dan container punya akses internet.',
            ],
            str_contains($pesan, 'certificate'),
            str_contains($pesan, 'ssl') => [
                'Masalah sertifikat/enkripsi.',
                '• Untuk port 587 pakai MAIL_ENCRYPTION=tls, untuk 465 pakai ssl.',
            ],
            str_contains($pesan, 'sender'),
            str_contains($pesan, 'from address'),
            str_contains($pesan, '553'),
            str_contains($pesan, '550') => [
                'Alamat pengirim ditolak.',
                '• Samakan MAIL_FROM_ADDRESS dengan MAIL_USERNAME (Gmail menolak pengirim lain).',
            ],
            default => [
                'Error tidak dikenali — baca pesan di atas.',
                '• Pastikan sudah menjalankan: php artisan config:clear setelah mengubah .env.',
            ],
        };

        foreach ($petunjuk as $baris) {
            $this->line($baris);
        }
    }
}
