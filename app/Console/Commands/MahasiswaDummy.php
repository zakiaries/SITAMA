<?php

namespace App\Console\Commands;

use App\Models\AssessmentComponent;
use App\Models\Company;
use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\Lecturer;
use App\Models\LogBook;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyiapkan akun mahasiswa DUMMY untuk demo/uji, lengkap dengan data
 * pendukungnya (magang, logbook, bimbingan, laporan, sertifikat, nilai).
 *
 * Idempoten: dijalankan ulang akan MENYETEL ULANG data aktivitas milik akun
 * dummy ini ke tahap yang diminta, jadi satu akun bisa dipakai bolak-balik
 * untuk menguji tahap yang berbeda. Data mahasiswa lain tidak disentuh.
 */
class MahasiswaDummy extends Command
{
    protected $signature = 'simama:mahasiswa-dummy
        {--nim=3.34.23.2.99 : NIM sekaligus username untuk login}
        {--nama=dummy : Nama tampilan}
        {--kelas=IK-3C : Kelas}
        {--prodi=Teknik Informatika : Program studi}
        {--jurusan=Teknik Elektro : Jurusan}
        {--tahun=2023/2024 : Tahun akademik}
        {--tahap=sedang-magang : sedang-magang | siap-dinilai | siap-selesai | selesai}
        {--dospem= : Username dosen pembimbing (default: dosen pertama yang ada)}
        {--password= : Password akun dummy — berlaku juga untuk pembimbing industri dummy (default: diacak lalu dicetak sekali)}
        {--hapus : Hapus akun dummy beserta seluruh data pendukungnya}
        {--force : Lewati konfirmasi saat menghapus}';

    protected $description = 'Siapkan akun mahasiswa dummy untuk demo/uji (4 tahap: sedang-magang, siap-dinilai, siap-selesai, selesai)';

    /**
     * Tahapnya mengikuti urutan alur nyata, dan 'siap-dinilai' ada karena
     * penilaian kini digerbangi kelengkapan mahasiswa: tanpa tahap ini tak ada
     * satu pun akun uji yang bisa dipakai pembimbing untuk MENGISI nilai dari
     * kosong — 'sedang-magang' tergerbang, 'siap-selesai' nilainya sudah ada,
     * 'selesai' terkunci.
     */
    private const TAHAP = ['sedang-magang', 'siap-dinilai', 'siap-selesai', 'selesai'];

    public function handle(): int
    {
        $nim = (string) $this->option('nim');

        if ($this->option('hapus')) {
            return $this->hapus($nim);
        }

        $tahap = (string) $this->option('tahap');
        if (! in_array($tahap, self::TAHAP, true)) {
            $this->error("Tahap '{$tahap}' tidak dikenal. Pilih: " . implode(' | ', self::TAHAP));

            return self::FAILURE;
        }

        $dospem = $this->cariDospem();
        if (! $dospem) {
            $this->error('Tidak ada akun dosen pembimbing di sistem. Buat dosen dulu lewat portal Kaprodi.');

            return self::FAILURE;
        }

        [$user, $passwordBaru] = $this->siapkanAkun($nim);

        $student = $this->siapkanStudent($user, $dospem);
        $this->bersihkanDataAktivitas($student);

        $internship = $this->siapkanMagang($student, $dospem);

        $this->isiLogbook($student, $internship);
        $this->isiBimbingan($student);

        // Laporan akhir di-ACC = syarat kedua (setelah logbook) agar pembimbing
        // boleh menilai. Lihat Internship::syaratPenilaian().
        if ($tahap !== 'sedang-magang') {
            $this->isiLaporan($student);
        }

        // Sertifikat & nilai sengaja BELUM ada di tahap 'siap-dinilai': itulah
        // yang membuatnya berguna. Sertifikat memang bukan syarat penilaian —
        // perusahaan sering menerbitkannya belakangan — jadi tahap ini juga
        // menggambarkan keadaan yang lazim di lapangan.
        if (! in_array($tahap, ['sedang-magang', 'siap-dinilai'], true)) {
            $this->isiSertifikat($internship);
            $this->isiNilai($internship);
        }

        $internship->update([
            'is_finished'      => $tahap === 'selesai',
            'finish_requested' => false,
        ]);

        $this->laporkan($user, $student, $internship, $dospem, $tahap, $passwordBaru);

        return self::SUCCESS;
    }

    /* ───────────────────────── akun & profil ───────────────────────── */

