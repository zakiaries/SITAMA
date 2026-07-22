<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use Illuminate\Http\Request;

/**
 * Pemantauan seminar oleh Kaprodi (info-only).
 *
 * Pada model sesi-grup, penjadwalan & pengesahan dilakukan oleh dosen
 * pembimbing. Kaprodi hanya memantau status tiap sesi (terjadwal/selesai).
 */
class SeminarController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all'); // all | scheduled | completed | draft

        $query = Seminar::whereNotNull('lecturer_id')
            ->with(['lecturer.user', 'presenters.student.user', 'attendances']);

        if (in_array($status, ['draft', 'scheduled', 'completed'], true)) {
            $query->where('status', $status);
        }

        $seminars = $query
            ->orderByRaw("FIELD(status,'scheduled','draft','completed','cancelled')")
            ->orderByDesc('date')
            ->get();

        $counts = [
            'all'       => Seminar::whereNotNull('lecturer_id')->count(),
            'draft'     => Seminar::whereNotNull('lecturer_id')->where('status', 'draft')->count(),
            'scheduled' => Seminar::whereNotNull('lecturer_id')->where('status', 'scheduled')->count(),
            'completed' => Seminar::whereNotNull('lecturer_id')->where('status', 'completed')->count(),
        ];

        return view('kaprodi.seminar.index', compact('seminars', 'status', 'counts'));
    }
}
