<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\Request;

class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = JobListing::query();

        if ($status === 'active') $query->where('status', 'active');
        if ($status === 'closed') $query->where('status', 'closed');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('company_name', 'like', "%$search%");
            });
        }

        $lowongans = $query->latest()->get();

        $counts = [
            'all'    => JobListing::count(),
            'active' => JobListing::where('status', 'active')->count(),
            'closed' => JobListing::where('status', 'closed')->count(),
        ];

        return view('kaprodi.lowongan.index', compact('lowongans', 'status', 'counts'));
    }

    public function create()
    {
        return view('kaprodi.lowongan.form', ['lowongan' => null]);
    }

    public function store(Request $request)
    {
        JobListing::create($this->validateData($request));

        return redirect()->route('kaprodi.lowongan.index')
            ->with('success', 'Pengumuman lowongan berhasil diterbitkan.');
    }

    public function edit(JobListing $jobListing)
    {
        return view('kaprodi.lowongan.form', ['lowongan' => $jobListing]);
    }

    public function update(Request $request, JobListing $jobListing)
    {
        $jobListing->update($this->validateData($request));

        return redirect()->route('kaprodi.lowongan.index')
            ->with('success', 'Pengumuman lowongan berhasil diperbarui.');
    }

    public function toggleStatus(JobListing $jobListing)
    {
        $jobListing->update([
            'status' => $jobListing->status === 'active' ? 'closed' : 'active',
        ]);

        return back()->with('success', 'Status pengumuman diperbarui.');
    }

    public function destroy(JobListing $jobListing)
    {
        $jobListing->delete();

        return back()->with('success', 'Pengumuman lowongan berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'division'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'skills'       => 'nullable|string',
            'location'     => 'nullable|string|max:255',
            'job_type'     => 'required|in:On-site,Work From Home,Hybrid',
            'pic_name'     => 'nullable|string|max:255',
            'pic_email'    => 'nullable|email|max:255',
            'pic_phone'    => 'nullable|string|max:50',
            'status'       => 'required|in:active,closed',
        ]);

        // Pengumuman tidak terikat akun perusahaan.
        $validated['company_id'] = null;

        // Ubah skills string "Laravel, PHP, MySQL" -> array
        $validated['skills'] = collect(explode(',', (string) $request->skills))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        return $validated;
    }
}
