<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $query = JobListing::with('company')->where('status', 'active');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $lowongans = $query->latest()->get();

        return view('mahasiswa.lowongan.index', compact('lowongans'));
    }
}
