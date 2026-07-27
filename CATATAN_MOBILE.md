# Catatan Sinkronisasi Mobile (untuk partner — sisi `Api/` + Flutter)

Update 2026-07-27: **Fase 1 (backend/API) SUDAH dikerjakan & ditest** (commit `22fde6a`).
Sisa = **Fase 2 (UI Flutter)** yang perlu kamu build/cek di emulator.

## ✅ Fase 1 — API sudah disamakan dengan web (SELESAI)
Endpoint baru di `routes/api.php` + `app/Http/Controllers/Api/*`:
- `PUT /api/mahasiswa/logbook/{id}` — edit logbook.
- `DELETE /api/mahasiswa/bimbingan/{id}` — hapus bimbingan yang belum di-approve.
- `DELETE /api/mahasiswa/ajukan-magang/{id}` — batal pengajuan magang pending.
- `PUT /api/dosen/seminar/{id}` — edit judul/deskripsi sesi.
- `GET /api/dosen/seminar/{id}/qr` — token QR **rotating** terkini (`{url, rt, interval}`).
- `POST /api/lupa-password` + `POST /api/reset-password` — reset password.
Validasi disamakan (NIM, HP, file bimbingan mimes, tanggal ≤ hari ini) + rate-limit login API
+ throttle reset. Semua lolos 5 test `ApiParityTest` (Sanctum).

## ⚠️ Fase 2 — UI Flutter SUDAH DITULIS, tapi WAJIB DI-BUILD-CHECK (commit `aff482b`)
Kode Dart ditulis mengikuti pola yang ada, TAPI **belum bisa divalidasi** (laptop Lanang: Flutter
SDK 3.9.2 < syarat project Dart ^3.12.2, `flutter analyze` gagal resolve). **Partner WAJIB cek:**
```
cd mobile && flutter pub get && flutter analyze && flutter run
```
Yang sudah ditambahkan di `mobile/lib`:
1. `screens/mahasiswa/logbook_screen.dart` — tombol Edit (form dipakai ulang) + date picker ≤ hari ini.
2. `screens/mahasiswa/bimbingan_screen.dart` — tombol Hapus (status ≠ approved) + date picker ≤ hari ini.
3. `screens/mahasiswa/ajukan_magang_screen.dart` — tombol Batalkan (pengajuan pending).
4. `screens/dosen/seminar_screen.dart` — tombol Ubah Detail (judul+deskripsi) + `_QrDialog` yang
   auto-refresh tiap 10 dtk via `GET /dosen/seminar/{id}/qr` (QR rotating).
5. `screens/auth/forgot_password_screen.dart` — disambung ke `POST /lupa-password` (dulu stub).

Kalau ada error saat analyze/build, kemungkinan besar hal kecil (tipe/argumen) — perbaiki lalu commit.

## Catatan tetap
- Rebrand nama tampilan mobile → SIMAMA sudah (`9a7e94d`). Identitas teknis (`sitama_mobile`,
  `applicationId com.sitama.*`, `_tokenKey`, class `SitamaApp`) **sengaja dibiarkan** — jangan diubah.
- Timezone: config bersama, API otomatis WIB. Pastikan Flutter tidak asumsi UTC saat format waktu.
