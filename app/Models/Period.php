<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Periode magang: satu semester dari satu tahun akademik.
 *
 * Istilah "Gasal" (bukan "Ganjil") mengikuti Simadu Polines, supaya Kaprodi
 * membaca label yang sama dengan yang ia lihat sehari-hari di sistem kampus.
 */
class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year', 'semester', 'start_date', 'end_date',
        'duration_months', 'study_programs', 'is_active',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'study_programs' => 'array',
        'is_active'      => 'boolean',
    ];

    /** Lama magang wajib, dalam bulan — dipakai sebagai isian awal periode baru. */
    const DEFAULT_DURATION_MONTHS = 5;

    /** Nilai kolom `semester` => label yang ditampilkan. */
    const SEMESTER = [
        'gasal' => 'Gasal',
        'genap' => 'Genap',
    ];

    /** "2026/2027 Gasal" — bentuk yang dipakai di dropdown & judul halaman. */
    public function getLabelAttribute(): string
    {
        return $this->academic_year . ' ' . (self::SEMESTER[$this->semester] ?? $this->semester);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /** Periode yang sedang berjalan; jadi acuan default semua penyaring. */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Urutan tampil: terbaru di atas, mengikuti Simadu.
     *
     * `semester` menurun kebetulan menempatkan Genap di atas Gasal untuk tahun
     * yang sama ('genap' > 'gasal' secara abjad), dan itu memang urutan
     * kronologisnya — Gasal berjalan lebih dulu.
     */
    public function scopeTerbaru($query)
    {
        return $query->orderByDesc('academic_year')->orderByDesc('semester');
    }

    /** Periode aktif, atau null bila Kaprodi belum menetapkannya. */
    public static function sekarang(): ?self
    {
        return static::aktif()->first();
    }

    /**
     * Jadikan periode ini satu-satunya yang aktif.
     *
     * Keaktifan dipaksa tunggal karena ia menentukan periode mana yang dipakai
     * saat mahasiswa baru mendaftar — dua periode aktif membuat penempatannya
     * bergantung urutan baris, yang berarti tak bisa diramalkan.
     */
    public function aktifkan(): void
    {
        static::whereKeyNot($this->getKey())->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    /**
     * Apakah prodi ini termasuk peserta periode.
     *
     * Daftar kosong berarti Kaprodi belum membatasi, jadi semua prodi diterima —
     * bukan "tak ada yang diterima", yang akan mengunci semua orang di luar.
     */
    public function menerimaProdi(?string $prodi): bool
    {
        $peserta = $this->study_programs ?? [];

        return $peserta === [] || ($prodi !== null && in_array($prodi, $peserta, true));
    }
}