    private function cariDospem(): ?Lecturer
    {
        if ($username = $this->option('dospem')) {
            $u = User::where('username', $username)->where('role', 'lecturer')->first();

            if (! $u || ! $u->lecturer) {
                $this->error("Dosen dengan username '{$username}' tidak ditemukan.");

                return null;
            }

            return $u->lecturer;
        }

        return Lecturer::whereHas('user', fn ($q) => $q->where('role', 'lecturer'))->orderBy('id')->first();
    }

    /** @return array{0: User, 1: ?string} user + password baru (null bila tak diubah) */
    private function siapkanAkun(string $nim): array
    {
        $user = User::where('username', $nim)->first();

        // Password hanya di-set saat akun baru dibuat atau bila diminta eksplisit,
        // supaya menjalankan ulang command tidak mengunci akun yang sudah dipakai.
        $passwordBaru = $this->option('password') ?: null;

        if (! $user) {
            $passwordBaru ??= Str::random(14);
            $user = User::create([
                'name'     => (string) $this->option('nama'),
                'username' => $nim,
                'email'    => Str::slug($nim, '_') . '@simama.local',
                'password' => Hash::make($passwordBaru),
                'role'     => 'student',
            ]);
        } else {
            $data = ['name' => (string) $this->option('nama')];
            if ($passwordBaru) {
                $data['password'] = Hash::make($passwordBaru);
            }
            $user->update($data);
        }

        return [$user, $passwordBaru];
    }

    private function siapkanStudent(User $user, Lecturer $dospem): Student
    {
        $student = Student::firstOrNew(['user_id' => $user->id]);

        $student->fill([
            'the_class'     => (string) $this->option('kelas'),
            'study_program' => (string) $this->option('prodi'),
            'major'         => (string) $this->option('jurusan'),
            'academic_year' => (string) $this->option('tahun'),
            'status'        => 'active',
            'lecturer_id'   => $dospem->id,
        ])->save();

        return $student->refresh();
    }

    /** Hapus HANYA data aktivitas milik dummy ini, agar tahap bisa disetel ulang. */
    private function bersihkanDataAktivitas(Student $student): void
    {
        foreach ($student->guidances as $g) {
            if ($g->name_file) {
                Storage::disk('public')->delete($g->name_file);
            }
        }
        $student->guidances()->delete();
        $student->logBooks()->delete();

        foreach (InternshipReport::where('student_id', $student->id)->get() as $r) {
            if ($r->file_path) {
                Storage::disk('public')->delete($r->file_path);
            }
            $r->delete();
        }

        foreach ($student->internships as $i) {
            if ($i->certificate_path) {
                Storage::disk('public')->delete($i->certificate_path);
            }
            StudentScore::where('internship_id', $i->id)->delete();
        }
    }

    /* ───────────────────────── magang ───────────────────────── */

    private function siapkanMagang(Student $student, Lecturer $dospem): Internship
    {
        $company = Company::firstOrCreate(
            ['name' => 'PT Dummy Teknologi (data uji)'],
            ['verification_status' => 'verified']
        );

        $pembimbingIndustri = $this->siapkanPembimbingIndustri();

        $internship = $student->internships()->latest()->first() ?? new Internship(['student_id' => $student->id]);

        $internship->fill([
            'student_id'           => $student->id,
            'lecturer_id'          => $dospem->id,
            'company_id'           => $company->id,
            'lecturer_industry_id' => $pembimbingIndustri->id,
            'position'             => 'Junior Web Developer',
            'start_date'           => now()->subMonths(3)->startOfMonth()->toDateString(),
            'end_date'             => now()->toDateString(),
            'certificate_path'     => null,
            'performance_notes'    => null,
        ])->save();

        return $internship->refresh();
    }

    private function siapkanPembimbingIndustri(): Lecturer
    {
        $user = User::where('username', 'industri_dummy')->first();

        // --password ikut dipakai di sini supaya penguji bisa login sebagai
        // pembimbing industri (mengomentari logbook & mengisi nilai) tanpa
        // perlu mencatat dua password berbeda.
        $password = $this->option('password') ?: null;

        if (! $user) {
            $password ??= Str::random(14);
            $user = User::create([
                'name'         => 'Pembimbing Industri Dummy',
                'username'     => 'industri_dummy',
                'email'        => 'industri_dummy@simama.local',
                'password'     => Hash::make($password),
                'role'         => 'lecturer_industry',
                'is_activated' => true,
            ]);
            $this->warn("Akun pembimbing industri dummy dibuat — username: industri_dummy | password: {$password}");
        } elseif ($password) {
            $user->update(['password' => Hash::make($password), 'is_activated' => true]);
            $this->line("Password pembimbing industri dummy (industri_dummy) disetel ulang ke password yang sama.");
        }

        return $user->lecturer ?? Lecturer::create(['user_id' => $user->id]);
    }

