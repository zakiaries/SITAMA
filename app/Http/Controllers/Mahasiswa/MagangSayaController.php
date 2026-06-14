<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InternshipGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MagangSayaController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $applications = Application::with([
            'jobListing.company',
            'internshipGroup.members.student.user',
        ])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $invitations = InternshipGroupMember::with([
            'group.leaderApplication.student.user',
            'group.jobListing.company',
        ])
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $internship = $student->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->first();

        $logBooks = $student->logBooks()->orderByDesc('date')->limit(5)->get();

        return view('mahasiswa.magang-saya.index', compact('applications', 'invitations', 'internship', 'logBooks'));
    }

    public function uploadCertificate(Request $request)
    {
        $student    = Auth::user()->student;
        $internship = $student->activeInternship()->first();

        if (!$internship) {
            return back()->with('error', 'Belum ada data magang aktif.');
        }

        $request->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'certificate.required' => 'File sertifikat wajib diunggah.',
            'certificate.mimes'    => 'Sertifikat harus berformat PDF atau gambar (JPG/PNG).',
            'certificate.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        if ($internship->certificate_path) {
            Storage::disk('public')->delete($internship->certificate_path);
        }

        $internship->update([
            'certificate_path' => $request->file('certificate')->store('certificates', 'public'),
        ]);

        return back()->with('success', 'Sertifikat magang berhasil diunggah.');
    }
}
