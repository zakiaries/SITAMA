<?php

namespace App\Http\Controllers\DosenIndustri;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $lecturer = $user->lecturer;

        if (!$lecturer) abort(403, 'Akses ditolak.');

        $query = Student::whereHas('internships', fn($q) => $q->where('lecturer_industry_id', $lecturer->id))
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_industry_id', $lecturer->id)
                    ->with('company')->latest(),
                'logBooks',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        // Pembimbing industri yang dipakai perusahaan yang sama tiap tahun akan
        // menumpuk bimbingan lintas angkatan, sampai tak jelas lagi siapa yang
        // sedang ia bimbing sekarang. Daftarnya hanya memuat periode yang ia
        // memang punya bimbingan di dalamnya.
        $periodeList = Period::whereHas('students', fn($q) => $q->whereHas(
            'internships',
            fn($i) => $i->where('lecturer_industry_id', $lecturer->id)
        ))->terbaru()->get();

        $periode = $request->input('periode') ?: $this->periodeBawaan($periodeList);

        Period::terapkan($query, $periode);

        $students = $query->get();

        $totalMahasiswa = $students->count();
        $aktif          = $students->filter(fn($s) => !($s->internships->first()?->is_finished ?? true))->count();
        $belumDikomen   = $students->sum(fn($s) => $s->logBooks->whereNull('industry_note')->count());

        return view('dosen-industri.dashboard.index', compact(
            'user', 'lecturer', 'students', 'totalMahasiswa', 'aktif', 'belumDikomen',
            'periode', 'periodeList'
        ));
    }

    /**
     * Periode berjalan jadi acuan, tapi hanya bila pembimbing ini punya
     * bimbingan di dalamnya — kalau tidak, ia membuka dashboard dan melihat
     * layar kosong padahal bimbingannya ada di angkatan sebelumnya.
     */
    private function periodeBawaan($periodeList): string
    {
        $bawaan = Period::pilihanBawaan();

        if (Period::menyaring($bawaan) && $periodeList->contains('id', (int) $bawaan)) {
            return $bawaan;
        }

        return (string) ($periodeList->first()?->id ?? Period::PILIHAN_SEMUA);
    }
}
