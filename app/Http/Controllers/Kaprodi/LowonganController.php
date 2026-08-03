<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Http\Request;

/**
 * Kelola lowongan/tempat magang perusahaan yang BERAFILIASI dengan Polines.
 *
 * Karena akun industri sudah ditiadakan, Kaprodi yang memasukkan perusahaan
 * afiliasi beserta lowongannya. Saat lowongan disimpan, perusahaannya otomatis
 * ditandai berafiliasi (Company.verification_status = 'verified') sehingga muncul
 * di daftar lowongan mahasiswa. Alur "Ajukan Magang" mahasiswa TIDAK diubah.
 */
class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $listings = JobListing::query()
            ->with('company')
            ->withCount('magangBerjalan')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('title', 'like', "%{$q}%")
                ->orWhere('company_name', 'like', "%{$q}%")
                ->orWhere('location', 'like', "%{$q}%")))
            ->latest()
            ->get();

        return view('kaprodi.lowongan.index', compact('listings', 'q'));
    }

    public function create()
    {
        return view('kaprodi.lowongan.form', ['listing' => new JobListing()]);
    }

    public function store(Request $request)
    {
        $data    = $this->validateData($request);
        $company = $this->resolveCompany($data);

        JobListing::create($this->listingPayload($data, $company));

        return redirect()->route('kaprodi.lowongan.index')
            ->with('success', 'Lowongan magang berhasil ditambahkan ke daftar mahasiswa.');
    }

    public function edit(JobListing $lowongan)
    {
        return view('kaprodi.lowongan.form', ['listing' => $lowongan]);
    }

    public function update(Request $request, JobListing $lowongan)
    {
        $data    = $this->validateData($request);
        $company = $this->resolveCompany($data);

        $lowongan->update($this->listingPayload($data, $company));

        return redirect()->route('kaprodi.lowongan.index')
            ->with('success', 'Lowongan magang berhasil diperbarui.');
    }

    public function destroy(JobListing $lowongan)
    {
        $lowongan->delete();

        return redirect()->route('kaprodi.lowongan.index')
            ->with('success', 'Lowongan magang dihapus.');
    }

    /** Aktif/nonaktifkan lowongan (yang nonaktif tidak tampil ke mahasiswa). */
    public function toggle(JobListing $lowongan)
    {
        // Kolom status = enum('active','closed') — memakai 'inactive' membuat SQL error (500).
        $lowongan->update(['status' => $lowongan->status === 'active' ? 'closed' : 'active']);

        return back()->with('success', 'Status lowongan diperbarui.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'company_name'    => 'required|string|max:255',
            'field'           => 'nullable|string|max:255',
            'title'           => 'required|string|max:255',
            'bidang'          => 'nullable|string|max:100',
            'division'        => 'nullable|string|max:255',
            'location'        => 'nullable|string|max:255',
            'job_type'        => 'nullable|string|max:100',
            'quota'           => 'nullable|integer|min:1|max:999',
            'skills'          => 'nullable|string|max:1000',
            'description'     => 'nullable|string',
        ], [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'title.required'        => 'Judul/posisi lowongan wajib diisi.',
            'quota.integer'         => 'Kuota harus berupa angka.',
            'quota.min'             => 'Kuota minimal 1. Kosongkan bila tidak dibatasi.',
            'quota.max'             => 'Kuota maksimal 999.',
        ]);
    }

    /** Cari/buat perusahaan berdasarkan nama, lalu tandai berafiliasi (verified). */
    private function resolveCompany(array $data): Company
    {
        $company = Company::firstOrNew(['name' => trim($data['company_name'])]);

        if (! $company->exists) {
            $company->field   = $data['field']    ?? null;
            $company->address = $data['location'] ?? null;
        }
        $company->verification_status = 'verified';
        $company->save();

        return $company;
    }

    private function listingPayload(array $data, Company $company): array
    {
        $skills = collect(explode(',', (string) ($data['skills'] ?? '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        return [
            'company_id'      => $company->id,
            'company_name'    => $company->name,
            'title'           => $data['title'],
            'bidang'          => $data['bidang'] ?? null,
            'division'        => $data['division'] ?? null,
            'description'     => $data['description'] ?? null,
            'skills'          => $skills,
            'location'        => $data['location'] ?? null,
            'job_type'        => $data['job_type'] ?? null,
            // Kosong = tak dibatasi; tak ada penanda kuota yang ditampilkan.
            'quota'           => $data['quota'] ?? null,
            'status'          => 'active',
        ];
    }
}
