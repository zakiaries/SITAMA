<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\BerkasController;
use App\Models\InternshipReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);
        $student->loadMissing('lecturer.user', 'activeInternship.lecturer.user');

        $report   = $student->report;
        $lecturer = $student->lecturer ?? $student->activeInternship?->lecturer;

        return response()->json([
            'lecturer' => $lecturer?->user?->name,
            'report'   => $report ? [
                'id'            => $report->id,
                'title'         => $report->title,
                'status'        => $report->status,
                'lecturer_note' => $report->lecturer_note,
                'file_url'      => $report->file_path ? BerkasController::tautanBertandaTangan('berkas.laporan', $report) : null,
                'reviewed_at'   => optional($report->reviewed_at)->toDateTimeString(),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $student  = $this->currentStudent($request);
        $existing = $student->report;

        // Boleh diganti selama belum disetujui dosen (pending maupun revisi/rejected).
        if ($existing && $existing->status === 'approved') {
            return response()->json(['message' => 'Laporan sudah disetujui dosen dan tidak bisa diganti.'], 422);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'file'  => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'file.required' => 'File laporan wajib diunggah.',
            'file.mimes'    => 'Laporan harus berformat PDF atau Word.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $path = $request->file('file')->store('reports', 'local');

        if ($existing) {
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

        return response()->json(['message' => 'Laporan akhir berhasil diunggah dan menunggu persetujuan dosen.'], 201);
    }
}