    /* ───────────────────────── data aktivitas ───────────────────────── */

    private function isiLogbook(Student $student, Internship $internship): void
    {
        $kegiatan = [
            'Orientasi perusahaan dan pengenalan tim',
            'Setup lingkungan kerja dan akses repositori',
            'Mempelajari alur bisnis aplikasi internal',
            'Membuat halaman daftar produk',
            'Perbaikan bug pada form pendaftaran',
            'Diskusi kebutuhan fitur dengan pembimbing',
            'Implementasi validasi input',
            'Menyusun query laporan penjualan',
            'Optimasi tampilan untuk layar kecil',
            'Menulis dokumentasi API internal',
        ];

        $total = Internship::MIN_LOGBOOK;

        for ($i = 0; $i < $total; $i++) {
            $tanggal = now()->subDays($total - $i)->toDateString();

            LogBook::create([
                'student_id'    => $student->id,
                'title'         => 'Hari ke-' . ($i + 1),
                'activity'      => $kegiatan[$i % count($kegiatan)] . '.',
                'date'          => $tanggal,
                // Sebagian sengaja belum dikomentari agar filter "Belum Dikomen"
                // di portal dosen & industri ada isinya saat didemokan.
                'note'          => $i % 3 === 0 ? 'Catatan dosen: lanjutkan, sudah sesuai rencana.' : null,
                'industry_note' => $i % 2 === 0 ? 'Komentar industri: hasil kerja rapi dan tepat waktu.' : null,
            ]);
        }
    }

    private function isiBimbingan(Student $student): void
    {
        $data = [
            ['Konsultasi proposal magang', 'Membahas kerangka proposal dan target capaian.', 'approved', 'Proposal sudah sesuai, lanjutkan.'],
            ['Konsultasi progres mingguan', 'Melaporkan progres dan kendala teknis.', 'pending', null],
            ['Konsultasi draft laporan', 'Membahas struktur bab laporan akhir.', 'rejected', 'Perbaiki sistematika bab 3, lalu kirim ulang.'],
        ];

        foreach ($data as $i => [$judul, $aktivitas, $status, $catatan]) {
            Guidance::create([
                'student_id'    => $student->id,
                'title'         => $judul,
                'activity'      => $aktivitas,
                'date'          => now()->subDays(21 - ($i * 7))->toDateString(),
                'name_file'     => $this->simpanPdf('guidances', "bimbingan-{$i}", 'Lampiran Bimbingan — Data Uji SIMAMA'),
                'status'        => $status,
                'lecturer_note' => $catatan,
            ]);
        }
    }

    private function isiLaporan(Student $student): void
    {
        InternshipReport::create([
            'student_id'  => $student->id,
            'title'       => 'Laporan Akhir Magang — Data Uji',
            'file_path'   => $this->simpanPdf('reports', 'laporan-akhir', 'Laporan Akhir Magang — Data Uji SIMAMA'),
            'status'      => 'approved',
            'reviewed_at' => now(),
        ]);
    }

    private function isiSertifikat(Internship $internship): void
    {
        $internship->update([
            'certificate_path' => $this->simpanPdf('certificates', 'sertifikat', 'Sertifikat Magang — Data Uji SIMAMA'),
        ]);
    }

    private function isiNilai(Internship $internship): void
    {
        $nilai = [
            'lecturer'          => 8,
            'lecturer_industry' => 9,
        ];

        foreach ($nilai as $scorerType => $skor) {
            $komponen = AssessmentComponent::forScorer($scorerType)->with('detailedComponents')->get();

            if ($komponen->isEmpty()) {
                $this->warn("Rubrik penilaian untuk '{$scorerType}' belum ada — jalankan: php artisan db:seed --class=RubrikPenilaianSeeder");
                continue;
            }

            foreach ($komponen as $c) {
                foreach ($c->detailedComponents as $detail) {
                    StudentScore::updateOrCreate([
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $detail->id,
                        'scorer_type'                      => $scorerType,
                    ], ['score' => $skor]);
                }
            }
        }

        $internship->update([
            'performance_notes'      => 'Mahasiswa menunjukkan sikap kerja yang baik, disiplin, dan mampu bekerja mandiri.',
            'performance_notes_by'   => 'Pembimbing Industri Dummy',
            'performance_notes_date' => now()->toDateString(),
        ]);
    }

