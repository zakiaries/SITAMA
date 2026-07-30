<?php

namespace App\Console\Commands;

use App\Models\AssessmentComponent;
use App\Models\Company;
use App\Models\Guidance;
use App\Models\Internship;
use App\Models\InternshipReport;
use App\Models\Lecturer;
use App\Models\LogBook;
use App\Models\Seminar;
use App\Models\SeminarPresenter;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyiapkan SATU alur magang lengkap yang realistis untuk keperluan demo/sidang:
 * akun mahasiswa → magang berjalan → logbook & bimbingan → laporan akhir di-ACC →
 * sertifikat → nilai dosen & pembimbing industri → magang selesai → sesi seminar
 * terjadwal beserta QR daftar hadir.
 *
 * Berbeda dengan simama:mahasiswa-dummy yang memakai data bertanda "dummy/data uji",
 * perintah ini mengisi data seperti kasus magang sungguhan sehingga layak dipakai
 * sebagai tangkapan layar dokumentasi.
 *
 * Idempoten: dijalankan ulang akan menyusun ulang data aktivitas milik akun ini saja.
 * Data mahasiswa lain tidak disentuh.
 *
 * Contoh:
 *   php artisan simama:simulasi-magang --dospem=dosen1 --password=RahasiaKuat123
 *   php artisan simama:simulasi-magang --hapus --force
 */
class SimulasiMagang extends Command
{
    protected $signature = 'simama:simulasi-magang
        {--nim=3.34.23.2.55 : NIM sekaligus username untuk login}
        {--nama=Rizky Adi Nugroho : Nama mahasiswa}
        {--kelas=IK-3C : Kelas}
        {--prodi=Teknik Informatika : Program studi}
        {--jurusan=Teknik Elektro : Jurusan}
        {--tahun=2025/2026 : Tahun akademik}
        {--dospem= : Username dosen pembimbing (default: dosen pertama yang ada)}
        {--password= : Password akun mahasiswa & pembimbing industri (default: diacak lalu dicetak sekali)}
        {--perusahaan=Dinas Komunikasi dan Informatika Kota Semarang : Nama tempat magang}
        {--kota=Semarang : Lokasi perusahaan}
        {--posisi=Web Developer : Posisi magang}
        {--pembimbing=Andri Setiawan, S.Kom. : Nama pembimbing industri}
        {--pembimbing-username=andri.setiawan : Username login pembimbing industri}
        {--tanpa-seminar : Lewati pembuatan sesi seminar}
        {--tanggal-seminar= : Tanggal seminar YYYY-MM-DD (default: 7 hari lagi)}
        {--waktu-seminar=09.00 - 11.00 WIB : Jam pelaksanaan seminar}
        {--ruang-seminar=Ruang Seminar TI-01 : Ruang pelaksanaan seminar}
        {--hapus : Hapus akun simulasi beserta seluruh data pendukungnya}
        {--force : Lewati konfirmasi saat menghapus}';

    protected $description = 'Siapkan satu alur magang lengkap yang realistis: dari akun sampai seminar terjadwal + QR';

    public function handle(): int
    {
        $nim = trim((string) $this->option('nim'));

        if ($this->option('hapus')) {
            return $this->hapus($nim);
        }

        $dospem = $this->cariDospem();
        if (! $dospem) {
            return self::FAILURE;
        }

        [$user, $passwordBaru] = $this->siapkanAkun($nim);
        $student    = $this->siapkanStudent($user, $dospem);

        $this->bersihkanDataAktivitas($student);

        $internship = $this->siapkanMagang($student, $dospem);

        $this->isiLogbook($student, $internship);
        $this->isiBimbingan($student, $internship);
        $this->isiLaporan($student);
        $this->isiSertifikat($internship);
        $this->isiNilai($internship);

        $internship->update(['is_finished' => true, 'finish_requested' => false]);

        $seminar = $this->option('tanpa-seminar') ? null : $this->siapkanSeminar($student, $dospem);

        $this->laporkan($user, $student, $internship->refresh(), $dospem, $seminar, $passwordBaru);

        return self::SUCCESS;
    }

