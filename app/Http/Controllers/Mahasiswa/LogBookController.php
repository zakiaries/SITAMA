<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LogBook;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogBookController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;
        $query   = $student->logBooks()->orderByDesc('date');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $logBooks = $query->get();

        return view('mahasiswa.logbook.index', compact('logBooks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date',
        ]);

        $student = Auth::user()->student;

        $logBook = LogBook::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
        ]);

        $this->notifySupervisors($student, $logBook);

        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil ditambahkan.');
    }

    public function update(Request $request, LogBook $logBook)
    {
        if ($logBook->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'activity' => 'required|string',
            'date'     => 'required|date',
        ]);

        $logBook->update([
            'title'    => $request->title,
            'activity' => $request->activity,
            'date'     => $request->date,
        ]);

        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil diperbarui.');
    }

    private function notifySupervisors($student, LogBook $logBook): void
    {
        $internship = $student->activeInternship()->with(['lecturer.user', 'lecturerIndustry.user'])->first();

        if (!$internship) {
            return;
        }

        $message = "{$student->user->name} mengisi log book baru: \"{$logBook->title}\".";
        $detail  = "Log book tanggal {$logBook->date->format('d M Y')}: {$logBook->activity}";

        $userIds = collect([
            $internship->lecturer?->user?->id,
            $internship->lecturerIndustry?->user?->id,
        ])->filter()->unique();

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id'     => $userId,
                'message'     => $message,
                'date'        => now()->toDateString(),
                'category'    => 'log_book',
                'is_read'     => false,
                'detail_text' => $detail,
            ]);
        }
    }

    public function destroy(LogBook $logBook)
    {
        if ($logBook->student_id !== Auth::user()->student->id) {
            abort(403);
        }
        $logBook->delete();
        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil dihapus.');
    }
}
