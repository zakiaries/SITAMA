<?php

namespace App\Http\Controllers;

use App\Models\Guidance;
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
        $guidances = $student->guidances()->orderBy('date', 'desc')->get()
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
        $logBooks = $student->logBooks()->orderBy('date', 'desc')->get()
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
