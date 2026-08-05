<?php

namespace App\Http\Controllers\Dosen;

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

        $status = $request->input('status', 'semua');

        // Internship bimbingan dosen ini yang sudah dinilai oleh dosen ini (punya StudentScore scorer_type=lecturer).
        $ownScores = fn($q) => $q->where('scorer_type', 'lecturer');
        $gradedInternship = fn($q) => $q->where('lecturer_id', $lecturer->id)->whereHas('scores', $ownScores);

        // Mahasiswa nonaktif (cuti/gap year) dikeluarkan dari DAFTAR, tapi
        // SENGAJA tidak dari gerbang akses: dosen tetap boleh membuka detail
        // dan berkasnya, karena bisa saja ia berhenti di tengah magang dan
        // riwayatnya masih perlu ditengok atau dinilai.
        $query = Student::dibimbingOleh($lecturer->id)
            ->where('status', '!=', Student::NONAKTIF)
            ->with([
                'user',
                'internships' => fn($q) => $q->where('lecturer_id', $lecturer->id)
                    ->with('company')->withCount(['scores' => $ownScores])->latest(),
                'guidances',
                'logBooks',
                'report', // dipakai penanda "laporan menunggu review" di kartu
            ]);

        if ($status === 'dinilai') {
            $query->whereHas('internships', $gradedInternship);
        } elseif ($status === 'belum') {
            // Belum dinilai termasuk yang magangnya belum terbentuk sama sekali.
            $query->whereDoesntHave('internships', $gradedInternship);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('major', 'like', "%$search%");
            });
        }

        if ($request->filled('jurusan')) {
            $query->where('major', $request->jurusan);
        }

        // Penyaring periode, sama seperti portal Kaprodi. Yang lama membaca
        // students.academic_year — teks bebas yang diketik sendiri mahasiswa —
        // sehingga dropdownnya berisi nilai seperti "2023/2026".
        //
        // Bedanya dengan Kaprodi: daftar periode di sini hanya yang dosen ini
        // memang punya bimbingan di dalamnya. Menawarkan periode kosong pada
        // dosen yang tak mengajar di angkatan itu hanya jadi pilihan buntu.
        $periodeList = Period::whereHas('students', fn($q) => $q->dibimbingOleh($lecturer->id))
            ->terbaru()->get();

        $periode = $request->input('periode') ?: $this->periodeBawaan($periodeList);

        Period::terapkan($query, $periode);

        $students = $query->get();

        // Hitungan untuk tab status (mengabaikan filter status, tetap ikut
        // filter dasar bimbingan dosen DAN periode yang sedang dilihat — angka
        // di tab harus menjawab pertanyaan yang sama dengan daftarnya).
        $base = fn() => Period::terapkan(
            Student::dibimbingOleh($lecturer->id)->where('status', '!=', Student::NONAKTIF),
            $periode
        );
        $counts = [
            'semua'   => $base()->count(),
            'dinilai' => $base()->whereHas('internships', $gradedInternship)->count(),
            'belum'   => $base()->whereDoesntHave('internships', $gradedInternship)->count(),
        ];

        $majors = Student::dibimbingOleh($lecturer->id)->distinct()->pluck('major');

        return view('dosen.dashboard.index', compact(
            'user', 'lecturer', 'students', 'majors', 'status', 'counts',
            'periode', 'periodeList'
        ));
    }

    /**
     * Periode yang ditampilkan lebih dulu bagi seorang dosen.
     *
     * Periode berjalan jadi acuan, seperti di portal Kaprodi — TAPI hanya bila
     * dosen ini punya bimbingan di dalamnya. Dosen yang giliran prodinya belum
     * tiba akan membuka dashboard dan melihat layar kosong tanpa penjelasan,
     * padahal bimbingannya ada di periode sebelumnya. Dalam hal itu, yang
     * ditampilkan adalah periode terbarunya sendiri.
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
