<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KaprodiController extends Controller
{
    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'name'          => $user->name,
            'username'      => $user->username,
            'photo_profile' => $user->photo_profile ? asset('storage/' . $user->photo_profile) : null,
            'role'          => 'Kaprodi',
        ]);
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $totalMahasiswa  = Student::count();
        $belumDospem     = Student::whereDoesntHave('internships')->count();
        $verifIndustri   = Company::where('verification_status', 'pending')->count();
        $aktif           = Internship::where('is_finished', false)->count();
        $selesai         = Internship::where('is_finished', true)->count();

        return response()->json([
            'total_mahasiswa'  => $totalMahasiswa,
            'belum_dospem'     => $belumDospem,
            'verif_industri'   => $verifIndustri,
            'aktif'            => $aktif,
            'selesai'          => $selesai,
        ]);
    }

    // ─── Mahasiswa ────────────────────────────────────────────────────────────

    public function getMahasiswa(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');

        $query = Student::with(['user', 'internships.lecturer.user', 'internships.company']);

        if ($search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%"));
        }

        $students = $query->get()->filter(function ($student) use ($status) {
            $internship = $student->internships->first();
            return match ($status) {
                'aktif'       => $internship && !$internship->is_finished,
                'belum_dospem' => !$internship,
                'belum_magang' => !$internship,
                'selesai'     => $internship && $internship->is_finished,
                default       => true,
            };
        })->map(fn($s) => $this->formatStudent($s));

        return response()->json(['mahasiswa' => array_values($students->toArray())]);
    }

    public function assignLecturer(Request $request, int $studentId)
    {
        $request->validate(['lecturer_id' => 'required|exists:lecturers,id']);

        $student    = Student::findOrFail($studentId);
        $internship = $student->internships()->whereNull('lecturer_id')->first()
                   ?? $student->internships()->latest()->first();

        if ($internship) {
            $internship->update(['lecturer_id' => $request->lecturer_id]);
        } else {
            $student->internships()->create(['lecturer_id' => $request->lecturer_id]);
        }

        return response()->json(['message' => 'Dosen pembimbing berhasil ditugaskan']);
    }

    // ─── Dosen ───────────────────────────────────────────────────────────────

    public function getDosen(Request $request)
    {
        $search = $request->query('search', '');

        $query = Lecturer::with(['user', 'internships.student.user']);

        if ($search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $dosens = $query->get()->map(function ($lecturer) {
            return [
                'id'             => $lecturer->id,
                'name'           => $lecturer->user->name,
                'username'       => $lecturer->user->username,
                'photo_profile'  => $lecturer->user->photo_profile
                    ? asset('storage/' . $lecturer->user->photo_profile) : null,
                'student_count'  => $lecturer->internships->count(),
            ];
        });

        return response()->json(['dosen' => $dosens]);
    }

    public function getDosenStudents(int $lecturerId)
    {
        $lecturer = Lecturer::with(['internships.student.user', 'internships.company'])->findOrFail($lecturerId);

        $students = $lecturer->internships->map(fn($i) => $this->formatStudent($i->student, $i));

        return response()->json([
            'lecturer' => [
                'id'   => $lecturer->id,
                'name' => $lecturer->user->name,
            ],
            'students' => $students,
        ]);
    }

    // ─── Industry Verification ────────────────────────────────────────────────

    public function getIndustri(Request $request)
    {
        $status = $request->query('status', 'pending');
        $search = $request->query('search', '');

        $query = Company::with('user');

        if (in_array($status, ['pending', 'verified', 'rejected'])) {
            $query->where('verification_status', $status);
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $companies = $query->get()->map(fn($c) => $this->formatCompany($c));

        return response()->json(['industri' => $companies]);
    }

    public function verifyIndustri(int $companyId)
    {
        $company = Company::findOrFail($companyId);
        $company->update(['verification_status' => 'verified', 'rejection_reason' => null]);

        return response()->json(['message' => 'Perusahaan berhasil diverifikasi']);
    }

    public function rejectIndustri(Request $request, int $companyId)
    {
        $request->validate(['reason' => 'nullable|string']);

        $company = Company::findOrFail($companyId);
        $company->update([
            'verification_status' => 'rejected',
            'rejection_reason'    => $request->reason,
        ]);

        return response()->json(['message' => 'Perusahaan ditolak']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function formatStudent(Student $student, ?Internship $internship = null): array
    {
        $internship = $internship ?? $student->internships->first();
        $status = 'Belum Magang';
        if ($internship) {
            $status = $internship->is_finished ? 'Selesai' : 'Aktif';
        }

        return [
            'id'           => $student->id,
            'name'         => $student->user->name,
            'nim'          => $student->user->username,
            'class'        => $student->the_class,
            'major'        => $student->major,
            'status'       => $status,
            'company'      => $internship?->company->name ?? '-',
            'lecturer'     => $internship?->lecturer->user->name ?? '-',
            'photo_profile' => $student->user->photo_profile
                ? asset('storage/' . $student->user->photo_profile) : null,
        ];
    }

    private function formatCompany(Company $company): array
    {
        return [
            'id'                  => $company->id,
            'name'                => $company->name,
            'field'               => $company->field,
            'address'             => $company->address,
            'email'               => $company->email,
            'phone'               => $company->phone,
            'verification_status' => $company->verification_status,
            'rejection_reason'    => $company->rejection_reason,
            'pic_name'            => $company->user?->name ?? '-',
            'pic_email'           => $company->user?->email ?? '-',
        ];
    }
}
