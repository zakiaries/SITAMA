<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Database\Seeder;

/**
 * Data DEMO (perusahaan + lowongan afiliasi) agar fitur Lowongan & chatbot
 * rekomendasi ada isinya saat demo/sidang. OPSIONAL — jalankan manual:
 *   php artisan db:seed --class=DemoSeeder
 * Idempoten (firstOrCreate). JANGAN diikutkan ke DatabaseSeeder produksi.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['PT Nusantara Teknologi', 'Semarang', 'Teknologi Informasi', [
                ['Front End Developer', 'Front End Developer', ['React', 'JavaScript', 'Tailwind'], 'On-site'],
                ['Back End Developer', 'Back End Developer', ['Laravel', 'MySQL', 'REST API'], 'Hybrid'],
            ]],
            ['CV Data Insight', 'Semarang', 'Data & Analitik', [
                ['Data Analyst Intern', 'Data / Data Science', ['Python', 'SQL', 'Power BI'], 'On-site'],
            ]],
            ['PT Gamatechno', 'Yogyakarta', 'Perangkat Lunak', [
                ['Mobile Developer (Flutter)', 'Mobile Developer', ['Flutter', 'Dart', 'Firebase'], 'On-site'],
                ['UI/UX Designer', 'UI/UX Design', ['Figma', 'Prototyping'], 'Remote'],
            ]],
            ['PT Telkom Indonesia', 'Bandung', 'Telekomunikasi', [
                ['Network Engineer Intern', 'Jaringan & Sistem', ['Cisco', 'Mikrotik', 'Routing'], 'On-site'],
                ['Cyber Security Intern', 'Cyber Security', ['Pentest', 'SIEM', 'Linux'], 'On-site'],
            ]],
            ['Tokopedia', 'Jakarta', 'E-commerce', [
                ['Full Stack Developer', 'Full Stack Developer', ['Go', 'React', 'PostgreSQL'], 'Hybrid'],
            ]],
            ['Studio Kreatif Pixel', 'Surabaya', 'Multimedia', [
                ['Video Editor & Motion', 'Multimedia / Editor', ['Premiere', 'After Effects'], 'On-site'],
            ]],
            ['PT Sinar Mas IT', 'Semarang', 'IT Services', [
                ['IT Support Intern', 'IT Support', ['Troubleshooting', 'Jaringan Dasar'], 'On-site'],
            ]],
        ];

        foreach ($data as [$nama, $kota, $field, $lowongan]) {
            $company = Company::firstOrCreate(
                ['name' => $nama],
                ['address' => $kota, 'field' => $field, 'verification_status' => 'verified',
                 'email' => strtolower(str_replace(' ', '', $nama)) . '@contoh.co.id', 'phone' => '024-' . random_int(1000000, 9999999)]
            );

            foreach ($lowongan as [$title, $bidang, $skills, $type]) {
                JobListing::firstOrCreate(
                    ['company_id' => $company->id, 'title' => $title],
                    [
                        'company_name' => $nama, 'division' => $field, 'bidang' => $bidang,
                        'description' => "Lowongan magang {$title} di {$nama} ({$kota}). Cocok untuk mahasiswa bidang {$bidang}.",
                        'skills' => $skills, 'location' => $kota, 'job_type' => $type,
                        'quota' => random_int(1, 4), 'duration_months' => 3, 'status' => 'active',
                    ]
                );
            }
        }

        $this->command->info('DemoSeeder: ' . Company::count() . ' perusahaan, ' . JobListing::count() . ' lowongan tersedia.');
    }
}
