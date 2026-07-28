<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Guidance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $student  = Auth::user()->student;
        $student->loadMissing('lecturer.user', 'activeInternship.lecturer.user');
        $query    = $student->guidances()->orderByDesc('date');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $guidances = $query->get();
        // Dospem dari students.lecturer_id; fallback ke dospem yang menempel di internship.
        $lecturer  = $student->lecturer ?? $student->activeInternship?->lecturer;

        return view('mahasiswa.bimbingan.index', compact('guidances', 'lecturer'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
            'file'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'file.mimes' => 'File bimbingan harus berformat PDF atau Word (doc/docx).',
            'file.max'   => 'Ukuran file maksimal 10 MB.',
        ]);

        $student = Auth::user()->student;

        $nameFile = null;
        if ($request->hasFile('file')) {
            $nameFile = $request->file('file')->store('guidances', 'public');
        }

        Guidance::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
            'name_file'  => $nameFile,
            'status'     => 'pending',
        ]);

        return redirect()->route('mahasiswa.bimbingan')
            ->with('success', 'Bimbingan berhasil ditambahkan.');
    }

    public function update(Request $request, Guidance $guidance)
    {
        $student = Auth::user()->student;

        // Hanya pemilik; boleh diubah selama belum disetujui dosen (pending maupun revisi/rejected).
        if ($guidance->student_id !== $student->id) abort(403);
        if ($guidance->status === 'approved') {
            return back()->with('error', 'Bimbingan yang sudah disetujui dosen tidak bisa diubah.');
        }
        $wasRejected = $guidance->status === 'rejected';

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
            'file'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'file.mimes' => 'File bimbingan harus berformat PDF atau Word (doc/docx).',
            'file.max'   => 'Ukuran file maksimal 10 MB.',
        ]);

        $data = [
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
            'status'   => 'pending',
        ];

        if ($request->hasFile('file')) {
            $data['name_file'] = $request->file('file')->store('guidances', 'public');
        }

        $guidance->update($data);

        return redirect()->route('mahasiswa.bimbingan')
            ->with('success', $wasRejected
                ? 'Revisi bimbingan berhasil dikirim ulang ke dosen.'
                : 'Bimbingan berhasil diperbarui.');
    }

    /**
     * Hapus bimbingan yang BELUM disetujui dosen. Yang sudah 'approved' terkunci
     * (jejak akademik yang sah tidak boleh dihapus).
     */
    public function destroy(Guidance $guidance)
    {
        $student = Auth::user()->student;

        if ($guidance->student_id !== $student->id) abort(403);

        if ($guidance->status === 'approved') {
            return back()->with('error', 'Bimbingan yang sudah disetujui dosen tidak bisa dihapus.');
        }

        if ($guidance->name_file) {
            Storage::disk('public')->delete($guidance->name_file);
        }

        $guidance->delete();

        return redirect()->route('mahasiswa.bimbingan')
            ->with('success', 'Bimbingan berhasil dihapus.');
    }
}
