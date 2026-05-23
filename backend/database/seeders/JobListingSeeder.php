<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Database\Seeder;

class JobListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all verified companies that have users
        $companies = Company::where('verification_status', 'verified')
            ->whereNotNull('user_id')
            ->get();

        if ($companies->isEmpty()) {
            // If no verified company, create one for testing
            $user = \App\Models\User::where('role', 'industri')->first();
            if ($user) {
                $company = Company::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => 'PT. Teknologi Digital',
                        'address' => 'Jakarta, Indonesia',
                        'field' => 'Information Technology',
                        'phone' => '021-1234567',
                        'email' => 'info@teknologi.com',
                        'verification_status' => 'verified',
                    ]
                );
                $companies = collect([$company]);
            } else {
                $this->command->error('No company user found. Please create a company user first.');
                return;
            }
        }

        // Create job listings for each company
        foreach ($companies as $company) {
            // Check if listings already exist
            if ($company->jobListings()->count() > 0) {
                $this->command->info("Company {$company->name} already has job listings");
                continue;
            }

            $listings = [
                [
                    'title' => 'Junior Web Developer',
                    'division' => 'IT Development',
                    'description' => 'Kami mencari Junior Web Developer yang bersemangat dengan pengalaman 0-1 tahun. Tanggung jawab: mengembangkan dan memelihara aplikasi web menggunakan Laravel dan React.',
                    'skills' => ['Laravel', 'React', 'MySQL', 'Git'],
                    'location' => 'Jakarta, Indonesia',
                    'job_type' => 'On-site',
                    'quota' => 5,
                    'duration_months' => 6,
                    'pic_email' => $company->user->email ?? $company->email,
                    'status' => 'active',
                ],
                [
                    'title' => 'Mobile Developer (Flutter)',
                    'division' => 'Mobile Development',
                    'description' => 'Kami mencari Mobile Developer dengan pengalaman Flutter. Akan bekerja pada aplikasi mobile yang melayani jutaan pengguna.',
                    'skills' => ['Flutter', 'Dart', 'Firebase', 'REST API'],
                    'location' => 'Jakarta, Indonesia',
                    'job_type' => 'On-site',
                    'quota' => 3,
                    'duration_months' => 6,
                    'pic_email' => $company->user->email ?? $company->email,
                    'status' => 'active',
                ],
                [
                    'title' => 'Data Analyst Intern',
                    'division' => 'Data Analytics',
                    'description' => 'Bergabunglah dengan tim data kami untuk menganalisis trend bisnis dan membuat insights yang actionable.',
                    'skills' => ['SQL', 'Python', 'Excel', 'Tableau'],
                    'location' => 'Jakarta, Indonesia',
                    'job_type' => 'Hybrid',
                    'quota' => 2,
                    'duration_months' => 4,
                    'pic_email' => $company->user->email ?? $company->email,
                    'status' => 'active',
                ],
                [
                    'title' => 'UI/UX Designer',
                    'division' => 'Design',
                    'description' => 'Desain interface yang user-friendly dan menarik untuk berbagai platform digital.',
                    'skills' => ['Figma', 'UI Design', 'UX Research', 'Prototyping'],
                    'location' => 'Jakarta, Indonesia',
                    'job_type' => 'Work From Home',
                    'quota' => 2,
                    'duration_months' => 3,
                    'pic_email' => $company->user->email ?? $company->email,
                    'status' => 'active',
                ],
            ];

            foreach ($listings as $listing) {
                $company->jobListings()->create($listing);
            }

            $this->command->info("Created " . count($listings) . " job listings for {$company->name}");
        }
    }
}