    /* ───────────────────────── util ───────────────────────── */

    /** Tulis PDF minimal yang valid supaya tautan file di UI benar-benar bisa dibuka. */
    private function simpanPdf(string $folder, string $nama, string $judul): string
    {
        $path = "{$folder}/dummy-{$nama}.pdf";
        Storage::disk('public')->put($path, $this->pdfMinimal($judul));

        return $path;
    }

    private function pdfMinimal(string $judul): string
    {
        $teks = 'BT /F1 16 Tf 60 760 Td (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $judul) . ') Tj ET';

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            4 => '<< /Length ' . strlen($teks) . " >>\nstream\n" . $teks . "\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf     = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
        }

        $startXref = strlen($pdf);
        $size      = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach ($objects as $num => $body) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$num]);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$startXref}\n%%EOF\n";

        return $pdf;
    }

    private function hapus(string $nim): int
    {
        $user = User::where('username', $nim)->first();

        if (! $user) {
            $this->warn("Akun '{$nim}' tidak ada — tidak ada yang dihapus.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Hapus akun '{$nim}' ({$user->name}) beserta seluruh data magang, logbook, bimbingan, laporan, dan nilainya?")) {
            $this->info('Dibatalkan.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($user) {
            if ($student = $user->student) {
                $this->bersihkanDataAktivitas($student);
                $student->internships()->delete();
                $student->delete();
            }
            $user->delete();
        });

        $this->info("Akun '{$nim}' dan data pendukungnya dihapus.");
        $this->line('Akun pembimbing industri dummy (industri_dummy) dan perusahaan uji TIDAK dihapus — hapus manual bila perlu.');

        return self::SUCCESS;
    }

    private function laporkan(User $user, Student $student, Internship $internship, Lecturer $dospem, string $tahap, ?string $passwordBaru): void
    {
        $this->newLine();
        $this->info('Akun mahasiswa dummy siap.');
        $this->newLine();

        $this->table(['Item', 'Nilai'], [
            ['Login (NIM)', $user->username],
            ['Nama', $user->name],
            ['Kelas', $student->the_class],
            ['Program studi', $student->study_program],
            ['Dosen pembimbing', $dospem->user->name ?? '-'],
            ['Perusahaan', $internship->company->name ?? '-'],
            ['Tahap', $tahap],
            ['Logbook', $student->logBooks()->count() . ' entri'],
            ['Bimbingan', $student->guidances()->count() . ' entri'],
            ['Sertifikat', $internship->certificate_path ? 'ada' : 'belum'],
            ['Laporan akhir', InternshipReport::where('student_id', $student->id)->where('status', 'approved')->exists() ? 'sudah di-ACC' : 'belum'],
            ['Nilai dosen', StudentScore::where('internship_id', $internship->id)->where('scorer_type', 'lecturer')->exists() ? 'terisi' : 'belum'],
            ['Nilai industri', StudentScore::where('internship_id', $internship->id)->where('scorer_type', 'lecturer_industry')->exists() ? 'terisi' : 'belum'],
            ['Magang selesai', $internship->is_finished ? 'ya (siap seminar)' : 'belum'],
        ]);

        if ($passwordBaru) {
            $this->newLine();
            $this->warn("Password: {$passwordBaru}");
            $this->line('Catat sekarang — password ini tidak ditampilkan lagi.');
        } else {
            $this->newLine();
            $this->line('Password akun tidak diubah. Untuk menggantinya, jalankan ulang dengan --password=...');
        }

        $this->newLine();
        $this->line(match ($tahap) {
            'sedang-magang' => 'Tahap: magang berjalan. Logbook & bimbingan terisi; laporan, sertifikat, dan nilai sengaja dibiarkan kosong.',
            'siap-dinilai'  => 'Tahap: syarat penilaian terpenuhi (logbook lengkap + laporan di-ACC), nilai masih KOSONG. '
                . 'Inilah akun untuk menguji dosen & pembimbing industri mengisi nilai. '
                . 'Sertifikat sengaja belum ada — ia bukan syarat menilai.',
            'siap-selesai'  => 'Tahap: semua syarat lengkap — tombol "Ajukan Selesai Magang" di menu Magang Saya sudah bisa diklik.',
            'selesai'       => 'Tahap: magang sudah selesai — mahasiswa ini muncul di daftar peserta sesi seminar di portal dosen.',
        });
    }
}
