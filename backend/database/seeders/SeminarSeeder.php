<?php

namespace Database\Seeders;

use App\Models\Seminar;
use Illuminate\Database\Seeder;

class SeminarSeeder extends Seeder
{
    public function run(): void
    {
        $seminars = [
            [
                'title'       => 'Seminar Hasil Magang & Pembelajaran',
                'program'     => 'Teknik Informatika',
                'date'        => now()->addDays(10)->format('Y-m-d'),
                'time'        => '13:00 - 15:00 WIB',
                'location'    => 'Aula Blok B Lantai 3',
                'organizer'   => 'Dr. Siti Rahayu, M.T.',
                'description' => 'Seminar wajib presentasi hasil magang untuk semua mahasiswa aktif.',
                'qr_code'     => 'QR_SEMINAR_001',
                'status'      => 'scheduled',
            ],
            [
                'title'       => 'Flutter Development Workshop',
                'program'     => 'Teknik Informatika',
                'date'        => now()->addDays(20)->format('Y-m-d'),
                'time'        => '10:00 - 12:00 WIB',
                'location'    => 'Lab Komputer Blok C',
                'organizer'   => 'Budi Hartono, S.T., M.Eng.',
                'description' => 'Workshop pengembangan aplikasi mobile menggunakan Flutter dan Dart.',
                'qr_code'     => 'QR_SEMINAR_002',
                'status'      => 'scheduled',
            ],
            [
                'title'       => 'Sharing Pengalaman Praktik Industri',
                'program'     => 'Teknik Informatika',
                'date'        => now()->addDays(5)->format('Y-m-d'),
                'time'        => '14:00 - 16:00 WIB',
                'location'    => 'Aula Blok A Lantai 1',
                'organizer'   => 'Dr. Ahmad Fauzi, M.Kom.',
                'description' => 'Sharing session bersama alumni tentang pengalaman magang di industri.',
                'qr_code'     => 'QR_SEMINAR_003',
                'status'      => 'scheduled',
            ],
        ];

        foreach ($seminars as $seminar) {
            Seminar::create($seminar);
        }
    }
}
