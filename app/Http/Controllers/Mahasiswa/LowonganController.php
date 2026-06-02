<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InternshipGroup;
use App\Models\InternshipGroupMember;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        $query   = JobListing::with('company')->where('status', 'active');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $lowongans = $query->latest()->get();

        $appliedIds = $student
            ? Application::where('student_id', $student->id)->pluck('job_listing_id')->toArray()
            : [];

        $user    = Auth::user();
        $student = $user->student;

        return view('mahasiswa.lowongan.index', compact('lowongans', 'appliedIds', 'user', 'student'));
    }

    public function apply(Request $request, JobListing $jobListing)
    {
        $student = Auth::user()->student;
        $type    = in_array($request->input('type'), ['solo', 'group']) ? $request->input('type') : 'solo';

        $already = Application::where('student_id', $student->id)
            ->where('job_listing_id', $jobListing->id)->exists();

        if ($already) {
            return back()->with('error', 'Anda sudah mendaftar pada lowongan ini.');
        }

        $application = Application::create([
            'student_id'     => $student->id,
            'job_listing_id' => $jobListing->id,
            'status'         => 'pending',
            'type'           => $type,
        ]);

        if ($type === 'group') {
            $group = InternshipGroup::create([
                'leader_application_id' => $application->id,
                'job_listing_id'        => $jobListing->id,
            ]);

            foreach (array_filter((array) $request->input('invited_nims', [])) as $nim) {
                $invitedUser = User::where('username', trim($nim))->where('role', 'student')->first();
                if ($invitedUser?->student && $invitedUser->student->id !== $student->id) {
                    InternshipGroupMember::firstOrCreate([
                        'group_id'   => $group->id,
                        'student_id' => $invitedUser->student->id,
                    ], ['status' => 'pending']);
                }
            }

            return redirect()->route('mahasiswa.magang-saya')
                ->with('success', 'Kelompok berhasil dibuat!');
        }

        return redirect()->route('mahasiswa.magang-saya')
            ->with('success', 'Berhasil mendaftar sebagai Solo!');
    }
}
