# HANDOFF — SIMAMA (untuk sesi Claude berikutnya)

> Baca file ini dulu untuk melanjutkan. State detail juga ada di memory
> (`project_sitama_progress.md`, `project_sitama_web.md`). Deploy: lihat `DEPLOY_CHECKLIST.md`.

## Apa ini
- **SIMAMA** = Sistem Informasi Manajemen Magang. **Laravel 9 (web) + REST API** untuk app **Flutter (mobile)**. MySQL (XAMPP). Bahasa UI: Indonesia.
- Repo `zakiaries/SITAMA.git`, branch **`web`**. Path web: `D:\SITAMA\SITAMA-web`.
- **Tugasku (dan user) = sisi WEB saja.** Partner (`zakiaries`) pegang **mobile** (`Api/` + `mobile/`); dia menyesuaikan mobile mengikuti perubahan web.
- Judul TA (terkunci): "Sistem Informasi Manajemen Magang dengan Chatbot Rekomendasi Berbasis TF-IDF dan Cosine Similarity". Dulu bernama SITAMA → di laporan v4 di-rename **SIMAMA** (sistem baru berdiri sendiri, terpisah dari SITAMA kampus yang = TA+magang).

## Cara jalankan / uji
- **XAMPP MySQL harus ON** (sering user lupa nyalakan → semua query gagal "connection refused").
- `php artisan serve --port=8000` (bukan lewat preview tool; launch.json ada tapi bukan di lokasi yang dicari tool).
- Browser MCP: pakai **claude-in-chrome** (load via ToolSearch). "Claude_Browser" tidak tersedia. Chrome kadang tab-nya hilang sendiri.
- **Akun uji (password semua `password123`, DIUBAH sebelum deploy!):** `kaprodi`, `dosen1` (Dr. Siti Rahayu, lecturer_id=1), `mahasiswa1` (Budi Santoso, student_id=1), `pembimbing_industri` (lecturer_industry).
- Uji flow yang mutasi DB: pakai transaksi rollback ATAU HTTP + cleanup (jangan kotori DB user).

## 4 peran (route prefix, semua di web.php + middleware `role`)
- `/mahasiswa/*` (role:student + student.approved), `/dosen/*` (role:lecturer), `/dosen-industri/*` (role:lecturer_industry), `/kaprodi/*` (role:kaprodi).

## Fitur utama (semua WEB sudah jadi & teruji fungsional sesi ini)
- **Chatbot HYBRID** (`app/Services/Chatbot/`): 2 korpus TF-IDF — FAQ (`chatbot_knowledges`, CRUD kaprodi) + Lowongan (`job_listings`). Praproses Sastrawi. Ambang 0.25 (FAQ) / 0.18 (lowongan). Cache index (kunci = hash isi, +versi). `chat_logs` + statistik kaprodi. Rekomendasi tempat magang by bidang.
- **Lowongan afiliasi**: kaprodi CRUD + terisi otomatis saat approve magang (hook `syncDirectoryListing`). Filter bidang + wilayah. Kolom `bidang` (JobListing::BIDANG_OPTIONS).
- **Seminar MODEL SESI-GRUP** (baru, hasil rework sesi ini): 1 sesi = 1 dosen + banyak penyaji. Alur: dosen buat sesi (draft) → mahasiswa isi ketersediaan → dosen finalisasi (scheduled) → audiens absen **wajib login** (1 akun=1, min **15**) → dosen **sahkan** (completed). Kaprodi info-only. Tabel: `seminars`(+lecturer_id,witnessed_at), `seminar_presenters`, `seminar_attendances`(+student_id). Berita acara PDF.
- Magang: ajukan (afiliasi/mandiri + bukti) → kaprodi approve (buat company/pembimbing industri/internship/token+email aktivasi). Logbook, bimbingan, laporan ACC, nilai multi-penilai (scorer_type lecturer/lecturer_industry), selesai magang (requestFinish gated → kaprodi approveFinish). Notifikasi, ekspor Excel.

## Yang dikerjakan & di-commit sesi ini (branch web, sudah push; lokal SUDAH sinkron s/d `7784264`)
- Chatbot hybrid + bidang + filter (`fcb6e09`).
- Keamanan: **middleware peran CheckRole** (dulu tidak ada — siapa pun login bisa akses `/kaprodi/*`!), **fix IDOR** bimbingan/logbook dosen & industri, approveFinish wajib finish_requested, **hapus alur internship-group mati** (`64c4df2`).
- **Rework seminar sesi-grup** + 2 bug fix: `notifications.category` ENUM→VARCHAR (dulu bikin notif pengajuan_magang/selesai_magang/seminar GAGAL di strict mode), form finalisasi/batalkan dipisah (`e926edf`).
- Partner lalu push mobile menyesuaikan model seminar + lowongan (`7784264`).

