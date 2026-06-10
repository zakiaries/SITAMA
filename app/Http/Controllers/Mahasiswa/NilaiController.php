<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $internship = $student->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->first();

        $nilai = $internship?->nilaiSummary();

        return view('mahasiswa.nilai.index', compact('internship', 'nilai'));
    }
}
