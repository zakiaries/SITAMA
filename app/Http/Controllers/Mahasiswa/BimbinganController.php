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
            'date'     => 'required|date',
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

        // Hanya pemilik & hanya bimbingan yang diminta revisi (ditolak) yang boleh dikirim ulang.
        if ($guidance->student_id !== $student->id) abort(403);
        if ($guidance->status !== 'rejected') {
            return back()->with('error', 'Bimbingan ini tidak sedang dalam status revisi.');
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date',
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
            ->with('success', 'Revisi bimbingan berhasil dikirim ulang ke dosen.');
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
