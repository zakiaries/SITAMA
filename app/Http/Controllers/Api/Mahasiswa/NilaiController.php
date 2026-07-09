<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;

class NilaiController extends ApiController
{
    public function index(Request $request)
    {
        $student = $this->currentStudent($request);

        $internship = $student->activeInternship()
            ->with(['company', 'lecturer.user', 'lecturerIndustry.user'])
            ->first();

        $nilai = $internship?->nilaiSummary();

        return response()->json([
            'internship' => $internship ? [
                'company'            => $internship->company->name ?? null,
                'position'           => $internship->position,
                'lecturer'           => $internship->lecturer?->user?->name,
                'lecturer_industry'  => $internship->lecturerIndustry?->user?->name,
                'is_finished'        => (bool) $internship->is_finished,
            ] : null,
            'nilai' => $nilai ? [
                'overall' => $nilai['overall'],
                'items'   => $nilai['items'],
            ] : null,
        ]);
    }
}
