<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Guidance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        $student  = Auth::user()->student;
        $query    = $student->guidances()->orderByDesc('date');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $guidances = $query->get();

        return view('mahasiswa.bimbingan.index', compact('guidances'));
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
}
