<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base untuk feature test: migrasi fresh + seed fixture (TestSeeder) di DB
 * `sitama_testing`. Tiap test dibungkus transaksi & di-rollback.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestSeeder::class);
    }

    /** Ambil user fixture berdasarkan username. */
    protected function userByUsername(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    /**
     * Kembalikan magang mahasiswa ke keadaan BERJALAN.
     *
     * Fixture mahasiswa 3.34.23.2.01 magangnya is_finished. Sejak isian
     * mahasiswa dikunci setelah selesai magang, tes yang menguji pengisian
     * (logbook, bimbingan, sertifikat) harus memakai mahasiswa yang magangnya
     * masih berjalan — di alur nyata mustahil mengisi logbook untuk magang yang
     * sudah ditutup Kaprodi.
     */
    protected function magangBerjalan(User $mahasiswa): User
    {
        $mahasiswa->student?->internships()->latest('id')->first()
            ?->update(['is_finished' => false, 'finish_requested' => false]);

        return $mahasiswa;
    }

    /**
     * Penuhi syarat penilaian: logbook lengkap + laporan akhir di-ACC.
     *
     * Sejak nilai digerbangi kelengkapan mahasiswa (Internship::syaratPenilaian),
     * tes yang menguji pengisian nilai harus memakai mahasiswa yang memang sudah
     * merampungkan magangnya — di alur nyata pembimbing tak pernah bisa menilai
     * lebih dulu. Fixture-nya lahir polos, jadi kelengkapannya diisi di sini.
     */
    protected function siapDinilai(User $mahasiswa): User
    {
        $student = $mahasiswa->student;

        if (! $student) {
            return $mahasiswa;
        }

        $kurang = \App\Models\Internship::MIN_LOGBOOK - $student->logBooks()->count();

        foreach (range(1, max($kurang, 0)) as $i) {
            \App\Models\LogBook::create([
                'student_id' => $student->id,
                'title'      => "Kegiatan magang ke-{$i}",
                'activity'   => 'Mengerjakan tugas magang.',
                'date'       => now()->subDays($i)->toDateString(),
            ]);
        }

        \App\Models\InternshipReport::updateOrCreate(
            ['student_id' => $student->id],
            ['title' => 'Laporan Akhir', 'file_path' => 'reports/uji.pdf', 'status' => 'approved']
        );

        return $mahasiswa;
    }
}
