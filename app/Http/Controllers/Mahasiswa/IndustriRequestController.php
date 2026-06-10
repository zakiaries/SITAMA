<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndustriRequestController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $requests = CompanyRequest::where('student_id', $student->id)->latest()->get();

        $hasPending = $requests->contains(fn($r) => $r->status === 'pending');

        return view('mahasiswa.industri-request.index', compact('requests', 'hasPending'));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        if (CompanyRequest::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Anda masih memiliki pengajuan yang sedang ditinjau Kaprodi.');
        }

        $validated = $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_field'   => 'nullable|string|max:255',
            'company_phone'   => 'nullable|string|max:30',
            'company_email'   => 'nullable|email|max:255',
            'pic_name'        => 'required|string|max:255',
            'pic_email'       => 'nullable|email|max:255',
            'pic_phone'       => 'nullable|string|max:30',
        ], [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'pic_name.required'     => 'Nama pembimbing industri wajib diisi.',
        ]);

        $validated['student_id'] = $student->id;
        $validated['status'] = 'pending';

        CompanyRequest::create($validated);

        return redirect()->route('mahasiswa.industri-request')
            ->with('success', 'Pengajuan akun industri berhasil dikirim. Silakan tunggu peninjauan dari Kaprodi.');
    }
}
