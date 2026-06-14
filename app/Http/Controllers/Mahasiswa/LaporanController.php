<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\InternshipReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $student->loadMissing('lecturer.user', 'activeInternship.lecturer.user');

        $report = $student->report;
        // Dospem dari students.lecturer_id; fallback ke dospem yang menempel di internship.
        $lecturer = $student->lecturer ?? $student->activeInternship?->lecturer;

        return view('mahasiswa.laporan.index', compact('report', 'lecturer'));
    }

    public function store(Request $request)
    {
        $student = Auth::user()->student;

        // Hanya boleh upload kalau belum ada, atau yang lama sudah ditolak.
        $existing = $student->report;
        if ($existing && $existing->status !== 'rejected') {
            return back()->with('error', 'Laporan sudah diunggah dan sedang/berhasil diproses.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'file'  => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'file.required' => 'File laporan wajib diunggah.',
            'file.mimes'    => 'Laporan harus berformat PDF atau Word.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $path = $request->file('file')->store('reports', 'public');

        if ($existing) {
            // Resubmit setelah revisi: ganti file, reset status.
            if ($existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->update([
                'title'         => $request->title ?: 'Laporan Akhir Magang',
                'file_path'     => $path,
                'status'        => 'pending',
                'lecturer_note' => null,
                'reviewed_at'   => null,
            ]);
        } else {
            InternshipReport::create([
                'student_id' => $student->id,
                'title'      => $request->title ?: 'Laporan Akhir Magang',
                'file_path'  => $path,
                'status'     => 'pending',
            ]);
        }

        return redirect()->route('mahasiswa.laporan')
            ->with('success', 'Laporan akhir berhasil diunggah dan menunggu persetujuan dosen.');
    }
}
