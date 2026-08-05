<?php

namespace App\Exports;

use App\Models\Internship;
use App\Models\Period;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Rekap magang per PERIODE.
 *
 * Dulu disaring `students.academic_year` — teks bebas yang diketik sendiri
 * mahasiswa — sehingga rekap satu angkatan bisa bocor ke angkatan lain hanya
 * karena bedanya penulisan. Sekarang memakai penyaring yang sama persis dengan
 * dashboard, supaya angka di layar dan angka di berkas tak mungkin berbeda.
 */
class MagangExport implements WithMultipleSheets
{
    public function __construct(private ?string $periode = null) {}

    public function sheets(): array
    {
        return [
            new MagangRingkasanSheet($this->periode),
            new MagangDetailSheet($this->periode),
        ];
    }
}

class MagangRingkasanSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private ?string $periode) {}

    public function title(): string { return 'Ringkasan'; }

    public function headings(): array
    {
        return ['Indikator', 'Jumlah'];
    }

    public function collection(): Collection
    {
        $base = Period::terapkan(Student::where('status', 'active'), $this->periode);

        $intBase = Internship::when(
            Period::menyaring($this->periode),
            fn($q) => $q->whereHas('student', fn($s) => Period::terapkan($s, $this->periode))
        );

        $total   = (clone $base)->count();
        $belum   = (clone $base)->whereDoesntHave('internships')->count();
        $aktif   = (clone $base)->whereHas('internships', fn($q) => $q->where('is_finished', false))->count();
        $selesai = (clone $base)->whereHas('internships', fn($q) => $q->where('is_finished', true))->count();
        $companies = $intBase->clone()->distinct('company_id')->count('company_id');

        return collect([
            ['Periode', Period::labelPilihan($this->periode)],
            ['Total Mahasiswa Aktif', $total],
            ['Belum Magang', $belum],
            ['Sedang Magang', $aktif],
            ['Selesai Magang', $selesai],
            ['Total Perusahaan', $companies],
            ['Tanggal Export', now()->format('d/m/Y H:i')],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class MagangDetailSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private ?string $periode) {}

    public function title(): string { return 'Data Mahasiswa'; }

    public function headings(): array
    {
        return [
            'No', 'Nama Mahasiswa', 'NIM', 'Program Studi', 'Kelas', 'Periode Magang',
            'Status Magang', 'Perusahaan', 'Posisi', 'Mulai Magang', 'Selesai',
            'Dosen Pembimbing', 'Pembimbing Industri', 'Jumlah Logbook',
        ];
    }

    public function collection(): Collection
    {
        $query = Student::with([
                'user',
                'period',
                'internships.company',
                'internships.lecturer.user',
                'internships.lecturerIndustry.user',
                'logBooks',
            ])
            ->where('status', 'active')
            ->orderBy('study_program');

        $students = Period::terapkan($query, $this->periode)->get();

        $rows = collect();
        $i = 1;
        foreach ($students as $student) {
            $internship = $student->internships->first();
            if ($internship) {
                $status = $internship->is_finished ? 'Selesai' : 'Aktif';
            } else {
                $status = 'Belum Magang';
            }

            $rows->push([
                $i++,
                $student->user->name ?? '-',
                $student->nim ?? '-',
                $student->study_program ?? '-',
                $student->the_class ?? '-',
                $student->period?->label ?? '-',
                $status,
                $internship?->company?->name ?? '-',
                $internship?->position ?? '-',
                $internship?->start_date?->format('d/m/Y') ?? '-',
                $internship?->is_finished ? ($internship->end_date?->format('d/m/Y') ?? 'Ya') : '-',
                $internship?->lecturer?->user?->name ?? '-',
                $internship?->lecturerIndustry?->user?->name ?? '-',
                $student->logBooks->count(),
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
