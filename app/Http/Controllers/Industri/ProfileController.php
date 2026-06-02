<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $company = $user->company;

        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');

        $lowonganAktif   = $company->jobListings()->where('status', 'active')->count();
        $magangAktif     = $company->internships()->where('is_finished', false)->count();
        $pelamarDiterima = \App\Models\Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id))
            ->where('status', 'accepted')->count();

        return view('industri.profile.index', compact(
            'user', 'company', 'lowonganAktif', 'magangAktif', 'pelamarDiterima'
        ));
    }

    public function update(Request $request)
    {
        $user    = Auth::user();
        $company = $user->company;

        if (!$company) abort(403);

        $request->validate([
            'name'    => 'required|string|max:255',
            'field'   => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'required|email|max:255',
        ]);

        $company->update($request->only(['name', 'field', 'address', 'phone', 'email']));
        $user->update(['name' => $request->name]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