    /* ───────────────────────── akun & profil ───────────────────────── */

    private function cariDospem(): ?Lecturer
    {
        if ($username = $this->option('dospem')) {
            $u = User::where('username', $username)->where('role', 'lecturer')->first();

            if (! $u) {
                $this->error("Dosen dengan username '{$username}' tidak ditemukan.");
                $this->line('Daftar dosen yang ada: ' . User::where('role', 'lecturer')->pluck('username')->implode(', '));

                return null;
            }

            return $u->lecturer ?: Lecturer::create(['user_id' => $u->id]);
        }

        $lecturer = Lecturer::whereHas('user', fn ($q) => $q->where('role', 'lecturer'))->orderBy('id')->first();

        if (! $lecturer) {
            $this->error('Belum ada akun dosen pembimbing. Buat dulu lewat portal Kaprodi, atau pakai --dospem=<username>.');
        }

        return $lecturer;
    }

    /** @return array{0: User, 1: ?string} user + password baru (null bila tidak diubah) */
    private function siapkanAkun(string $nim): array
    {
        $user         = User::where('username', $nim)->first();
        $passwordBaru = $this->option('password') ?: null;

        if (! $user) {
            $passwordBaru ??= Str::random(14);

            $user = User::create([
                'name'         => (string) $this->option('nama'),
                'username'     => $nim,
                'email'        => Str::slug($nim, '_') . '@student.polines.ac.id',
                'password'     => Hash::make($passwordBaru),
                'role'         => 'student',
                'is_activated' => true,
            ]);
        } else {
            $data = ['name' => (string) $this->option('nama'), 'is_activated' => true];

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

    /** Bersihkan HANYA data aktivitas milik mahasiswa ini agar bisa disusun ulang. */
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

        // Sesi seminar: lepas mahasiswa ini; sesi yang jadi kosong ikut dibuang.
        foreach (SeminarPresenter::where('student_id', $student->id)->with('seminar')->get() as $p) {
            $seminar = $p->seminar;
            $p->delete();

            if ($seminar && $seminar->presenters()->count() === 0) {
                $seminar->attendances()->delete();
                $seminar->delete();
            }
        }
    }

    /* ───────────────────────── magang ───────────────────────── */

    private function siapkanMagang(Student $student, Lecturer $dospem): Internship
    {
        $company = Company::firstOrCreate(
            ['name' => (string) $this->option('perusahaan')],
            [
                'address'             => (string) $this->option('kota'),
                'field'               => 'Pemerintahan / Layanan Publik',
                'verification_status' => 'verified',
            ]
        );

        $pembimbing = $this->siapkanPembimbingIndustri();

        $internship = $student->internships()->latest()->first() ?? new Internship(['student_id' => $student->id]);

        $internship->fill([
            'student_id'           => $student->id,
            'lecturer_id'          => $dospem->id,
            'company_id'           => $company->id,
            'lecturer_industry_id' => $pembimbing->id,
            'position'             => (string) $this->option('posisi'),
            'start_date'           => $this->mulai()->toDateString(),
            'end_date'             => $this->selesai()->toDateString(),
            'certificate_path'     => null,
            'performance_notes'    => null,
        ])->save();

        return $internship->refresh();
    }

    private function siapkanPembimbingIndustri(): Lecturer
    {
        $username = (string) $this->option('pembimbing-username');
        $nama     = (string) $this->option('pembimbing');
        $password = $this->option('password') ?: null;

        $user = User::where('username', $username)->first();

        if (! $user) {
            $password ??= Str::random(14);

            $user = User::create([
                'name'         => $nama,
                'username'     => $username,
                'email'        => Str::slug($username, '.') . '@' . Str::slug((string) $this->option('perusahaan'), '') . '.go.id',
                'password'     => Hash::make($password),
                'role'         => 'lecturer_industry',
                'is_activated' => true,
            ]);
        } else {
            $data = ['name' => $nama, 'is_activated' => true];

            if ($password) {
                $data['password'] = Hash::make($password);
            }

            $user->update($data);
        }

        return $user->lecturer ?: Lecturer::create(['user_id' => $user->id]);
    }

    private function mulai(): Carbon
    {
        return now()->subMonths(5)->startOfMonth();
    }

    private function selesai(): Carbon
    {
        return now()->subDays(7);
    }

    /* ───────────────────────── data aktivitas ───────────────────────── */

    /**
     * 20 entri logbook (Internship::MIN_LOGBOOK) berisi progres kerja yang runtut,
     * dari orientasi sampai serah terima pekerjaan.
     */
    private function isiLogbook(Student $student, Internship $internship): void
    {
        $kegiatan = [
            ['Orientasi dan pengenalan unit kerja', 'Mengikuti pengarahan dari Kepala Bidang Aplikasi Informatika, pengenalan struktur organisasi, tata tertib kerja, serta penempatan pada tim pengembang aplikasi.'],
            ['Penyiapan lingkungan kerja', 'Melakukan instalasi PHP, Composer, dan Laravel pada perangkat kerja, serta memperoleh hak akses ke repositori Git internal dan basis data pengembangan.'],
            ['Mempelajari aplikasi yang sedang berjalan', 'Menelusuri struktur kode aplikasi layanan publik yang sudah berjalan untuk memahami alur autentikasi, hak akses pengguna, dan pola penamaan yang dipakai tim.'],
            ['Analisis kebutuhan modul pengaduan', 'Mengikuti rapat kebutuhan bersama tim pengembang dan mencatat kebutuhan modul pengaduan masyarakat beserta alur disposisi antarbidang.'],
            ['Perancangan basis data modul pengaduan', 'Menyusun rancangan tabel pengaduan, kategori, dan riwayat disposisi beserta relasinya, kemudian dikonsultasikan kepada pembimbing lapangan.'],
            ['Pembuatan migrasi dan model', 'Membuat berkas migrasi serta model Eloquent untuk tabel pengaduan dan kategori, termasuk penentuan kolom wajib dan indeks pencarian.'],
            ['Implementasi halaman daftar pengaduan', 'Membangun halaman daftar pengaduan lengkap dengan penomoran halaman dan penanda status agar petugas mudah memantau antrean masuk.'],
            ['Implementasi formulir pengaduan', 'Membuat formulir pengaduan masyarakat beserta pemilihan kategori dan lokasi kejadian, mengikuti panduan tampilan yang berlaku di lingkungan instansi.'],
            ['Validasi masukan dan penanganan galat', 'Menambahkan validasi pada seluruh isian formulir dan menampilkan pesan kesalahan yang mudah dipahami oleh pengguna umum.'],
            ['Integrasi unggah lampiran foto', 'Menambahkan fitur unggah foto bukti pada pengaduan, termasuk pembatasan ukuran berkas dan penyimpanan pada direktori publik.'],
            ['Pembuatan halaman detail pengaduan', 'Membangun halaman rincian pengaduan yang menampilkan data pelapor, lampiran, serta riwayat penanganan secara berurutan.'],
            ['Implementasi pencarian dan penyaringan', 'Menambahkan pencarian berdasarkan kata kunci serta penyaringan berdasarkan kategori, status, dan rentang tanggal.'],
            ['Pembuatan alur disposisi antarbidang', 'Mengimplementasikan pemindahan pengaduan ke bidang terkait beserta pencatatan waktu dan petugas yang melakukan disposisi.'],
            ['Pembuatan dasbor rekapitulasi', 'Menyusun dasbor yang menampilkan jumlah pengaduan masuk, sedang diproses, dan selesai untuk kebutuhan pemantauan pimpinan.'],
            ['Penyajian grafik statistik pengaduan', 'Menampilkan grafik tren pengaduan bulanan dan sebaran per kategori guna mendukung penyusunan laporan kinerja.'],
            ['Penyesuaian tampilan untuk layar kecil', 'Menyesuaikan tata letak halaman agar tetap terbaca pada perangkat telepon seluler yang banyak digunakan petugas lapangan.'],
            ['Perbaikan kesalahan pada proses disposisi', 'Menelusuri dan memperbaiki kesalahan yang menyebabkan riwayat disposisi tercatat ganda ketika petugas menekan tombol dua kali.'],
            ['Pengujian fungsional modul pengaduan', 'Melakukan pengujian seluruh fungsi modul pengaduan bersama petugas, mencatat temuan, dan memperbaiki kekurangan yang ditemukan.'],
            ['Penyusunan dokumentasi teknis', 'Menyusun dokumentasi struktur basis data, alur proses, dan panduan singkat penggunaan modul untuk keperluan pemeliharaan.'],
            ['Serah terima pekerjaan dan evaluasi akhir', 'Mempresentasikan hasil kerja kepada pembimbing lapangan dan tim, menyerahkan kode beserta dokumentasi, serta menerima evaluasi akhir pelaksanaan magang.'],
        ];

        $catatanDosen = [
            2  => 'Bagus, pastikan setiap perubahan kode dicatat pada logbook secara rinci.',
            7  => 'Progres sesuai rencana. Perhatikan konsistensi penamaan variabel.',
            12 => 'Alur disposisi sudah tepat. Sertakan tangkapan layarnya pada laporan.',
            18 => 'Dokumentasi teknis ini bisa langsung dipakai untuk lampiran laporan akhir.',
        ];

        $catatanIndustri = [
            0  => 'Cepat beradaptasi dengan lingkungan kerja dan aktif bertanya.',
            5  => 'Rancangan tabel sudah sesuai kebutuhan bidang.',
            9  => 'Fitur unggah lampiran berjalan baik saat diuji petugas.',
            13 => 'Dasbor sangat membantu pemantauan harian.',
            17 => 'Pengujian dilakukan dengan teliti, temuan ditindaklanjuti dengan cepat.',
            19 => 'Hasil kerja rapi, tepat waktu, dan siap digunakan.',
        ];

        $total = Internship::MIN_LOGBOOK;
        $awal  = $this->selesai()->copy()->subDays($total * 2);

        foreach (array_slice($kegiatan, 0, $total) as $i => [$judul, $aktivitas]) {
            LogBook::create([
                'student_id'    => $student->id,
                'title'         => $judul,
                'activity'      => $aktivitas,
                'date'          => $awal->copy()->addDays($i * 2)->toDateString(),
                'lecturer_note' => $catatanDosen[$i] ?? null,
                'industry_note' => $catatanIndustri[$i] ?? null,
            ]);
        }
    }

    private function isiBimbingan(Student $student, Internship $internship): void
    {
        $data = [
            ['Konsultasi rencana kegiatan magang', 'Membahas rencana kegiatan selama magang, target capaian mingguan, dan bentuk keluaran yang harus dihasilkan di tempat magang.', 'approved', 'Rencana kegiatan sudah sesuai, silakan dilanjutkan.'],
            ['Konsultasi progres modul pengaduan', 'Melaporkan progres pengerjaan modul pengaduan masyarakat serta kendala teknis pada proses unggah lampiran.', 'approved', 'Progres baik. Dokumentasikan setiap kendala beserta solusinya.'],
            ['Konsultasi sistematika laporan akhir', 'Membahas sistematika penulisan laporan akhir magang dan pembagian isi tiap bab sesuai pedoman.', 'approved', 'Sistematika sudah sesuai pedoman, lanjutkan ke pembahasan hasil.'],
            ['Konsultasi persiapan seminar hasil', 'Membahas materi presentasi seminar hasil magang dan kelengkapan berkas yang harus disiapkan.', 'pending', null],
        ];

        foreach ($data as $i => [$judul, $aktivitas, $status, $catatan]) {
            Guidance::create([
                'student_id'    => $student->id,
                'title'         => $judul,
                'activity'      => $aktivitas,
                'date'          => $this->selesai()->copy()->subDays(60 - ($i * 18))->toDateString(),
                'name_file'     => $this->simpanPdf('guidances', 'bimbingan-' . ($i + 1), 'Lembar Bimbingan Magang ' . ($i + 1) . ' — ' . $judul),
                'status'        => $status,
                'lecturer_note' => $catatan,
            ]);
        }
    }

    private function isiLaporan(Student $student): void
    {
        InternshipReport::create([
            'student_id'    => $student->id,
            'title'         => 'Laporan Akhir Magang',
            'file_path'     => $this->simpanPdf('reports', 'laporan-akhir', 'Laporan Akhir Magang — ' . $this->option('perusahaan')),
            'status'        => 'approved',
            'lecturer_note' => 'Laporan sudah lengkap dan sesuai pedoman. Disetujui.',
            'reviewed_at'   => now()->subDays(3),
        ]);
    }

    private function isiSertifikat(Internship $internship): void
    {
        $internship->update([
            'certificate_path' => $this->simpanPdf(
                'certificates',
                'sertifikat',
                'Sertifikat Magang — ' . $this->option('nama')
            ),
        ]);
    }

    /**
     * Nilai skala 1–10 sesuai rubrik yang aktif di basis data:
     * dosen (Proposal 20% + Laporan 80%) dan pembimbing industri (8 komponen).
     */
    private function isiNilai(Internship $internship): void
    {
        $pola = [
            'lecturer'          => [8, 9, 8, 9, 9, 8],
            'lecturer_industry' => [9, 8, 9, 9, 8, 9],
        ];

        foreach ($pola as $scorerType => $nilai) {
            $komponen = AssessmentComponent::forScorer($scorerType)->with('detailedComponents')->get();

            if ($komponen->isEmpty()) {
                $this->warn("Rubrik '{$scorerType}' belum ada — jalankan: php artisan db:seed --class=RubrikPenilaianSeeder");
                continue;
            }

            $n = 0;
            foreach ($komponen as $c) {
                foreach ($c->detailedComponents as $detail) {
                    StudentScore::updateOrCreate([
                        'internship_id'                    => $internship->id,
                        'detailed_assessment_component_id' => $detail->id,
                        'scorer_type'                      => $scorerType,
                    ], ['score' => $nilai[$n++ % count($nilai)]]);
                }
            }
        }

        $internship->update([
            'performance_notes'      => 'Mahasiswa menunjukkan sikap kerja yang baik, disiplin, dan mampu menyelesaikan tugas secara mandiri maupun dalam tim.',
            'performance_notes_by'   => (string) $this->option('pembimbing'),
            'performance_notes_date' => $this->selesai()->toDateString(),
        ]);
    }

    /* ───────────────────────── seminar ───────────────────────── */

    /**
     * Membuat sesi seminar seperti alur aslinya: dosen membuat sesi, mahasiswa
     * mengisi ketersediaan tanggal, lalu dosen menetapkan jadwal final sehingga
     * halaman QR daftar hadir aktif.
     */
    private function siapkanSeminar(Student $student, Lecturer $dospem): Seminar
    {
        $tanggal = $this->option('tanggal-seminar')
            ? Carbon::parse((string) $this->option('tanggal-seminar'))
            : now()->addDays(7);

        $seminar = Seminar::create([
            'lecturer_id' => $dospem->id,
            'title'       => 'Seminar Hasil Magang ' . $student->academic_year,
            'program'     => $student->study_program ?: 'Magang',
            'organizer'   => $dospem->user->name ?? '-',
            'description' => 'Seminar hasil pelaksanaan magang mahasiswa Program Studi '
                . ($student->study_program ?: '-') . ' Politeknik Negeri Semarang.',
            'status'      => 'draft',
        ]);

        SeminarPresenter::create([
            'seminar_id'      => $seminar->id,
            'student_id'      => $student->id,
            'available_dates' => $tanggal->copy()->translatedFormat('d M Y') . ', '
                . $tanggal->copy()->addDay()->translatedFormat('d M Y') . ', atau '
                . $tanggal->copy()->addDays(2)->translatedFormat('d M Y') . ' (sesi pagi)',
            'responded_at'    => now()->subDay(),
        ]);

        // Penetapan jadwal final — menyalakan QR daftar hadir (lihat SeminarController::finalize).
        $seminar->update([
            'date'         => $tanggal->toDateString(),
            'time'         => (string) $this->option('waktu-seminar'),
            'location'     => (string) $this->option('ruang-seminar'),
            'status'       => 'scheduled',
            'access_token' => $seminar->access_token ?: Str::random(48),
        ]);

        return $seminar->refresh();
    }

    /* ───────────────────────── util ───────────────────────── */

    /** PDF minimal yang valid supaya tautan berkas di antarmuka benar-benar bisa dibuka. */
    private function simpanPdf(string $folder, string $nama, string $judul): string
    {
        $path = "{$folder}/simulasi-{$nama}.pdf";
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

        if (! $this->option('force')
            && ! $this->confirm("Hapus akun '{$nim}' ({$user->name}) beserta seluruh data magang, logbook, bimbingan, laporan, nilai, dan sesi seminarnya?")) {
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
        $this->line('Akun pembimbing industri dan data perusahaan TIDAK ikut dihapus — hapus manual bila perlu.');

        return self::SUCCESS;
    }

    private function laporkan(User $user, Student $student, Internship $internship, Lecturer $dospem, ?Seminar $seminar, ?string $passwordBaru): void
    {
        $ringkas = $internship->nilaiSummary();

        $this->newLine();
        $this->info('Simulasi magang siap dipakai.');
        $this->newLine();

        $this->table(['Item', 'Nilai'], [
            ['Login (NIM)', $user->username],
            ['Nama', $user->name],
            ['Kelas', $student->the_class],
            ['Program studi', $student->study_program],
            ['Dosen pembimbing', $dospem->user->name ?? '-'],
            ['Pembimbing industri', $internship->lecturerIndustry->user->name ?? '-'],
            ['Perusahaan', $internship->company->name ?? '-'],
            ['Posisi', $internship->position],
            ['Periode magang', $internship->start_date?->format('d M Y') . ' – ' . $internship->end_date?->format('d M Y')],
            ['Logbook', $student->logBooks()->count() . ' entri'],
            ['Bimbingan', $student->guidances()->count() . ' entri'],
            ['Laporan akhir', InternshipReport::where('student_id', $student->id)->where('status', 'approved')->exists() ? 'sudah di-ACC' : 'belum'],
            ['Sertifikat', $internship->certificate_path ? 'ada' : 'belum'],
            ['Nilai dosen', $ringkas['lecturer']['average'] ?? 'belum'],
            ['Nilai industri', $ringkas['industry']['average'] ?? 'belum'],
            ['Nilai akhir', $ringkas['final'] ?? 'belum'],
            ['Magang selesai', $internship->is_finished ? 'ya' : 'belum'],
            ['Sesi seminar', $seminar
                ? $seminar->status . ' — ' . $seminar->date?->format('d M Y') . ' ' . $seminar->time . ' @ ' . $seminar->location
                : 'tidak dibuat (--tanpa-seminar)'],
        ]);

        if ($passwordBaru) {
            $this->newLine();
            $this->warn("Password mahasiswa & pembimbing industri: {$passwordBaru}");
            $this->line('Catat sekarang — password ini tidak ditampilkan lagi.');
        }

        $this->newLine();
        $this->line('Login pembimbing industri : ' . $this->option('pembimbing-username'));

        if ($seminar) {
            $this->line('Halaman QR daftar hadir   : masuk sebagai ' . ($dospem->user->username ?? '-')
                . ' → menu Seminar Bimbingan → tombol QR pada sesi "' . $seminar->title . '"');
        }
    }
}
