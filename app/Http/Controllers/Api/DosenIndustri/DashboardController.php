<?php

namespace App\Http\Controllers\Api\DosenIndustri;

use App\Http\Controllers\Api\ApiController;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $lecturer = $this->currentLecturer($request, 'lecturer_industry');

        $query = Student::whereHas('internships', fn ($q) => $q->where('lecturer_industry_id', $lecturer->id))
            ->with([
                'user',
                'internships' => fn ($q) => $q->where('lecturer_industry_id', $lecturer->id)->with('company')->latest(),
                'logBooks',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        $students = $query->get();

        $data = $students->map(function ($s) {
            $internship = $s->internships->first();
            $total      = $s->logBooks->count();
            $dikomen    = $s->logBooks->whereNotNull('industry_note')->count();

            return [
                'id'          => $s->id,
                'name'        => $s->user->name ?? '-',
                'username'    => $s->user->username ?? '-',
                'the_class'   => $s->the_class,
                'position'    => $internship?->position,
                'company'     => $internship?->company?->name,
                'logbook_total'   => $total,
                'logbook_dikomen' => $dikomen,
                'is_finished' => (bool) ($internship?->is_finished),
            ];
        });

        return response()->json([
            'lecturer' => $request->user()->name,
            'stats'    => [
                'total_mahasiswa' => $students->count(),
                'aktif'           => $students->filter(fn ($s) => ! ($s->internships->first()?->is_finished ?? true))->count(),
                'belum_dikomen'   => $students->sum(fn ($s) => $s->logBooks->whereNull('industry_note')->count()),
            ],
            'students' => $data,
        ]);
    }
}
