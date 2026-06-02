<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LowonganController extends Controller
{
    private function company(): Company
    {
        $company = Auth::user()->company;
        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');
        return $company;
    }

    private function authorizeListing(Company $company, JobListing $jobListing): void
    {
        if ($jobListing->company_id !== $company->id) abort(403);
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $status  = $request->input('status', 'all');

        $query = $company->jobListings()->withCount('applications');

        if ($status === 'active')  $query->where('status', 'active');
        if ($status === 'closed')  $query->where('status', 'closed');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $lowongans = $query->latest()->get();

        $counts = [
            'all'    => $company->jobListings()->count(),
            'active' => $company->jobListings()->where('status', 'active')->count(),
            'closed' => $company->jobListings()->where('status', 'closed')->count(),
        ];

        return view('industri.lowongan.index', compact('lowongans', 'status', 'counts', 'company'));
    }

    public function create()
    {
        $company = $this->company();
        return view('industri.lowongan.form', ['company' => $company, 'lowongan' => null]);
    }

    public function store(Request $request)
    {
        $company = $this->company();
        $data    = $this->validateData($request);

        $company->jobListings()->create($data);

        return redirect()->route('industri.lowongan.index')
            ->with('success', 'Lowongan berhasil diterbitkan.');
    }

    public function edit(JobListing $jobListing)
    {
        $company = $this->company();
        $this->authorizeListing($company, $jobListing);

        return view('industri.lowongan.form', ['company' => $company, 'lowongan' => $jobListing]);
    }

    public function update(Request $request, JobListing $jobListing)
    {
        $company = $this->company();
        $this->authorizeListing($company, $jobListing);

        $jobListing->update($this->validateData($request));

        return redirect()->route('industri.lowongan.index')
            ->with('success', 'Lowongan berhasil diperbarui.');
    }

    public function toggleStatus(JobListing $jobListing)
    {
        $company = $this->company();
        $this->authorizeListing($company, $jobListing);

        $jobListing->update([
            'status' => $jobListing->status === 'active' ? 'closed' : 'active',
        ]);

        return back()->with('success', 'Status lowongan diperbarui.');
    }

    public function destroy(JobListing $jobListing)
    {
        $company = $this->company();
        $this->authorizeListing($company, $jobListing);

        $jobListing->delete();

        return back()->with('success', 'Lowongan berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'division'        => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'skills'          => 'nullable|string',
            'location'        => 'nullable|string|max:255',
            'job_type'        => 'required|in:On-site,Work From Home,Hybrid',
            'quota'           => 'required|integer|min:1|max:100',
            'duration_months' => 'required|integer|min:1|max:12',
            'pic_email'       => 'nullable|email|max:255',
            'status'          => 'required|in:active,closed',
        ]);

        // Ubah skills string "Laravel, PHP, MySQL" -> array
        $validated['skills'] = collect(explode(',', (string) $request->skills))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        return $validated;
    }
}
