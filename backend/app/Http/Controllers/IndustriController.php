<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndustriController extends Controller
{
    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $user    = $request->user();
        $company = $user->company;

        if (!$company) {
            return response()->json(['errors' => ['message' => 'Data perusahaan tidak ditemukan']], 404);
        }

        $activeInternships = $company->internships()->where('is_finished', false)->count();
        $totalAccepted     = Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id))
            ->where('status', 'accepted')->count();

        return response()->json([
            'id'                  => $company->id,
            'name'                => $company->name,
            'address'             => $company->address,
            'field'               => $company->field,
            'phone'               => $company->phone,
            'email'               => $company->email,
            'verification_status' => $company->verification_status,
            'photo_profile'       => $user->photo_profile ? asset('storage/' . $user->photo_profile) : null,
            'pic_name'            => $user->name,
            'pic_email'           => $user->email,
            'active_lowongan'     => $company->jobListings()->where('status', 'active')->count(),
            'active_internships'  => $activeInternships,
            'total_accepted'      => $totalAccepted,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'     => 'sometimes|string',
            'address'  => 'sometimes|string',
            'field'    => 'sometimes|string',
            'phone'    => 'sometimes|string',
            'email'    => 'sometimes|email',
            'pic_name' => 'sometimes|string',
        ]);

        $user    = $request->user();
        $company = $user->company;

        $company->update($request->only(['name', 'address', 'field', 'phone', 'email']));

        if ($request->filled('pic_name')) {
            $user->update(['name' => $request->pic_name]);
        }

        return response()->json(['message' => 'Profil berhasil diperbarui']);
    }

    // ─── Job Listings ─────────────────────────────────────────────────────────

    public function getLowongan(Request $request)
    {
        $company = $request->user()->company;
        $status  = $request->query('status', 'all');
        $search  = $request->query('search', '');

        $query = $company->jobListings()->withCount('applications');

        if (in_array($status, ['active', 'closed'])) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where('title', 'like', "%$search%");
        }

        $listings = $query->latest()->get()->map(fn($j) => $this->formatLowongan($j));

        return response()->json(['lowongan' => $listings]);
    }

    public function createLowongan(Request $request)
    {
        $request->validate([
            'title'           => 'required|string',
            'division'        => 'nullable|string',
            'description'     => 'nullable|string',
            'skills'          => 'nullable|array',
            'location'        => 'nullable|string',
            'job_type'        => 'nullable|in:On-site,Work From Home,Hybrid',
            'quota'           => 'nullable|integer|min:1',
            'duration_months' => 'nullable|integer|min:1|max:12',
            'pic_email'       => 'nullable|email',
        ]);

        $company  = $request->user()->company;
        $listing  = $company->jobListings()->create($request->only([
            'title', 'division', 'description', 'skills',
            'location', 'job_type', 'quota', 'duration_months', 'pic_email',
        ]));

        return response()->json(['data' => $this->formatLowongan($listing->loadCount('applications'))], 201);
    }

    public function updateLowongan(Request $request, int $id)
    {
        $company = $request->user()->company;
        $listing = $company->jobListings()->findOrFail($id);

        $listing->update($request->only([
            'title', 'division', 'description', 'skills',
            'location', 'job_type', 'quota', 'duration_months', 'pic_email', 'status',
        ]));

        return response()->json(['data' => $this->formatLowongan($listing->fresh()->loadCount('applications'))]);
    }

    public function deleteLowongan(Request $request, int $id)
    {
        $company = $request->user()->company;
        $company->jobListings()->findOrFail($id)->delete();

        return response()->json(['message' => 'Lowongan berhasil dihapus']);
    }

    // ─── Applications ─────────────────────────────────────────────────────────

    public function getPelamar(Request $request)
    {
        $company = $request->user()->company;
        $status  = $request->query('status', 'all');
        $search  = $request->query('search', '');

        $query = Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id))
            ->with(['student.user', 'jobListing']);

        if (in_array($status, ['pending', 'accepted', 'rejected'])) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('student.user', fn($q) =>
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%"));
        }

        $applications = $query->latest()->get()->map(fn($a) => $this->formatApplicant($a));

        return response()->json(['pelamar' => $applications]);
    }

    public function acceptPelamar(int $applicationId)
    {
        $application = Application::findOrFail($applicationId);
        $application->update(['status' => 'accepted']);

        return response()->json(['message' => 'Pelamar diterima']);
    }

    public function rejectPelamar(int $applicationId)
    {
        $application = Application::findOrFail($applicationId);
        $application->update(['status' => 'rejected']);

        return response()->json(['message' => 'Pelamar ditolak']);
    }

    // ─── Public job listings (for student job listing feature) ───────────────

    public function getPublicListings(Request $request)
    {
        $search   = $request->query('search', '');
        $category = $request->query('category', '');

        $query = JobListing::with('company')
            ->where('status', 'active')
            ->whereHas('company', fn($q) => $q->where('verification_status', 'verified'));

        if ($search) {
            $query->where(fn($q) => $q->where('title', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%"));
        }

        if ($category) {
            $query->whereJsonContains('skills', $category);
        }

        $listings = $query->latest()->get()->map(function ($j) {
            $companyName = $j->company->name;
            $words       = explode(' ', $companyName);
            $logo        = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
            $isNew       = $j->created_at->diffInDays(now()) <= 7;

            return [
                'id'           => (string) $j->id,
                'position'     => $j->title,
                'company'      => $companyName,
                'company_logo' => $logo,
                'description'  => $j->description ?? '',
                'skills'       => $j->skills ?? [],
                'location'     => $j->location ?? '-',
                'category'     => $j->job_type ?? 'IT',
                'is_new'       => $isNew,
                'created_at'   => $j->created_at->toIso8601String(),
            ];
        });

        return response()->json(['job_listings' => $listings]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatLowongan(JobListing $j): array
    {
        return [
            'id'               => $j->id,
            'title'            => $j->title,
            'division'         => $j->division,
            'description'      => $j->description,
            'skills'           => $j->skills ?? [],
            'location'         => $j->location,
            'job_type'         => $j->job_type,
            'quota'            => $j->quota,
            'duration_months'  => $j->duration_months,
            'pic_email'        => $j->pic_email,
            'status'           => $j->status,
            'applicant_count'  => $j->applications_count ?? 0,
            'created_at'       => $j->created_at->format('Y-m-d'),
        ];
    }

    private function formatApplicant(Application $a): array
    {
        $student = $a->student;
        $user    = $student->user;
        return [
            'id'           => $a->id,
            'status'       => $a->status,
            'applied_at'   => $a->created_at->format('Y-m-d'),
            'job_title'    => $a->jobListing->title,
            'student' => [
                'id'    => $student->id,
                'name'  => $user->name,
                'nim'   => $user->username,
                'class' => $student->the_class,
                'major' => $student->major,
            ],
        ];
    }
}
