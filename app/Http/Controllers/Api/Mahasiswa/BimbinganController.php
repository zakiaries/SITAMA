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

        $query = $student->guidances()->orderByDesc('date');
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

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date',
            'file'     => 'nullable|file|max:10240',
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

        if ($guidance->status !== 'rejected') {
            return response()->json(['message' => 'Bimbingan ini tidak sedang dalam status revisi.'], 422);
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date',
            'file'     => 'nullable|file|max:10240',
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

        return response()->json(['message' => 'Revisi bimbingan berhasil dikirim ulang ke dosen.']);
    }
}
