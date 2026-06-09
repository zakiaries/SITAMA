<?php

namespace App\Http\Controllers\Industri;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PelamarController extends Controller
{
    private function company(): Company
    {
        $company = Auth::user()->company;
        if (!$company) abort(403, 'Akun ini tidak terhubung dengan data perusahaan.');
        return $company;
    }

    private function authorizeApplication(Company $company, Application $application): void
    {
        $application->loadMissing('jobListing');
        if ($application->jobListing->company_id !== $company->id) abort(403);
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $status  = $request->input('status', 'pending');

        $base = fn() => Application::whereHas('jobListing', fn($q) => $q->where('company_id', $company->id));

        $query = $base()->with(['student.user', 'jobListing']);

        if (in_array($status, ['pending', 'accepted', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', fn($u) => $u->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $pelamars = $query->latest()->get();

        $counts = [
            'pending'  => $base()->where('status', 'pending')->count(),
            'accepted' => $base()->where('status', 'accepted')->count(),
            'rejected' => $base()->where('status', 'rejected')->count(),
        ];

        // Daftar dosen pembimbing industri untuk dipilih saat menerima pelamar.
        $industriLecturers = Lecturer::whereHas('user', fn($q) => $q->where('role', 'lecturer_industry'))
            ->with('user')->get();

        return view('industri.pelamar.index', compact('pelamars', 'status', 'counts', 'company', 'industriLecturers'));
    }

    public function accept(Request $request, Application $application)
    {
        $company = $this->company();
        $this->authorizeApplication($company, $application);

        $request->validate([
            'lecturer_industry_id' => [
                'required',
                Rule::exists('lecturers', 'id')->where(
                    fn($q) => $q->whereIn('user_id', \App\Models\User::where('role', 'lecturer_industry')->select('id'))
                ),
            ],
        ], [
            'lecturer_industry_id.required' => 'Silakan pilih dosen pembimbing industri.',
        ]);

        $application->update(['status' => 'accepted']);

        $this->createInternships($company, $application, (int) $request->lecturer_industry_id);
        $this->notifyStudent($company, $application, true);

        return back()->with('success', 'Pelamar diterima.');
    }

    public function reject(Application $application)
    {
        $company = $this->company();
        $this->authorizeApplication($company, $application);
        $application->update(['status' => 'rejected']);

        $this->removeInternships($company, $application);
        $this->notifyStudent($company, $application, false);

        return back()->with('success', 'Pelamar ditolak.');
    }

    /**
     * Kumpulkan student_id pelamar (ketua) + seluruh anggota kelompok bila lamaran berbentuk kelompok.
     */
    private function applicantStudentIds(Application $application)
    {
        $application->loadMissing('internshipGroup.members');

        return collect([$application->student_id])
            ->merge($application->internshipGroup?->members->pluck('student_id') ?? [])
            ->filter()
            ->unique();
    }

    /**
     * Saat diterima: buat (atau perbarui) data magang agar mahasiswa masuk ke daftar
     * dosen pembimbing kampus (lewat lecturer_id yang sudah di-plot Kaprodi).
     */
    private function createInternships(Company $company, Application $application, ?int $lecturerIndustryId = null): void
    {
        $application->loadMissing('jobListing');
        $position = $application->jobListing->title;

        foreach ($this->applicantStudentIds($application) as $studentId) {
            $student = Student::find($studentId);
            if (!$student) continue;

            $internship = Internship::firstOrNew([
                'student_id' => $studentId,
                'company_id' => $company->id,
            ]);

            $internship->lecturer_id          = $student->lecturer_id; // dosen pembimbing kampus
            $internship->lecturer_industry_id = $lecturerIndustryId;   // dosen pembimbing industri (dipilih perusahaan)
            $internship->position             = $position;

            if (!$internship->exists) {
                $internship->start_date  = now()->toDateString();
                $internship->is_finished = false;
            }

            $internship->save();
        }
    }

    /**
     * Saat ditolak (mis. setelah sempat diterima): cabut data magang di perusahaan ini.
     */
    private function removeInternships(Company $company, Application $application): void
    {
        Internship::where('company_id', $company->id)
            ->whereIn('student_id', $this->applicantStudentIds($application))
            ->delete();
    }

    private function notifyStudent(Company $company, Application $application, bool $accepted): void
    {
        $application->loadMissing(
            'student.user',
            'jobListing',
            'internshipGroup.members.student.user'
        );

        $position = $application->jobListing->title;

        if ($accepted) {
            $message = "Lamaran magang Anda di {$company->name} diterima.";
            $detail  = "Selamat! Lamaran Anda untuk posisi \"{$position}\" di {$company->name} telah diterima. Silakan hubungi perusahaan untuk langkah selanjutnya.";
        } else {
            $message = "Lamaran magang Anda di {$company->name} ditolak.";
            $detail  = "Mohon maaf, lamaran Anda untuk posisi \"{$position}\" di {$company->name} belum dapat diterima. Anda dapat melamar pada lowongan lain.";
        }

        // Kumpulkan id user pelamar (ketua) dan seluruh anggota kelompok bila ada.
        $userIds = collect([$application->student?->user?->id])
            ->merge($application->internshipGroup?->members
                ->map(fn($m) => $m->student?->user?->id) ?? [])
            ->filter()
            ->unique();

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id'     => $userId,
                'message'     => $message,
                'date'        => now()->toDateString(),
                'category'    => 'general',
                'is_read'     => false,
                'detail_text' => $detail,
            ]);
        }
    }
}
