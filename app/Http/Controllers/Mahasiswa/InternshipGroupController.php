<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\InternshipGroup;
use App\Models\InternshipGroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipGroupController extends Controller
{
    public function invitePage(InternshipGroup $internshipGroup)
    {
        $student         = Auth::user()->student;
        $leaderStudentId = $internshipGroup->leaderApplication->student_id;

        if ($leaderStudentId !== $student->id) {
            abort(403, 'Hanya ketua kelompok yang dapat mengundang anggota.');
        }

        $internshipGroup->load(['members.student.user', 'leaderApplication.jobListing.company']);

        return view('mahasiswa.internship-groups.invite', compact('internshipGroup'));
    }

    public function invite(Request $request, InternshipGroup $internshipGroup)
    {
        $student         = Auth::user()->student;
        $leaderStudentId = $internshipGroup->leaderApplication->student_id;

        if ($leaderStudentId !== $student->id) {
            abort(403);
        }

        $request->validate([
            'username' => 'required|string',
        ], [
            'username.required' => 'Username/NIM wajib diisi.',
        ]);

        $invitedUser = User::where('username', $request->username)
            ->where('role', 'student')->first();

        if (!$invitedUser || !$invitedUser->student) {
            return back()->with('error', 'Mahasiswa dengan username "' . $request->username . '" tidak ditemukan.');
        }

        $invitedStudent = $invitedUser->student;

        if ($invitedStudent->id === $student->id) {
            return back()->with('error', 'Anda tidak dapat mengundang diri sendiri.');
        }

        $alreadyMember = InternshipGroupMember::where('group_id', $internshipGroup->id)
            ->where('student_id', $invitedStudent->id)->exists();

        if ($alreadyMember) {
            return back()->with('error', 'Mahasiswa ini sudah diundang.');
        }

        InternshipGroupMember::create([
            'group_id'   => $internshipGroup->id,
            'student_id' => $invitedStudent->id,
            'status'     => 'pending',
        ]);

        return back()->with('success', $invitedUser->name . ' berhasil diundang!');
    }

    public function accept(InternshipGroupMember $member)
    {
        $student = Auth::user()->student;

        if ($member->student_id !== $student->id) {
            abort(403);
        }

        $member->update(['status' => 'accepted']);

        return back()->with('success', 'Undangan berhasil diterima.');
    }

    public function decline(InternshipGroupMember $member)
    {
        $student = Auth::user()->student;

        if ($member->student_id !== $student->id) {
            abort(403);
        }

        $member->update(['status' => 'rejected']);

        return back()->with('success', 'Undangan ditolak.');
    }
}
