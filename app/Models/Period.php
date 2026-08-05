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

    /** Nilai penyaring yang bukan id periode. */
    const PILIHAN_SEMUA = 'semua';

    /** Mahasiswa yang belum masuk periode mana pun (`period_id` kosong). */
    const PILIHAN_TANPA = 'tanpa';

    /**
     * Pilihan penyaring bawaan: periode yang sedang berjalan.
     *
     * Kaprodi membuka halaman dan langsung melihat angkatan yang sedang ia urus,
     * bukan tumpukan semua angkatan sejak sistem dipakai — itu keluhan yang
     * membuat penyaring ini dibuat.
     *
     * Bila belum ada periode aktif, sengaja TIDAK menyaring apa pun: menyaring
     * ke periode yang tak ada akan menyodorkan halaman kosong tanpa penjelasan,
     * dan itu lebih membingungkan daripada daftar yang panjang.
     */
    public static function pilihanBawaan(): string
    {
        return (string) (static::sekarang()?->id ?? self::PILIHAN_SEMUA);
    }

    /** Apakah pilihan ini benar-benar mempersempit daftar. */
    public static function menyaring(?string $pilihan): bool
    {
        return $pilihan !== null && $pilihan !== '' && $pilihan !== self::PILIHAN_SEMUA;
    }

    /**
     * Terapkan penyaring pada query apa pun yang punya kolom `period_id`.
     *
     * Dipakai bersama oleh dashboard, daftar mahasiswa, dan ekspor Excel supaya
     * ketiganya tak mungkin berbeda aturan — angka di kartu, isi daftar, dan isi
     * berkas ekspor harus menjawab pertanyaan yang sama.
     */
    public static function terapkan($query, ?string $pilihan, string $kolom = 'period_id')
    {
        if (! self::menyaring($pilihan)) {
            return $query;
        }

        return $pilihan === self::PILIHAN_TANPA
            ? $query->whereNull($kolom)
            : $query->where($kolom, $pilihan);
    }

    /** Label pilihan, untuk judul halaman & nama berkas ekspor. */
    public static function labelPilihan(?string $pilihan): string
    {
        if (! self::menyaring($pilihan)) {
            return 'Semua periode';
        }

        return $pilihan === self::PILIHAN_TANPA
            ? 'Tanpa periode'
            : (static::find($pilihan)?->label ?? 'Semua periode');
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
