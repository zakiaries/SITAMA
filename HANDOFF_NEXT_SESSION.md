# Handoff — Sesi Berikutnya (SIMAMA)

Tempel isi di bawah (mulai dari "Lanjutkan pengerjaan") sebagai pesan pertama di chat baru.

---

Lanjutkan pengerjaan **SIMAMA** (sistem informasi magang, Laravel 9 + Flutter, TA D3 Polines).
Kode di `D:\SITAMA\SITAMA-web`, branch produksi **`web`** (repo `zakiaries/SITAMA`).
Baca dulu memory-ku (MEMORY.md + project_*.md) untuk konteks.

## Status: LIVE & sehat

- **Web + API ter-deploy** di **https://simama.site** (HTTPS aktif).
- VPS Rumahweb Ubuntu, IP **202.10.38.76** → `ssh root@202.10.38.76` (pakai `tmux`, koneksi suka drop).
  Login pakai password (Claude tak punya akses SSH — semua perintah server dijalankan user).
- Stack **Docker Compose** di `/var/www/simama`: Caddy (auto-HTTPS) + PHP-FPM + MariaDB. Panduan: `DEPLOY_DOCKER.md`.
- Login Kaprodi: `kaprodi` / `SimamaKaprodi2026` (harus diganti).
- **SMTP AKTIF** (Gmail App Password, pengirim `SIMAMA <lanangbayup@gmail.com>`) → reset password
  mandiri & email aktivasi pembimbing industri sudah berfungsi. Uji: `php artisan simama:tes-email <tujuan>`.
- **54 feature test** (`php artisan test`; butuh MySQL XAMPP lokal nyala — sering mati/crash, nyalakan dulu).

### Alur update ke server (wajib urut)
```bash
cd /var/www/simama
git checkout -- storage 2>/dev/null   # buang perubahan .gitignore placeholder
git pull origin web
docker compose exec app php artisan migrate --force   # bila ada migrasi baru
docker compose exec app php artisan route:clear       # bila ada rute baru
docker compose exec app php artisan view:clear        # bila ada perubahan blade (SERING)
docker compose exec app php artisan config:clear      # bila .env berubah
docker compose up -d --build                          # HANYA bila Dockerfile berubah
```
Menjalankan artisan sebagai user web (hindari file cache/log jadi milik root):
`docker compose exec -u www-data -e HOME=/tmp app php artisan ...`

## Yang TERSISA

1. **Mobile Flutter belum pernah diuji** (tugas partner `zakiaries`). Base URL sudah ke produksi
   (`AppConfig.useProduction = true` → `https://simama.site/api`). Partner harus
   `flutter pub get && flutter build apk --release` (ada dependensi baru `mobile_scanner`).
   **Penting:** API berubah sesi ini — `GET /api/mahasiswa/ajukan-magang` kini mengembalikan
   `has_lecturer`, dan `POST` ditolak 422 bila mahasiswa belum diplot dospem.
2. **Jam Operasional di halaman Kontak belum dikonfirmasi** (Senin–Jumat 08.00–16.00, Sabtu
   08.00–12.00). Angka lama yang kemungkinan karangan seperti data kontak sebelumnya — sengaja
   tidak ditebak. Tanyakan user: benarkan / ganti / hapus kartunya.
3. **Dua command data dummy yang tumpang tindih**: `simama:mahasiswa-dummy` (Claude) dan
   `simama:simulasi-magang` (partner). Perlu disepakati satu supaya data uji tak saling menimpa.
4. Opsional: pindah pengirim email ke `noreply@simama.site` (domain sudah dimiliki) lewat
   Brevo/Resend/Mailgun + record SPF/DKIM. Nol perubahan kode, cukup `MAIL_*` di `.env`.

## Selesai di sesi 2026-07-30 (commit `e8fd24c` … `48ee17e`, semua pushed)

- **PRIORITAS 1 lama (bimbingan tak tersimpan) — SELESAI & diagnosanya dulu SALAH ARAH.**
  Penyebab asli `Guidance::count()=0` adalah **modal Tambah Bimbingan yang hilang** (sudah difix
  `914ce2d`); kegagalan tes partner sesudahnya karena **akun mahasiswa belum diplot dospem**.
  Batas upload PHP **tidak pernah** jadi penyebab (server sudah 12M/15M sebelum sesi ini) dan
  rebuild Docker tak dibutuhkan. Bimbingan kini tersimpan dengan lampiran & di-ACC dosen.
- `e8fd24c` Kegagalan validasi kini **dicatat ke log**; POST yang body-nya dibuang karena
  melebihi `post_max_size` tak lagi dibalas "sesi kedaluwarsa" tapi "file terlalu besar".
- `6ade180` **Foto profil** tampil di semua tempat (komponen `<x-avatar>`, 21 titik).
- `3a284ea` **Ikon mata** show/hide di SEMUA field password (15 field).
- `d69de14` **Magang wajib punya dospem** sebelum terbentuk — gerbang di Ajukan Magang (mahasiswa),
  approve pengajuan & catat-magang (kaprodi), dan API mobile.
- `0da2b07` **Kontak POLINES resmi** (data lama karangan: alamat Malang!) + form "Kirim Pesan"
  atrapa diganti; **detail mahasiswa portal industri** diperkaya.
- `29b9ac3`/`88f2e55` Command **`simama:mahasiswa-dummy`** (3 tahap, idempoten, `--hapus`).
- `2680e93` **Fix hitungan mahasiswa bimbingan** di Data Dosen kaprodi (tab industri selalu 0).
- `fb608e4` **Penanda unggahan baru** (badge sidebar + titik merah kartu) untuk dosen & industri.
- `48ee17e` Command **`simama:tes-email`** untuk mendiagnosa SMTP.

## Catatan penting (mahal didapat, jangan diulang)

- **`LOG_LEVEL` server sudah dinaikkan `error` → `warning`.** Sebelumnya semua kegagalan validasi
  dan penolakan guard tak meninggalkan jejak apa pun, sehingga diagnosa terpaksa menebak.
  **Jangan turunkan lagi.** Kalau ada laporan "gagal tanpa pesan", cek `storage/logs/laravel.log` dulu.
- **Jangan percaya "tidak ada error di log" sebagai bukti** sebelum memastikan log memang ditulis
  (level, channel, kepemilikan file).
- Saat mengubah banyak blade, **jangan pakai regex/script otomatis** — sudah pernah merusak layout.
  Edit manual, lalu verifikasi `php artisan view:cache`.
- **Nama kolom yang mudah keliru**: komentar dosen di logbook = `lecturer_note` (BUKAN `note`);
  komentar industri = `industry_note`. Keduanya kolom terpisah, jadi penanda dua peran saling bebas.
- **Dua kolom dospem**: `students.lecturer_id` (hasil plot Kaprodi, sumber kebenaran) dan
  `internships.lecturer_id` (dipakai portal dosen memfilter). `assignLecturer` sudah menyinkronkan
  keduanya, dan sejak `d69de14` magang tak bisa lahir tanpa dospem.
- Guard yang sudah ada: **logbook** butuh magang aktif; **bimbingan** butuh dospem; **ajukan magang**
  butuh dospem. Kalau partner melapor "tak bisa mengisi", cek dulu apakah akunnya memenuhi syarat —
  bukan bug.
- Rubrik nilai sesuai form resmi (dosen: Proposal 20% + Laporan 80%; industri 8 komponen; skala 1–10;
  nilai akhir = rata dosen + rata industri, maks 20). Lihat `project_nilai_rework.md`.
- Semua pekerjaan sudah ter-commit & ter-push ke branch `web`.
