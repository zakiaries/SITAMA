<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Models\Guidance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BimbinganController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);
        $student->loadMissing('lecturer.user', 'activeInternship.lecturer.user');

        // Terbaru ditambah/diedit di paling atas (updated_at ikut berubah saat edit).
        $query = $student->guidances()->orderByDesc('updated_at')->orderByDesc('id');
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $guidances = $query->get()->map(fn ($g) => [
            'id'            => $g->id,
            'title'         => $g->title,
            'activity'      => $g->activity,
            'date'          => optional($g->date)->toDateString(),
            'status'        => $g->status,
            'lecturer_note' => $g->lecturer_note,
            'file_url'      => $g->name_file ? Storage::url($g->name_file) : null,
        ]);

        $lecturer = $student->lecturer ?? $student->activeInternship?->lecturer;

        return response()->json([
            'lecturer'  => $lecturer?->user?->name,
            'guidances' => $guidances,
        ]);
    }

    public function store(Request $request)
    {
        $student = $this->currentStudent($request);

        // Sama seperti web: butuh dosen pembimbing agar bimbingan bisa diproses.
        if (! ($student->lecturer_id ?? $student->activeInternship?->lecturer_id)) {
            return response()->json(['message' => 'Dosen pembimbing belum ditugaskan oleh Kaprodi. Kamu bisa mengajukan bimbingan setelah dosen pembimbingmu ditetapkan.'], 422);
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
            'file'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $nameFile = $request->hasFile('file')
            ? $request->file('file')->store('guidances', 'public')
            : null;

        Guidance::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
            'name_file'  => $nameFile,
            'status'     => 'pending',
        ]);

        return response()->json(['message' => 'Bimbingan berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, Guidance $guidance)
    {
        $student = $this->currentStudent($request);
        abort_if($guidance->student_id !== $student->id, 403, 'Akses ditolak.');

        // Boleh diubah selama belum disetujui dosen (pending maupun revisi/rejected).
        if ($guidance->status === 'approved') {
            return response()->json(['message' => 'Bimbingan yang sudah disetujui dosen tidak bisa diubah.'], 422);
        }
        $wasRejected = $guidance->status === 'rejected';

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date|before_or_equal:today',
            'file'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',
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

        return response()->json(['message' => $wasRejected
            ? 'Revisi bimbingan berhasil dikirim ulang ke dosen.'
            : 'Bimbingan berhasil diperbarui.']);
    }

    public function destroy(Request $request, Guidance $guidance)
    {
        $student = $this->currentStudent($request);
        abort_if($guidance->student_id !== $student->id, 403, 'Akses ditolak.');

        if ($guidance->status === 'approved') {
            return response()->json(['message' => 'Bimbingan yang sudah disetujui dosen tidak bisa dihapus.'], 422);
        }

        if ($guidance->name_file) {
            Storage::disk('public')->delete($guidance->name_file);
        }

        $guidance->delete();

        return response()->json(['message' => 'Bimbingan berhasil dihapus.']);
    }
}
