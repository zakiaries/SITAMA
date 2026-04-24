<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Guidance;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\LogBook;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Kaprodi ──────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Dr. Ahmad Fauzi, M.Kom',
            'username' => 'kaprodi',
            'email'    => 'kaprodi@politeknik.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'kaprodi',
        ]);

        // ── Lecturers ─────────────────────────────────────────────────────────
        $lecturerUser1 = User::create([
            'name'     => 'Dr. Siti Rahayu, M.T.',
            'username' => 'dosen1',
            'email'    => 'siti.rahayu@politeknik.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'lecturer',
        ]);
        $lecturer1 = Lecturer::create(['user_id' => $lecturerUser1->id]);

        $lecturerUser2 = User::create([
            'name'     => 'Budi Hartono, S.T., M.Eng.',
            'username' => 'dosen2',
            'email'    => 'budi.hartono@politeknik.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'lecturer',
        ]);
        $lecturer2 = Lecturer::create(['user_id' => $lecturerUser2->id]);

        // ── Lecturer Industry ─────────────────────────────────────────────────
        $lecturerIndUser = User::create([
            'name'     => 'Andi Wijaya, S.T.',
            'username' => 'pembimbing_industri',
            'email'    => 'andi.wijaya@telkom.co.id',
            'password' => Hash::make('password'),
            'role'     => 'lecturer_industry',
        ]);
        $lecturerInd = Lecturer::create(['user_id' => $lecturerIndUser->id]);

        // ── Companies ─────────────────────────────────────────────────────────
        $companyUser = User::create([
            'name'     => 'PT. Telkom Indonesia',
            'username' => 'telkom_hr',
            'email'    => 'hr@telkom.co.id',
            'password' => Hash::make('password'),
            'role'     => 'industri',
        ]);
        $company = Company::create([
            'user_id' => $companyUser->id,
            'name'    => 'PT. Telkom Indonesia',
            'address' => 'Jl. Japati No.1, Bandung, Jawa Barat',
            'field'   => 'Telekomunikasi & Teknologi Informasi',
            'phone'   => '022-4521111',
            'email'   => 'info@telkom.co.id',
        ]);

        $company2 = Company::create([
            'name'    => 'PT. Gojek Indonesia',
            'address' => 'Pasaraya Blok M, Jakarta Selatan',
            'field'   => 'Teknologi & Ride-Hailing',
            'phone'   => '021-5010000',
            'email'   => 'info@gojek.com',
        ]);

        // ── Students ──────────────────────────────────────────────────────────
        $students = [
            [
                'name' => 'Budi Santoso', 'username' => 'mahasiswa1',
                'email' => 'budi.santoso@student.politeknik.ac.id',
                'the_class' => 'TI-4A', 'study_program' => 'Teknik Informatika',
                'major' => 'Informatika', 'academic_year' => '2021/2022',
            ],
            [
                'name' => 'Sari Dewi', 'username' => 'mahasiswa2',
                'email' => 'sari.dewi@student.politeknik.ac.id',
                'the_class' => 'TI-4A', 'study_program' => 'Teknik Informatika',
                'major' => 'Informatika', 'academic_year' => '2021/2022',
            ],
            [
                'name' => 'Rizky Pratama', 'username' => 'mahasiswa3',
                'email' => 'rizky.pratama@student.politeknik.ac.id',
                'the_class' => 'TI-4B', 'study_program' => 'Teknik Informatika',
                'major' => 'Informatika', 'academic_year' => '2021/2022',
            ],
            [
                'name' => 'Putri Ayu', 'username' => 'mahasiswa4',
                'email' => 'putri.ayu@student.politeknik.ac.id',
                'the_class' => 'TI-4B', 'study_program' => 'Teknik Informatika',
                'major' => 'Informatika', 'academic_year' => '2021/2022',
            ],
        ];

        foreach ($students as $i => $data) {
            $user = User::create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
                'role'     => 'student',
            ]);

            $student = Student::create([
                'user_id'       => $user->id,
                'the_class'     => $data['the_class'],
                'study_program' => $data['study_program'],
                'major'         => $data['major'],
                'academic_year' => $data['academic_year'],
            ]);

            $lecturer = $i < 2 ? $lecturer1 : $lecturer2;
            $company  = $i < 3 ? $company : $company2;

            $internship = Internship::create([
                'student_id'          => $student->id,
                'lecturer_id'         => $lecturer->id,
                'company_id'          => $company->id,
                'lecturer_industry_id' => $lecturerInd->id,
                'position'            => ['Frontend Developer', 'Backend Engineer', 'UI/UX Designer', 'Data Analyst'][$i],
                'start_date'          => '2024-07-01',
                'end_date'            => $i < 2 ? '2024-09-30' : null,
                'is_finished'         => $i < 2,
            ]);

            $this->seedGuidances($student);
            $this->seedLogBooks($student);
            $this->seedNotifications($user);
        }
    }

    private function seedGuidances(Student $student): void
    {
        $guidances = [
            [
                'title'        => 'Bimbingan Awal Magang',
                'activity'     => 'Konsultasi rencana kegiatan magang dan target yang akan dicapai selama periode magang berlangsung.',
                'date'         => '2024-07-05',
                'status'       => 'approved',
                'lecturer_note' => 'Bagus, pastikan target terpenuhi sesuai jadwal.',
            ],
            [
                'title'        => 'Progress Report Minggu 2',
                'activity'     => 'Melaporkan progres pengerjaan fitur login dan registrasi pada aplikasi yang sedang dikembangkan.',
                'date'         => '2024-07-15',
                'status'       => 'approved',
                'lecturer_note' => 'Progress baik, lanjutkan ke fitur berikutnya.',
            ],
            [
                'title'        => 'Diskusi Teknologi yang Digunakan',
                'activity'     => 'Membahas stack teknologi yang digunakan perusahaan dan relevansinya dengan materi perkuliahan.',
                'date'         => '2024-07-22',
                'status'       => 'in-progress',
                'lecturer_note' => null,
            ],
            [
                'title'        => 'Review Laporan Akhir',
                'activity'     => 'Mengajukan draft laporan akhir magang untuk direview dan mendapatkan masukan dari dosen pembimbing.',
                'date'         => '2024-08-05',
                'status'       => 'pending',
                'lecturer_note' => null,
            ],
        ];

        foreach ($guidances as $g) {
            Guidance::create(array_merge(['student_id' => $student->id], $g));
        }
    }

    private function seedLogBooks(Student $student): void
    {
        $entries = [
            ['title' => 'Orientasi dan Perkenalan', 'activity' => 'Mengikuti sesi orientasi karyawan baru, perkenalan dengan tim, dan penjelasan SOP perusahaan.', 'date' => '2024-07-01'],
            ['title' => 'Setup Lingkungan Kerja', 'activity' => 'Instalasi tools pengembangan (VS Code, Git, Node.js) dan akses ke repositori project yang akan dikerjakan.', 'date' => '2024-07-02'],
            ['title' => 'Mempelajari Codebase', 'activity' => 'Membaca dan memahami struktur kode yang sudah ada, standar coding, dan alur data pada aplikasi.', 'date' => '2024-07-03'],
            ['title' => 'Pengerjaan Fitur Login', 'activity' => 'Mulai mengimplementasikan fitur autentikasi menggunakan JWT token sesuai spesifikasi yang diberikan mentor.', 'date' => '2024-07-08'],
            ['title' => 'Testing dan Bug Fixing', 'activity' => 'Melakukan unit testing pada fitur yang telah selesai dibuat dan memperbaiki bug yang ditemukan.', 'date' => '2024-07-10'],
        ];

        foreach ($entries as $lb) {
            LogBook::create(array_merge(['student_id' => $student->id], $lb));
        }
    }

    private function seedNotifications(User $user): void
    {
        $notifs = [
            [
                'message'  => 'Bimbingan Anda telah disetujui oleh dosen pembimbing.',
                'date'     => '2024-07-15',
                'category' => 'guidance',
                'is_read'  => true,
            ],
            [
                'message'  => 'Dosen pembimbing telah menambahkan catatan pada logbook Anda.',
                'date'     => '2024-07-16',
                'category' => 'log_book',
                'is_read'  => false,
            ],
            [
                'message'  => 'Pengingat: Segera unggah laporan akhir sebelum batas waktu.',
                'date'     => '2024-08-01',
                'category' => 'general',
                'is_read'  => false,
            ],
        ];

        foreach ($notifs as $n) {
            Notification::create(array_merge(['user_id' => $user->id], $n));
        }
    }
}
