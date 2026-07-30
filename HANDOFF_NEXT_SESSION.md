# Handoff — Sesi Berikutnya (SIMAMA)

Tempel isi di bawah (mulai dari "Lanjutkan pengerjaan") sebagai pesan pertama di chat baru.

---

Lanjutkan pengerjaan **SIMAMA** (sistem informasi magang, Laravel 9 + Flutter, TA D3 Polines).
Kode di `D:\SITAMA\SITAMA-web`, branch produksi **`web`** (repo `zakiaries/SITAMA`).
Baca dulu memory-ku (MEMORY.md + project_*.md) untuk konteks.

## Status: SUDAH LIVE
- **Web + API ter-deploy** di **https://simama.site** (HTTPS aktif).
- VPS Rumahweb Ubuntu, IP **202.10.38.76** → `ssh root@202.10.38.76` (pakai `tmux`, koneksi suka drop).
- Stack **Docker Compose** di `/var/www/simama`: Caddy (auto-HTTPS) + PHP-FPM + MariaDB. Panduan: `DEPLOY_DOCKER.md`.
- Login Kaprodi: `kaprodi` / `SimamaKaprodi2026` (harus diganti).
- Test: **30 feature test** (`php artisan test`; butuh MySQL XAMPP lokal nyala — sering mati, nyalakan dulu).

### Alur update ke server (wajib urut)
```bash
cd /var/www/simama
git checkout -- storage 2>/dev/null   # buang perubahan .gitignore placeholder
git pull origin web
docker compose exec app php artisan migrate --force   # bila ada migrasi baru
docker compose exec app php artisan route:clear       # bila ada rute baru
docker compose exec app php artisan view:clear        # bila ada perubahan blade
docker compose up -d --build                          # HANYA bila Dockerfile berubah
```

## PRIORITAS 1 — Bimbingan tidak tersimpan (web)
Mahasiswa isi bimbingan → tidak tersimpan (`App\Models\Guidance::count() = 0`), jadi tak muncul
di mahasiswa, dosen, maupun industri. **Logbook sudah normal** (tersimpan & muncul).

Yang sudah dipastikan:
- Modal "Tambah Bimbingan" **sudah ada** di server (dulu hilang, sudah difix).
- **Tidak ada exception di `storage/logs/laravel.log`** → berarti **validasi gagal** (Laravel redirect tanpa menulis log).
- Beda logbook vs bimbingan: bimbingan punya **upload file** + `enctype=multipart`.
- Batas upload PHP sudah dinaikkan di `Dockerfile` (commit `5f0db35`: upload 12M / post 15M) —
  **butuh `docker compose up -d --build`**, verifikasi:
  `docker compose exec app php -i | grep -E "upload_max_filesize|post_max_size"`.

**Langkah berikutnya:** minta partner tambah bimbingan **tanpa lampiran file** dulu.
Berhasil → biang keroknya file/batas PHP. Gagal → minta **teks pesan merah** di halaman
(error validasi sudah ditampilkan), lalu telusuri field penyebabnya.

## Item lain yang belum dikerjakan
1. **Foto profil tak muncul di user lain** — foto baru tampil di profil sendiri; di tempat user lain
   melihat (dosen lihat mahasiswa, daftar mahasiswa, dsb) masih inisial.
2. **Reset password tak mengirim email** — `.env` server masih `MAIL_MAILER=log`. Perlu SMTP
   (mis. Gmail App Password) diisi **oleh user sendiri**, lalu `config:clear`.
3. **Info kontak halaman login** masih kontak umum POLINES — user belum memutuskan:
   biarkan / ganti ke kontak prodi / hapus. **Tanyakan dulu.**
4. **Detail mahasiswa di portal pembimbing industri "terlalu sepi"** — perlu diperkaya.
5. **Badge/titik merah** saat mahasiswa mengunggah sesuatu (penanda visual untuk dosen/industri).
6. **Ikon mata (show/hide password)** belum ada di field password reset (halaman login sudah punya).
7. **Mobile belum pernah diuji.** Base URL sudah diarahkan ke produksi (commit `62e54f2`,
   `AppConfig.useProduction = true` → `https://simama.site/api`), tapi partner **harus rebuild APK**
   (`flutter pub get && flutter build apk --release`). Ada dependensi baru `mobile_scanner`.
8. Hapus entri logbook uji berjudul **"diag"** milik Muhammad Zaki (dibuat saat diagnosa).

## Catatan penting
- Saat mengubah banyak blade, **jangan pakai regex/script otomatis** untuk mengganti tag HTML —
  sudah pernah merusak layout (anchor tertutup di posisi salah). Edit manual, lalu verifikasi
  dengan `php artisan view:cache`.
- Guard baru: **logbook** butuh mahasiswa punya magang; **bimbingan** butuh dosen sudah diplot
  (kalau belum, tombol tambah nonaktif + banner penjelasan).
- Rubrik nilai sudah dirombak sesuai form resmi (dosen: Proposal 20% + Laporan 80%; industri:
  8 komponen; skala 1–10; nilai akhir = rata dosen + rata industri). Lihat `project_nilai_rework.md`.
- Semua pekerjaan sudah ter-commit & ter-push ke branch `web`.
