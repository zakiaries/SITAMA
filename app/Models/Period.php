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
     * Isian periode untuk mahasiswa yang baru lahir, dari periode yang berjalan.
     *
     * Dipakai SEMUA jalur pembuatan mahasiswa — pendaftaran web, pendaftaran
     * lewat aplikasi HP, impor peserta, dan perintah dummy — supaya tak ada
     * satu pun jalur yang melahirkan mahasiswa tanpa periode. Satu yang
     * terlewat sudah cukup membuat orang lenyap dari layar Kaprodi, karena
     * daftarnya menyaring per periode.
     *
     * `academic_year` ikut diisi dari periode, bukan lagi diketik pendaftar.
     * Kolomnya masih dibaca dashboard dosen, ekspor, dan endpoint mobile, jadi
     * ia tetap terisi — bedanya sekarang nilainya lahir dari sistem sehingga
     * tak mungkin lagi berisi "2023/2026".
     *
     * Bila Kaprodi belum menetapkan periode aktif, keduanya dibiarkan kosong:
     * menebak periode lebih berbahaya daripada mengosongkannya, karena yang
     * kosong masih terlihat lewat pilihan "Tanpa periode" dan bisa dibetulkan,
     * sedangkan yang salah tempat terlihat benar dan tak pernah diperiksa.
     */
    public static function penempatanPendaftarBaru(): array
    {
        $periode = static::sekarang();

        return [
            'period_id'     => $periode?->id,
            'academic_year' => $periode?->academic_year,
        ];
    }

    /**
     * Tahun akademik & semester dari sebuah tanggal.
     *
     * Gasal berjalan Agustus–Januari, Genap Februari–Juli. Januari sengaja
     * dihitung sebagai EKOR Gasal tahun sebelumnya, bukan awal sesuatu yang
     * baru — magang yang dimulai Agustus baru berakhir di bulan itu.
     */
    public static function dariTanggal(\DateTimeInterface $tanggal): array
    {
        $tahun = (int) $tanggal->format('Y');
        $bulan = (int) $tanggal->format('n');

        if ($bulan >= 8) {
            return [$tahun . '/' . ($tahun + 1), 'gasal'];
        }

        return $bulan === 1
            ? [($tahun - 1) . '/' . $tahun, 'gasal']
            : [($tahun - 1) . '/' . $tahun, 'genap'];
    }

    /** Jendela tanggal sebuah periode, diturunkan dari kalender akademik. */
    public static function jendela(string $tahunAkademik, string $semester): array
    {
        [$awal, $akhir] = array_map('intval', explode('/', $tahunAkademik));

        return $semester === 'gasal'
            ? [sprintf('%d-08-01', $awal), sprintf('%d-01-31', $akhir)]
            : [sprintf('%d-02-01', $akhir), sprintf('%d-07-31', $akhir)];
    }

    /** Periode sebelum/sesudah, satu langkah semester. */
    public static function langkah(string $tahunAkademik, string $semester, int $arah): array
    {
        [$a, $b] = array_map('intval', explode('/', $tahunAkademik));

        if ($arah > 0) {
            return $semester === 'gasal'
                ? [$tahunAkademik, 'genap']
                : [($a + 1) . '/' . ($b + 1), 'gasal'];
        }

        return $semester === 'genap'
            ? [$tahunAkademik, 'gasal']
            : [($a - 1) . '/' . ($b - 1), 'genap'];
    }

    /**
     * Pastikan baris periode di sekitar hari ini sudah ada.
     *
     * Periode TIDAK diketik siapa pun: ia fakta kalender, seperti di Simadu
     * yang daftarnya sudah terisi sampai bertahun-tahun ke belakang tanpa ada
     * yang pernah "membuat" 2019/2020 Gasal. Kaprodi hanya memutuskan mana yang
     * aktif dan prodi mana yang ikut — dua hal yang memang tak bisa disimpulkan
     * sistem dari kalender.
     *
     * Idempoten: dipanggil tiap kali halaman Periode dibuka.
     */
    public static function siapkanKalender(int $mundur = 2, int $maju = 2): void
    {
        [$tahun, $semester] = static::dariTanggal(now());

        // Mundur dulu ke titik awal, lalu maju satu-satu supaya prodi tiap
        // periode baru bisa dipilihkan bergantian dari periode SEBELUMNYA.
        foreach (range(1, $mundur) as $ignored) {
            [$tahun, $semester] = static::langkah($tahun, $semester, -1);
        }

        foreach (range(0, $mundur + $maju) as $ignored) {
            static::pastikanAda($tahun, $semester);
            [$tahun, $semester] = static::langkah($tahun, $semester, 1);
        }
    }

    /**
     * Buat baris periode bila belum ada, dengan prodi peserta dipilihkan
     * BERGANTIAN dari periode sebelumnya.
     *
     * Magang berjalan selang-seling: saat Teknik Informatika magang, Teknologi
     * Rekayasa Komputer tidak, dan sebaliknya. Polanya dipakai sebagai USULAN,
     * bukan aturan yang ditanamkan di kode — dua angkatan belum cukup jadi
     * hukum, dan aturan yang tertanam berarti harus deploy ulang begitu
     * kurikulum bergeser. Kaprodi selalu bisa mengubahnya.
     */
    public static function pastikanAda(string $tahunAkademik, string $semester): self
    {
        $ada = static::where('academic_year', $tahunAkademik)
            ->where('semester', $semester)->first();

        if ($ada) {
            return $ada;
        }

        [$mulai, $selesai] = static::jendela($tahunAkademik, $semester);
        [$tSebelum, $sSebelum] = static::langkah($tahunAkademik, $semester, -1);

        $sebelumnya = static::where('academic_year', $tSebelum)
            ->where('semester', $sSebelum)->first();

        $usulan = $sebelumnya
            ? array_values(array_diff(Student::PRODI, $sebelumnya->study_programs ?? []))
            : [];

        return static::create([
            'academic_year'   => $tahunAkademik,
            'semester'        => $semester,
            'start_date'      => $mulai,
            'end_date'        => $selesai,
            'duration_months' => self::DEFAULT_DURATION_MONTHS,
            'study_programs'  => $usulan,
            'is_active'       => false,
        ]);
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
