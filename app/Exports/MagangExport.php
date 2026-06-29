<?php

namespace App\Exports;

use App\Models\Internship;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class MagangExport implements WithMultipleSheets
{
    public function __construct(private ?string $tahun = null) {}

    public function sheets(): array
    {
        return [
            new MagangRingkasanSheet($this->tahun),
            new MagangDetailSheet($this->tahun),
        ];
    }
}

class MagangRingkasanSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(private ?string $tahun) {}

    public function title(): string { return 'Ringkasan'; }

    public function headings(): array
    {
        return ['Indikator', 'Jumlah'];
    }

    public function collection(): Collection
    {
        $base = Student::where('status', 'active')->when($this->tahun, fn($q) => $q->where('academic_year', $this->tahun));
        $intBase = Internship::when($this->tahun, fn($q) => $q->whereHas('student', fn($s) => $s->where('academic_year', $this->tahun)));

        $total   = (clone $base)->count();
        $belum   = (clone $base)->whereDoesntHave('internships')->count();
        $aktif   = (clone $base)->whereHas('internships', fn($q) => $q->where('is_finished', false))->count();
        $selesai = (clone $base)->whereHas('internships', fn($q) => $q->where('is_finished', true))->count();
        $companies = $intBase->clone()->distinct('company_id')->count('company_id');

        return collect([
            ['Total Mahasiswa Aktif' . ($this->tahun ? " TA {$this->tahun}" : ''), $total],
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
    public function __construct(private ?string $tahun) {}

    public function title(): string { return 'Data Mahasiswa'; }

    public function headings(): array
    {
        return [
            'No', 'Nama Mahasiswa', 'NIM', 'Program Studi', 'Kelas', 'Tahun Akademik',
            'Status Magang', 'Perusahaan', 'Posisi', 'Mulai Magang', 'Selesai',
            'Dosen Pembimbing', 'Pembimbing Industri', 'Jumlah Logbook',
        ];
    }

    public function collection(): Collection
    {
        $students = Student::with([
                'user',
                'internships.company',
                'internships.lecturer.user',
                'internships.lecturerIndustry.user',
                'logBooks',
            ])
            ->where('status', 'active')
            ->when($this->tahun, fn($q) => $q->where('academic_year', $this->tahun))
            ->orderBy('study_program')
            ->get();

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
                $student->academic_year ?? '-',
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
