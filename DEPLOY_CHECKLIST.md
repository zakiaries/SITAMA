# Checklist Deploy SIMAMA (Laravel web + API mobile)

Panduan menyiapkan aplikasi web SIMAMA ke server produksi (untuk uji pengguna Bab IV
maupun pemakaian berkelanjutan). Chatbot memakai PHP murni (tanpa layanan Python),
jadi ini deploy Laravel standar.

---

## 0. PRA-SYARAT — WAJIB dibereskan (blocker)

- [ ] **Set `SEED_KAPRODI_PASSWORD` (& username/email) di `.env`** ke nilai kuat sebelum
      `db:seed`. Deploy bersih TIDAK lagi membawa akun uji `password123` — DB dibangun dari
      migrasi + seeder, satu-satunya akun awal adalah Kaprodi dari `.env` ini.
      ⚠️ Bila password mengandung `#`/spasi/karakter khusus, **bungkus tanda kutip**
      (`SEED_KAPRODI_PASSWORD="P@ss#word1"`) — tanpa kutip, `#` dst dianggap komentar & password terpotong diam-diam.
- [ ] `APP_DEBUG=false` + `APP_ENV=production` (default lokal ON — stack trace bocor bila dibiarkan).
- [ ] `APP_URL` diisi URL publik https (menentukan isi QR seminar → agar bisa discan dari HP).

---

## 1. Server & runtime

- [ ] PHP **8.2+** dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`,
      `fileinfo`, `ctype`, `json`, `bcmath` (gd+zip diperlukan Sastrawi & Maatwebsite Excel).
- [ ] MySQL / MariaDB.
- [ ] Composer.
- [ ] Web server (Nginx/Apache) dengan document root ke folder **`public/`**
      (atau platform PaaS: Railway/Render/Forge).
- [ ] Folder `storage/` dan `bootstrap/cache/` dapat ditulis (permission).

## 2. Berkas .env produksi

```
APP_NAME=SIMAMA
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # php artisan key:generate
APP_URL=https://domain-anda        # WAJIB benar (QR & tautan)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simama
DB_USERNAME=...
DB_PASSWORD=...

CACHE_DRIVER=file          # cukup; index TF-IDF chatbot pakai cache ini
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# Mail aktivasi pembimbing industri (opsional; jika kosong, pakai fallback link manual)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
```

## 3. Langkah build & migrasi (di server)

> ✅ Skema dibangun sepenuhnya dari migrasi (migrasi tabel dasar sudah direkonstruksi).
> `php artisan migrate` jalan bersih di DB kosong; `db:seed` membuat akun Kaprodi awal +
> rubrik penilaian + FAQ chatbot. Tidak perlu impor SQL manual (`database/sitama.sql` lama
> sudah dihapus — dulu dump DEV berisi data/akun/token uji).

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate            # sekali, jika APP_KEY belum ada
php artisan migrate --force         # bangun seluruh skema dari nol
php artisan db:seed --force         # akun Kaprodi awal + rubrik penilaian + FAQ chatbot
php artisan storage:link            # agar file upload (bukti/sertifikat/laporan) bisa dibuka
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Kredensial Kaprodi awal diatur lewat `.env` (`SEED_KAPRODI_USERNAME` / `_EMAIL` /
> `_PASSWORD`). **Set `SEED_KAPRODI_PASSWORD` ke nilai kuat sebelum `db:seed`**, atau ganti
> password lewat menu profil segera setelah login pertama.

## 4. Data awal (bootstrap)

- [x] **1 akun Kaprodi** (superadmin) — otomatis dari `db:seed` (`DatabaseSeeder`, kredensial dari `.env`).
- [x] **Rubrik penilaian** (4 komponen + 12 rinci) — otomatis dari `db:seed` (`RubrikPenilaianSeeder`).
- [x] **KB chatbot** — otomatis dari `db:seed` (`ChatbotKnowledgeSeeder`).
- [ ] Akun **dosen** (role `lecturer`) — buat lewat login Kaprodi (menu Dosen) setelah deploy.
- [ ] Beberapa **perusahaan afiliasi + lowongan** (dengan bidang) — input lewat Kaprodi agar
      demo lowongan/rekomendasi chatbot ada isinya (opsional; belum ada demo seeder).

## 5. HTTPS

- [ ] Pasang SSL: Let's Encrypt (certbot) / Cloudflare / bawaan PaaS.
      Kamera scan QR & keamanan umum memerlukan https.

## 6. Verifikasi pasca-deploy (smoke test)

- [ ] Login tiap peran → dashboard tampil (mahasiswa/dosen/pembimbing industri/kaprodi).
- [ ] Coba akses lintas peran (mis. mahasiswa buka `/kaprodi/...`) → harus **403**.
- [ ] Chatbot menjawab FAQ & memberi rekomendasi lowongan.
- [ ] Upload file (bukti magang) tersimpan & bisa dibuka (storage:link).
- [ ] **Scan QR seminar dari HP asli** (bukan localhost) → halaman "Saya Hadir" terbuka.
- [ ] Notifikasi masuk saat ajukan magang / seminar / selesai magang.

## 7. Mobile (tugas partner)

- [ ] Base URL API Flutter (`lib/config/app_config.dart`) diarahkan ke URL deploy (bukan `10.0.2.2`/localhost).

## 8. Keterbatasan yang perlu disadari sebelum uji lapangan

- **Absensi seminar bisa di-abuse**: link/QR bila di-share ke grup, mahasiswa di luar
  ruangan tetap bisa menandai hadir (login hanya mencegah NIM palsu & dobel, bukan
  kehadiran fisik). Fitur "QR berputar (rotating)" belum dibangun — putuskan apakah
  perlu dibuat sebelum uji nyata.
- Belum ada suite pengujian otomatis; verifikasi masih manual → sebaiknya lakukan
  satu putaran uji fungsional menyeluruh sebelum menghadapkan ke 72 mahasiswa.
- Mail bisa gagal diam-diam bila SMTP belum dikonfigurasi → gunakan fallback link manual
  aktivasi pembimbing industri.

---

## Rekomendasi tahapan (jangan langsung 72 mahasiswa)
1. Beresin #0–#5 (config produksi + data awal).
2. Uji fungsional menyeluruh (semua alur, tiap peran).
3. Soft-launch ke grup kecil (1 kelas / 5–10 orang) ~1 minggu.
4. Baru full test 72 mahasiswa untuk Bab IV.
