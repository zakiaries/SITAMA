<?php

namespace App\Http\Controllers\Api\Dosen;

use App\Http\Controllers\Api\ApiController;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer');
        $status   = $request->input('status', 'semua');

        $ownScores        = fn ($q) => $q->where('scorer_type', 'lecturer');
        $gradedInternship = fn ($q) => $q->where('lecturer_id', $lecturer->id)->whereHas('scores', $ownScores);

        $query = Student::dibimbingOleh($lecturer->id)
            ->with([
                'user',
                'internships' => fn ($q) => $q->where('lecturer_id', $lecturer->id)
                    ->with('company')->withCount(['scores' => $ownScores])->latest(),
                'guidances',
                'logBooks',
                'report', // penanda "laporan menunggu review"
            ]);

        if ($status === 'dinilai') {
            $query->whereHas('internships', $gradedInternship);
        } elseif ($status === 'belum') {
            $query->whereDoesntHave('internships', $gradedInternship);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }
        if ($request->filled('jurusan')) {
            $query->where('major', $request->jurusan);
        }
        if ($request->filled('tahun')) {
            $query->where('academic_year', $request->tahun);
        }

        $students = $query->get()->map(function ($student) {
            $internship = $student->internships->first();
            $graded     = ($internship?->scores_count ?? 0) > 0;
            $badge      = $graded ? 'dinilai' : ($internship?->is_finished ? 'selesai' : 'aktif');

            // Paritas dengan web: foto profil & penanda "menunggu tanggapan".
            $lbBelum  = $student->logBooks->whereNull('lecturer_note')->count();
            $bimBelum = $student->guidances->where('status', 'pending')->count();
            $lapBelum = ($student->report && $student->report->status === 'pending') ? 1 : 0;

            return [
                'id'              => $student->id,
                'name'            => $student->user->name ?? '-',
                'username'        => $student->user->username ?? '-',
                'major'           => $student->major,
                'the_class'       => $student->the_class,
                'company'         => $internship?->company?->name,
                'guidances_count' => $student->guidances->count(),
                'logbooks_count'  => $student->logBooks->count(),
                'status'          => $badge,
                'photo_url'       => $student->user?->photoUrl(),
                'logbook_belum_dikomentari' => $lbBelum,
                'bimbingan_pending'         => $bimBelum,
                'laporan_pending'           => $lapBelum,
                'perlu_tanggapan'           => $lbBelum + $bimBelum + $lapBelum,
            ];
        });

        $base = fn () => Student::dibimbingOleh($lecturer->id);
        $counts = [
            'semua'   => $base()->count(),
            'dinilai' => $base()->whereHas('internships', $gradedInternship)->count(),
            'belum'   => $base()->whereDoesntHave('internships', $gradedInternship)->count(),
        ];

        return response()->json([
            'lecturer' => $request->user()->name,
            'counts'   => $counts,
            // Untuk badge di menu/appbar: total yang menunggu tanggapan dosen.
            'menunggu_tanggapan' => $lecturer->menungguTanggapanKampus(),
            'majors'   => Student::dibimbingOleh($lecturer->id)->distinct()->pluck('major'),
            'years'    => Student::dibimbingOleh($lecturer->id)->distinct()->pluck('academic_year'),
            'students' => $students,
        ]);
    }
}
