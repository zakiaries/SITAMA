# Catatan Sinkronisasi Mobile (untuk partner — sisi `Api/` + Flutter)

Sesi web (branch `web`, commit `c1294d1`..`60ee27b`) menambah beberapa aturan & perubahan.
Sisi **web sudah beres**; berikut yang **perlu disamakan di mobile** biar konsisten.

## 1. Validasi input — SAMAKAN di controller `Api/*`
Aturan ini ada di controller web tapi **belum tentu ada di endpoint API mobile**. Cek & tambahkan
di `App\Http\Controllers\Api\*` (registrasi, ajukan magang, logbook, bimbingan):

| Field | Aturan (Laravel) | Sumber web |
|---|---|---|
| **NIM** (username, saat register) | `regex:/^\d+\.\d+\.\d+\.\d+\.\d+$/` (5 kelompok angka, mis. `3.34.23.2.12`) | `Auth/RegisterController` |
| **Nomor HP** (pic_phone) | `regex:/^[0-9()+\-\s]{7,20}$/` | `Mahasiswa/MagangRequestController` |
| **File bimbingan** | `nullable\|file\|mimes:pdf,doc,docx\|max:10240` | `Mahasiswa/BimbinganController` |
| **Tanggal logbook & bimbingan** | `required\|date\|before_or_equal:today` (tak boleh masa depan) | `Mahasiswa/LogBook`/`BimbinganController` |
| Email | `email` | (sudah standar) |
| Nilai/skor | `numeric\|min:0\|max:100` | `Dosen`/`DosenIndustri` |

> Pola NIM & HP sengaja **longgar di nilai** (biar D3/D4, angkatan baru, prodi lain tak keblokir);
> legitimasi tetap lewat approve kaprodi.

## 2. Timezone — sudah otomatis untuk API, cek sisi Flutter
`config/app.php` diubah ke `Asia/Jakarta` (WIB). Karena API bagian dari app Laravel yang sama,
**waktu dari API otomatis WIB** — tak perlu ubah `Api/*`. Tapi kalau **Flutter memformat/menghitung
waktu sendiri**, pastikan pakai WIB (jangan asumsikan UTC).

## 3. Rebrand SITAMA → SIMAMA
Nama tampilan sudah SIMAMA di web & mobile (`0d7f3e6`, `9a7e94d`). **Identitas teknis sengaja
dibiarkan** (aman jangan diubah): `pubspec name: sitama_mobile`, `applicationId com.sitama.sitama_mobile`,
kunci token `sitama_token`, class `SitamaApp`. Mengubahnya = ganti identitas app di store & rusak signing.

## 4. Fitur/endpoint baru di web (opsional — kalau mau parity di mobile)
Kalau mobile ingin fitur yang sama, tambah endpoint padanannya:
- Mahasiswa: **edit logbook** (PUT), **batal pengajuan magang** pending (DELETE), **hapus bimbingan**
  belum-approve (DELETE).
- Kaprodi: **pulihkan mahasiswa ditolak** (tab "Ditolak" → approve).
- Dosen: **edit detail sesi seminar** + ubah jadwal terjadwal.

## 5. Skema DB / deploy
Migrasi tabel dasar sudah direkonstruksi → `php artisan migrate --seed` jalan dari nol. API pakai DB
yang sama, jadi tak ada kerja mobile khusus; ini info saja.
