<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Base controller untuk semua API SITAMA Mobile.
 * Menyediakan helper otorisasi yang setara dengan gating web (tapi balas JSON).
 */
abstract class ApiController extends Controller
{
    /** Ambil Student dari user token; pastikan role student & sudah di-ACC Kaprodi. */
    protected function currentStudent(Request $request): Student
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'student', 403, 'Hanya untuk akun mahasiswa.');

        $student = $user->student;
        abort_if(! $student, 403, 'Data mahasiswa tidak ditemukan.');
        // Aturan sama dengan web (CheckStudentApproval): blok hanya pending / rejected.
        abort_if($student->status === 'pending', 403, 'Akun Anda masih menunggu persetujuan Kaprodi.');
        abort_if($student->status === 'rejected', 403, 'Pendaftaran Anda ditolak Kaprodi.');

        return $student;
    }

    /** Ambil Lecturer dari user token; $role = 'lecturer' (dosen) atau 'lecturer_industry'. */
    protected function currentLecturer(Request $request, string $role): Lecturer
    {
        $user = $request->user();
        abort_unless($user && $user->role === $role, 403, 'Akses ditolak.');

        if ($role === 'lecturer_industry') {
            abort_unless($user->is_activated, 403, 'Akun Anda belum diaktivasi.');
        }

        $lecturer = $user->lecturer;
        abort_if(! $lecturer, 403, 'Data pembimbing tidak ditemukan.');

        return $lecturer;
    }
}
