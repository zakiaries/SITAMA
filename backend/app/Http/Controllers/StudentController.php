<?php

namespace App\Http\Controllers;

use App\Models\FinalReport;
use App\Models\Guidance;
use App\Models\InternshipApplication;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // ─── Home ───────────────────────────────────────────────────────────────

    public function home(Request $request)
    {
        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['errors' => ['message' => 'Data mahasiswa tidak ditemukan']], 404);
        }

        $latestGuidances = $student->guidances()
            ->orderBy('date', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($g) => $this->formatGuidance($g));

        $latestLogBooks = $student->logBooks()
            ->orderBy('date', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($lb) => $this->formatLogBook($lb));

        return response()->json([
            'data' => [
                'name'             => $user->name,
                'latest_guidances' => $latestGuidances,
                'latest_log_books' => $latestLogBooks,
            ],
        ]);
    }

    // ─── Guidance ────────────────────────────────────────────────────────────

    public function getGuidances(Request $request)
    {
        $student   = $request->user()->student;
        $guidances = $student->guidances()->orderBy('date', 'desc')->limit(100)->get()
            ->map(fn($g) => $this->formatGuidance($g));

        return response()->json(['guidances' => $guidances]);
    }

    public function addGuidance(Request $request)
    {
        $request->validate([
            'title'    => 'required|string',
            'activity' => 'required|string',
            'date'     => 'required|date_format:Y-m-d',
            'name_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $student  = $request->user()->student;
        $fileName = null;

        if ($request->hasFile('name_file')) {
            $fileName = $request->file('name_file')->store('guidances', 'public');
        }

        $guidance = $student->guidances()->create([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
            'name_file' => $fileName,
            'status'   => 'pending',
        ]);

        return response()->json(['data' => $this->formatGuidance($guidance)], 201);
    }

    public function editGuidance(Request $request, int $id)
    {
        $request->validate([
            'title'    => 'required|string',
            'activity' => 'required|string',
            'date'     => 'required|date_format:Y-m-d',
            'name_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $student  = $request->user()->student;
        $guidance = $student->guidances()->findOrFail($id);

        $fileName = $guidance->name_file;

        if ($request->hasFile('name_file')) {
            if ($fileName) {
                Storage::disk('public')->delete($fileName);
            }
            $fileName = $request->file('name_file')->store('guidances', 'public');
        }

        $guidance->update([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
            'name_file' => $fileName,
        ]);

        return response()->json(['data' => $this->formatGuidance($guidance->fresh())]);
    }

    public function deleteGuidance(Request $request, int $id)
    {
        $student  = $request->user()->student;
        $guidance = $student->guidances()->findOrFail($id);

        if ($guidance->name_file) {
            Storage::disk('public')->delete($guidance->name_file);
        }

        $guidance->delete();

        return response()->json(['message' => 'Bimbingan berhasil dihapus']);
    }

    // ─── LogBook ─────────────────────────────────────────────────────────────

    public function getLogBooks(Request $request)
    {
        $student  = $request->user()->student;
        $logBooks = $student->logBooks()->orderBy('date', 'desc')->limit(100)->get()
            ->map(fn($lb) => $this->formatLogBook($lb));

        return response()->json(['log_books' => $logBooks]);
    }

    public function addLogBook(Request $request)
    {
        $request->validate([
            'title'    => 'required|string',
            'activity' => 'required|string',
            'date'     => 'required|date_format:Y-m-d',
        ]);

        $student = $request->user()->student;

        $logBook = $student->logBooks()->create([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
        ]);

        return response()->json(['data' => $this->formatLogBook($logBook)], 201);
    }

    public function editLogBook(Request $request, int $id)
    {
        $request->validate([
            'title'    => 'required|string',
            'activity' => 'required|string',
            'date'     => 'required|date_format:Y-m-d',
        ]);

        $student = $request->user()->student;
        $logBook = $student->logBooks()->findOrFail($id);

        $logBook->update([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
        ]);

        return response()->json(['data' => $this->formatLogBook($logBook->fresh())]);
    }

    public function deleteLogBook(Request $request, int $id)
    {
        $student = $request->user()->student;
        $logBook = $student->logBooks()->findOrFail($id);
        $logBook->delete();

        return response()->json(['message' => 'Log book berhasil dihapus']);
    }

    // ─── Notifications ───────────────────────────────────────────────────────

    public function getNotifications(Request $request)
    {
        $user          = $request->user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get()
            ->map(fn($n) => $this->formatNotification($n));

        return response()->json(['notifications' => $notifications]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi telah dibaca']);
    }

    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $user    = $request->user();
        $student = $user->student;

        $internships = $student?->internships()
            ->with('company')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($i) => [
                'name'       => $i->company->name,
                'start_date' => $i->start_date->format('Y-m-d'),
                'end_date'   => $i->end_date?->format('Y-m-d'),
            ]);

        return response()->json([
            'name'          => $user->name,
            'username'      => $user->username,
            'email'         => $user->email,
            'photo_profile' => $user->photo_profile
                ? asset('storage/' . $user->photo_profile)
                : null,
            'internships'   => $internships ?? [],
        ]);
    }

    // ─── Ajukan Magang ───────────────────────────────────────────────────────

    public function getAjukanMagang(Request $request)
    {
        $student          = $request->user()->student;
        $hasActiveInternship = $student->internships()->where('is_finished', false)->exists();
        $hasPending       = InternshipApplication::where('student_id', $student->id)
            ->where('status', 'pending')->exists();

        $applications = InternshipApplication::where('student_id', $student->id)
            ->latest()->get()->map(fn($a) => [
                'id'               => $a->id,
                'company_name'     => $a->company_name,
                'position'         => $a->position,
                'start_date'       => $a->start_date->format('Y-m-d'),
                'pic_name'         => $a->pic_name,
                'pic_phone'        => $a->pic_phone,
                'status'           => $a->status,
                'rejection_reason' => $a->rejection_reason,
                'proof_file'       => asset('storage/' . $a->proof_file),
                'created_at'       => $a->created_at->format('d M Y H:i'),
            ]);

        return response()->json([
            'has_active_internship' => $hasActiveInternship,
            'has_pending'           => $hasPending,
            'applications'          => $applications,
        ]);
    }

    public function storeAjukanMagang(Request $request)
    {
        $request->validate([
            'proof_file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'company_name' => 'required|string|max:255',
            'pic_name'    => 'required|string|max:255',
            'pic_phone'   => 'nullable|string|max:20',
            'pic_email'   => 'nullable|email|max:255',
            'position'    => 'nullable|string|max:255',
            'start_date'  => 'required|date',
        ]);

        $student = $request->user()->student;

        if ($student->internships()->where('is_finished', false)->exists()) {
            return response()->json(['message' => 'Kamu sudah memiliki magang aktif'], 422);
        }

        if (InternshipApplication::where('student_id', $student->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Pengajuan sebelumnya masih diproses'], 422);
        }

        $filePath = $request->file('proof_file')->store('internship_proofs', 'public');

        InternshipApplication::create([
            'student_id'   => $student->id,
            'company_name' => $request->company_name,
            'pic_name'     => $request->pic_name,
            'pic_phone'    => $request->pic_phone,
            'pic_email'    => $request->pic_email,
            'position'     => $request->position,
            'start_date'   => $request->start_date,
            'proof_file'   => $filePath,
            'status'       => 'pending',
        ]);

        return response()->json(['message' => 'Pengajuan berhasil dikirim'], 201);
    }

    // ─── Internship (Magang Saya) ─────────────────────────────────────────────

    public function internship(Request $request)
    {
        $student    = $request->user()->student;
        $internship = $student->internships()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->latest('start_date')
            ->first();

        if (!$internship) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id'                 => $internship->id,
                'company'            => $internship->company->name ?? '-',
                'position'           => $internship->position ?? '-',
                'start_date'         => $internship->start_date->format('Y-m-d'),
                'end_date'           => $internship->end_date?->format('Y-m-d'),
                'is_finished'        => $internship->is_finished,
                'lecturer'           => $internship->lecturer?->user?->name ?? 'Belum ditugaskan',
                'lecturer_industry'  => $internship->lecturerIndustry?->user?->name ?? 'Belum ditugaskan',
            ],
        ]);
    }

    // ─── Nilai ───────────────────────────────────────────────────────────────

    public function nilai(Request $request)
    {
        $student    = $request->user()->student;
        $internship = $student->internships()
            ->with(['company', 'scores.detailedComponent.component'])
            ->latest('start_date')
            ->first();

        if (!$internship) {
            return response()->json(['data' => null]);
        }

        $scores  = $internship->scores;
        $overall = $scores->isNotEmpty() ? round($scores->avg('score'), 1) : null;

        $grouped = $scores->groupBy(fn($s) => $s->detailedComponent->component->name ?? 'Lainnya');

        $items = $grouped->map(function ($componentScores, $componentName) {
            $subItems = $componentScores->groupBy(fn($s) => $s->detailedComponent->name)
                ->map(fn($sub, $name) => [
                    'name' => $name,
                    'avg'  => $sub->isNotEmpty() ? round($sub->avg('score'), 1) : null,
                ])->values();

            return ['component' => $componentName, 'items' => $subItems];
        })->values();

        return response()->json([
            'data' => [
                'overall'    => $overall,
                'internship' => [
                    'company'     => $internship->company->name ?? '-',
                    'is_finished' => $internship->is_finished,
                ],
                'items' => $items,
            ],
        ]);
    }

    // ─── Laporan Akhir ───────────────────────────────────────────────────────

    public function getLaporan(Request $request)
    {
        $student = $request->user()->student;
        $report  = FinalReport::where('student_id', $student->id)->latest()->first();

        if (!$report) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id'          => $report->id,
                'title'       => $report->title,
                'status'      => $report->status,
                'file_url'    => asset('storage/' . $report->file_path),
                'lecturer_note' => $report->lecturer_note,
                'uploaded_at' => $report->updated_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function storeLaporan(Request $request)
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf,doc,docx|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        $student  = $request->user()->student;
        $filePath = $request->file('file')->store('final_reports', 'public');

        $existing = FinalReport::where('student_id', $student->id)->latest()->first();

        if ($existing && $existing->status === 'rejected') {
            Storage::disk('public')->delete($existing->file_path);
            $existing->update([
                'title'         => $request->title ?? $existing->title,
                'file_path'     => $filePath,
                'status'        => 'pending',
                'lecturer_note' => null,
            ]);
            return response()->json(['message' => 'Laporan berhasil dikirim ulang']);
        }

        FinalReport::create([
            'student_id' => $student->id,
            'title'      => $request->title ?? 'Laporan Akhir Magang',
            'file_path'  => $filePath,
            'status'     => 'pending',
        ]);

        return response()->json(['message' => 'Laporan berhasil diunggah'], 201);
    }

    // ─── File Download ───────────────────────────────────────────────────────

    public function downloadGuidanceFile(Request $request, int $id)
    {
        $student  = $request->user()->student;
        $guidance = $student->guidances()->findOrFail($id);

        if (!$guidance->name_file || !Storage::disk('public')->exists($guidance->name_file)) {
            return response()->json(['errors' => ['message' => 'File tidak ditemukan']], 404);
        }

        return Storage::disk('public')->download($guidance->name_file);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatGuidance(Guidance $g): array
    {
        return [
            'id'           => $g->id,
            'title'        => $g->title,
            'activity'     => $g->activity,
            'date'         => $g->date->format('Y-m-d'),
            'lecturer_note' => $g->lecturer_note ?? 'tidak ada catatan',
            'name_file'    => $g->name_file
                ? asset('storage/' . $g->name_file)
                : 'tidak ada file',
            'status'       => $g->status,
        ];
    }

    private function formatLogBook(LogBook $lb): array
    {
        return [
            'id'           => $lb->id,
            'title'        => $lb->title,
            'activity'     => $lb->activity,
            'date'         => $lb->date->format('Y-m-d'),
            'lecturer_note' => $lb->lecturer_note ?? 'tidak ada catatan',
        ];
    }

    private function formatNotification(Notification $n): array
    {
        return [
            'id'          => $n->id,
            'user_id'     => $n->user_id,
            'message'     => $n->message,
            'date'        => $n->date->format('Y-m-d'),
            'category'    => $n->category,
            'is_read'     => $n->is_read ? 1 : 0,
            'detail_text' => $n->detail_text,
            'created_at'  => $n->created_at->toISOString(),
            'updated_at'  => $n->updated_at->toISOString(),
        ];
    }
}
