<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data fixture deterministik untuk test otomatis (dipakai bersama RefreshDatabase
 * pada DB `sitama_testing`). Password semua akun: "password".
 */
class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RubrikPenilaianSeeder::class]);

        $mk = fn (string $name, string $username, string $role, array $extra = []) => User::create(array_merge([
            'name'     => $name,
            'username' => $username,
            'email'    => $username . '@test.ac.id',
            'password' => Hash::make('password'),
            'role'     => $role,
        ], $extra));

        $kaprodi  = $mk('Kaprodi Uji', 'kaprodi', 'kaprodi');
        $dosenU   = $mk('Dosen Satu', 'dosen1', 'lecturer');
        $industriU = $mk('Industri Satu', 'industri1', 'lecturer_industry', ['is_activated' => true]);

        $dosen    = Lecturer::create(['user_id' => $dosenU->id]);
        $industri = Lecturer::create(['user_id' => $industriU->id]);

        $company = Company::create(['name' => 'PT Uji Sejahtera', 'verification_status' => 'verified']);

        // Mahasiswa aktif + magang SELESAI (buat uji seminar/nilai/selesai).
        $mhs1U = $mk('Mahasiswa Satu', '3.34.23.2.01', 'student');
        $mhs1  = Student::create([
            'user_id' => $mhs1U->id, 'the_class' => 'TI-1A', 'study_program' => 'Teknik Informatika',
            'major' => 'Informatika', 'academic_year' => '2023/2024', 'status' => 'active', 'lecturer_id' => $dosen->id,
        ]);
        Internship::create([
            'student_id' => $mhs1->id, 'lecturer_id' => $dosen->id, 'company_id' => $company->id,
            'lecturer_industry_id' => $industri->id, 'position' => 'Developer',
            'start_date' => '2024-01-01', 'end_date' => '2024-04-01', 'is_finished' => true,
        ]);

        // Mahasiswa aktif tanpa magang (buat uji ajukan magang).
        $mhs2U = $mk('Mahasiswa Dua', '3.34.23.2.02', 'student');
        Student::create([
            'user_id' => $mhs2U->id, 'the_class' => 'TI-1A', 'study_program' => 'Teknik Informatika',
            'major' => 'Informatika', 'academic_year' => '2023/2024', 'status' => 'active', 'lecturer_id' => $dosen->id,
        ]);

        // Mahasiswa pending (buat uji approve/reject/pulihkan).
        $mhs3U = $mk('Mahasiswa Tiga', '3.34.23.2.03', 'student');
        Student::create([
            'user_id' => $mhs3U->id, 'the_class' => 'TI-1B', 'study_program' => 'Teknik Informatika',
            'major' => 'Informatika', 'academic_year' => '2023/2024', 'status' => 'pending',
        ]);
    }
}
