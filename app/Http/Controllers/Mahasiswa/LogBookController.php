<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\LogBook;
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

        LogBook::create([
            'student_id' => $student->id,
            'title'      => $request->title,
            'activity'   => $request->activity,
            'date'       => $request->date,
        ]);

        return redirect()->route('mahasiswa.logbook')
            ->with('success', 'Log book berhasil ditambahkan.');
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
