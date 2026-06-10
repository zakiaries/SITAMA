<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InternshipGroupMember;
use Illuminate\Support\Facades\Auth;

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
}