## Status testing (sesi ini)
- ✅ Seminar full e2e, ✅ ajukan-magang→approve (rantai lengkap + notif + hook direktori), ✅ akses lintas peran (403), ✅ chatbot, ✅ smoke 31 halaman 4 peran (200).
- ⬜ BELUM diuji e2e (CRUD sederhana, sudah smoke 200): logbook+notif dosen, bimbingan approve/revisi + guard IDOR, laporan ACC, input nilai 2 penilai, gerbang selesai-magang.

## ✅ BLOCKER DEPLOY — SUDAH DIBERESKAN (2026-07-25, jalur PROPER; di-commit ke branch `lanang`: c1294d1 + 7e551b6, BUKAN `web`, belum push)
1. **Migrasi tabel dasar direkonstruksi.** 15 file base baru (`0001_01_01_*`, `2026_04_24_*`,
   `2026_04_29_000002/3`) menggabungkan alter make-nullable & lecturer_industry. `migrate:fresh`
   jalan dari nol; skema hasilnya **byte-identical** dgn DB live (diff kosong, teruji di DB
   scratch `sitama_migtest` lalu di-drop). 26 migrasi folder tetap jalan di atasnya.
   `Sanctum::ignoreMigrations()` ditambah di AppServiceProvider (personal_access_tokens dikelola
   migrasi app, bukan paket).
2. **`database/sitama.sql` DIHAPUS** (`git rm`, staged) — skema kini dari migrasi, dump dev
   lama tak dipakai. (Catatan: token dev localhost masih ada di histori git; low-risk.)
3. **`DatabaseSeeder` diisi** (idempoten): akun Kaprodi awal (kredensial dari `.env`
   `SEED_KAPRODI_*`) + `RubrikPenilaianSeeder` (4+12 komponen) + `ChatbotKnowledgeSeeder`.
   `migrate:fresh --seed` diverifikasi: 1 kaprodi, 4/12 rubrik, 20 FAQ.
4. **`.env.example` + `DEPLOY_CHECKLIST.md` diperbarui** (SIMAMA, `SEED_KAPRODI_*`, prosedur
   migrate/seed yg benar). **Config `.env` PRODUKSI tetap dilakukan di server**: `APP_DEBUG=false`,
   `APP_ENV=production`, `APP_URL` https publik, DB creds, `SEED_KAPRODI_PASSWORD` kuat.

**Sisa untuk deploy (bukan kode):** provisioning server (PHP 8.2+ ext, MySQL, web root ke
`public/`, HTTPS) + isi `.env` produksi + `composer install --no-dev` + migrate/seed/storage:link.
Opsional/kualitas: demo seeder lowongan, reset-password mandiri, tes otomatis, rotating QR seminar.

## OPEN ITEMS / next steps (prioritas)
1. **Uji fungsional sisa alur** (logbook/bimbingan/laporan/nilai/selesai) — user izinkan testing SELAMA TIDAK EDIT KODE.
2. **Kelayakan deploy**: beresin `DEPLOY_CHECKLIST.md` — terutama `APP_DEBUG=false`, `APP_URL` publik (agar QR seminar bisa discan HP), **buat akun kaprodi awal** (DatabaseSeeder KOSONG), ganti password uji.
3. **Seeder akun awal** (kaprodi + dosen) — belum dibuat.
4. **Absensi seminar bisa di-abuse** (link di-share → hadir dari luar ruangan). Solusi disepakati arah: **rotating QR** (token berbasis waktu + halaman auto-refresh) — BELUM dibangun. Tanyakan user apakah jadi dibangun.
5. **Laporan (v4, file di `C:\Users\ASUS\Documents\TA\`)**: bagus & akurat. Sisa perbaikan: **Bootstrap masih diklaim** (2.3.2c + 3.4.2) padahal UI web pakai CSS custom `sitama.css` (bukan Bootstrap) — perlu dikoreksi. Cek klaim FK/cascade (NF-05). BAB IV (hasil uji) & V + semua Gambar/diagram belum ada.
6. Dospem minta: **deploy + uji 72 mahasiswa** (Bab IV), UAT ≥20 responden.

## Gotcha
- Chatbot cache dikunci hash isi KB + versi index (`ChatbotService::INDEX_VERSION`) — kalau ubah praproses/logika, naikkan versi atau `php artisan cache:clear`.
- `notifications.category` sekarang VARCHAR (jangan balik ke enum).
- File uji sementara di scratchpad boleh diabaikan.
