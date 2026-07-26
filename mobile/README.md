# SIMAMA Mobile

Aplikasi **mobile** untuk SIMAMA (Sistem Informasi Magang) — hanya 3 role:
**Mahasiswa, Dosen, Pembimbing Industri**. Peran Kaprodi tetap di web.

Folder ini adalah tempat project aplikasi mobile (rekomendasi: **Flutter**). Aplikasi mobile
**tidak** menjalankan kode web; ia **memanggil REST API** dari backend Laravel yang sama
(project di folder induk `SIMAMA WEB/`).

## Arsitektur singkat
```
SIMAMA WEB/            ← Backend Laravel (web + API) + MySQL (DB yang sudah ada)
└── mobile/            ← Project aplikasi mobile (Flutter), memanggil /api dari backend
```

- **Auth:** token (Laravel Sanctum). Login → dapat Bearer token → dikirim di header tiap request.
- **Database:** memakai DB yang sudah ada (tidak menambah tabel). Tabel `personal_access_tokens`
  untuk token sudah tersedia.
- **Aturan:** tidak mengubah kode web atau skema DB. API hanya "membaca/menulis" lewat logika yang
  sudah ada.

## Base URL API (saat development)
Jalankan backend agar bisa diakses dari perangkat:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
- **Emulator Android:** `http://10.0.2.2:8000/api`
- **HP fisik (satu WiFi):** `http://<IP-laptop>:8000/api`

## Endpoint yang sudah tersedia (Bagian A — auth)
| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/api/login` | Login (username, password) → token + data user |
| GET  | `/api/me` | Data user yang sedang login (butuh token) |
| POST | `/api/logout` | Hapus token aktif (butuh token) |

Endpoint fitur per role menyusul sesuai `PROMPT-sitama-mobile.md` (bab 8).

## Cara menaruh project Flutter di sini
Dari folder `SIMAMA WEB/`:
```bash
cd mobile
flutter create .        # membuat project Flutter di dalam folder ini
```
> Flutter otomatis membuat `.gitignore` sendiri (meng-ignore `build/`, `.dart_tool/`, dll).
> Biarkan folder `mobile/` ikut repo utama; **jangan** `git init` lagi di dalam sini.
