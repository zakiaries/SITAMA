# 🚀 Panduan Set Up Deployment SIMAMA (Mulai dari Sini)

Panduan menyeluruh untuk pemula: dari **beli VPS** sampai **SIMAMA online** dan siap uji (UAT).
Ini peta besarnya; perintah teknis lengkap ada di **`DEPLOY_VPS.md`**, detail konfig & smoke
test di **`DEPLOY_CHECKLIST.md`**.

---

## Gambaran besar (biar tidak bingung)

- **Web + API (Laravel)** → di-deploy ke **VPS**, diakses di `https://domainmu`. API-nya juga
  dipakai aplikasi mobile.
- **Aplikasi mobile (Flutter/APK)** → **tidak** di-deploy ke server; di-*build* jadi APK oleh
  partner, lalu di-install di HP. Dia hanya menyambung ke API di VPS.

> Urutan wajib: **backend online dulu**, baru mobile diarahkan ke URL-nya.

---

## Peta perjalanan (6 tahap)

```
Tahap 0  Persiapan & keputusan
Tahap 1  Beli VPS + domain
Tahap 2  Sambung ke VPS (SSH)
Tahap 3  Deploy backend   ← inti (ikuti DEPLOY_VPS.md)
Tahap 4  Verifikasi (smoke test)
Tahap 5  Rilis aplikasi mobile (partner)
Tahap 6  Uji pengguna (UAT)
```

Estimasi waktu Tahap 1–4 pertama kali: **±1–2 jam** (santai, sambil ikuti langkah).

---

## Tahap 0 — Persiapan & keputusan

- [ ] **Cek dulu**: apakah Polines/jurusan menyediakan hosting/VM untuk TA? Kalau ada & PHP 8.2+
      dengan SSH → gratis, pakai itu (lompat ke Tahap 2).
- [ ] Siapkan **metode bayar** (kartu/e-wallet) untuk VPS + domain.
- [ ] Tentukan & **catat di tempat aman** (dipakai nanti):
  - Password **database** yang kuat.
  - Password **akun Kaprodi** awal yang kuat.
- [ ] Pilihan yang direkomendasikan: **IDCloudHost — Basic Standard 2 GB, Ubuntu 22.04/24.04, lokasi Jakarta.**

---

## Tahap 1 — Beli VPS + domain

1. **Daftar akun IDCloudHost** (idcloudhost.com) → verifikasi.
2. **Deploy VPS**: Cloud VPS → pilih **2 GB RAM**, **OS Ubuntu 22.04/24.04**, **lokasi Indonesia (Jakarta)**,
   autentikasi **password root** (atau SSH key). ⚠️ Jangan pilih Windows.
3. Setelah jadi, **catat**: **IP publik**, **user** (`root`), **password**.
4. **Beli domain** murah, mis. **.my.id** (~Rp10–15rb/thn).
5. **Arahkan domain ke VPS**: di pengaturan DNS domain, buat **A record**:
   `@  →  IP_VPS`  (dan `www → IP_VPS` bila mau). Contoh domain: `simama.my.id`.
6. Tunggu DNS menyebar (5–30 menit). Cek dari komputermu: `ping simama.my.id` → harus balas IP VPS.

---

## Tahap 2 — Sambung ke VPS lewat SSH

**Windows 10/11** (sudah ada SSH bawaan):
1. Buka **PowerShell** atau **Terminal**.
2. Ketik: `ssh root@IP_VPS` (ganti IP_VPS dengan IP-mu).
3. Pertama kali muncul pertanyaan fingerprint → ketik **`yes`** → Enter.
4. Masukkan **password root** (saat mengetik password tidak kelihatan — itu normal).
5. Kalau muncul prompt seperti `root@vps:~#` → **berhasil masuk**. 🎉

> Alternatif GUI: aplikasi **PuTTY** (isi Host = IP, Port 22, lalu login).

---

## Tahap 3 — Deploy backend (inti)

Semua perintahnya sudah disiapkan **siap copy-paste** di **`DEPLOY_VPS.md`**. Ikuti berurutan
**Langkah 1 → 11**:

1. Pasang stack (PHP 8.2, Nginx, MySQL/MariaDB, Composer, Git).
2. Buat database & user MySQL.
3. Ambil kode (`git clone -b web ...`) + `composer install`.
4. Isi `.env` produksi (ganti `APP_URL` ke domainmu, password DB, `SEED_KAPRODI_PASSWORD`).
5. `migrate` + `db:seed` + `storage:link` + cache.
6. Atur hak akses folder.
7. Naikkan batas upload PHP.
8. Konfigurasi Nginx (docroot `public/`).
9. Pasang **HTTPS gratis** (certbot).
10. Firewall.

> **Tips**: kerjakan satu blok perintah, lihat hasilnya, baru lanjut. Kalau ada yang **merah/error**,
> **berhenti** dan tanya aku — jangan lanjut menumpuk error.

---

## Tahap 4 — Verifikasi (SIMAMA sudah online!)

- Buka **`https://domainmu`** di browser → halaman SIMAMA muncul.
- **Login Kaprodi** (username/password dari `.env` yang kamu set).
- Jalankan smoke test di **`DEPLOY_CHECKLIST.md` bagian 6**: login tiap peran, akses lintas peran
  harus 403, chatbot, upload file, **scan QR dari HP asli**, input nilai, ekspor PDF berita acara.

---

## Tahap 5 — Rilis aplikasi mobile (tugas partner)

1. Ubah base URL API di `mobile/lib/config/app_config.dart` → `https://domainmu`.
2. `flutter pub get` (ada dependensi baru `mobile_scanner`) → **build APK rilis**.
3. Bagikan file **APK** ke penguji (Google Drive/WhatsApp); penguji install manual.
   > Penguji yang tak mau install APK tetap bisa pakai **versi web**.

---

## Tahap 6 — Uji pengguna (UAT)

1. Login Kaprodi → buat akun **dosen** + **perusahaan/pembimbing industri** + data awal.
2. **Soft-launch** ke grup kecil (5–10 orang) ±1 minggu → perbaiki temuan.
3. Baru **UAT penuh** (jumlah responden sesuai target Bab IV).

---

## Kalau macet / error

- Buka log: `tail -n 50 storage/logs/laravel.log` di VPS.
- **Paste perintah + output/error-nya ke chat** — aku bantu perbaiki langkah demi langkah.
- Masalah umum & solusinya ada di **`DEPLOY_VPS.md` → Troubleshooting**.

---

## Ringkasan file panduan
| File | Isi |
|---|---|
| **PANDUAN_DEPLOY.md** (ini) | Peta perjalanan pemula, urutan besar |
| **DEPLOY_VPS.md** | Perintah teknis VPS siap copy-paste (Tahap 3) |
| **DEPLOY_CHECKLIST.md** | Detail `.env`, data awal, smoke test, keterbatasan |
