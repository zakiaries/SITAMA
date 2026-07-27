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

## ⬜ Fase 2 — yang perlu DIKERJAKAN di Flutter (`mobile/lib`)
Tambahkan UI yang memanggil endpoint di atas:
1. **Logbook**: tombol Edit (panggil `PUT /logbook/{id}`).
2. **Bimbingan**: tombol Hapus (muncul saat status ≠ approved) → `DELETE /bimbingan/{id}`.
3. **Ajukan magang**: tombol Batalkan pada pengajuan pending → `DELETE /ajukan-magang/{id}`.
4. **Dosen seminar**: tombol Ubah Detail (judul/deskripsi) → `PUT /dosen/seminar/{id}`.
5. **⚠️ QR seminar (PENTING)**: QR daftar hadir sekarang **berputar** tiap 20 dtk. QR statis lama
   **akan ditolak** web. Ubah layar QR dosen agar memanggil `GET /dosen/seminar/{id}/qr` secara
   berkala (mis. tiap 10 dtk) dan render ulang QR dari field `url` yang dikembalikan. (Sementara,
   field `hadir_url` di daftar sesi sudah menyertakan `rt` terkini, jadi QR valid saat layar dibuka
   tapi belum auto-refresh.)
6. **Reset password**: `forgot_password_screen` cukup panggil `POST /api/lupa-password` (link reset
   dikirim ke email → dibuka di web). Endpoint `POST /api/reset-password` tersedia bila mau in-app.

## Catatan tetap
- Rebrand nama tampilan mobile → SIMAMA sudah (`9a7e94d`). Identitas teknis (`sitama_mobile`,
  `applicationId com.sitama.*`, `_tokenKey`, class `SitamaApp`) **sengaja dibiarkan** — jangan diubah.
- Timezone: config bersama, API otomatis WIB. Pastikan Flutter tidak asumsi UTC saat format waktu.
