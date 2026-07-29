# 🐳 Deploy SIMAMA dengan Docker (VPS Ubuntu)

Cara deploy SIMAMA memakai Docker Compose. Stack sudah disiapkan:
**Caddy** (web + HTTPS otomatis) · **PHP-FPM** (app) · **MariaDB** (database).
File terkait: `Dockerfile`, `docker-compose.yml`, `docker/Caddyfile`.

> **Data aman**: file upload (foto/laporan/sertifikat) tersimpan di disk host
> (folder proyek), database di volume `db_data`. Keduanya **tidak hilang** saat
> container di-restart/rebuild.

---

## 0. Prasyarat
- VPS **Ubuntu 22.04/24.04**.
- Domain (mis. `simama.web.id`) sudah dibuat **A record → IP VPS**, dan sudah menyebar
  (cek: `ping simama.web.id` membalas IP VPS). ⚠️ **Wajib sebelum menyalakan**, agar Caddy
  bisa mengambil sertifikat HTTPS.

---

## 1. Pasang Docker (sekali)

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER      # agar tak perlu sudo tiap perintah docker
# keluar-masuk SSH sekali (logout lalu ssh lagi) agar grup berlaku
docker --version && docker compose version
```

---

## 2. Ambil kode

```bash
sudo mkdir -p /var/www
sudo git clone -b web https://github.com/zakiaries/SITAMA.git /var/www/simama
sudo chown -R $USER:$USER /var/www/simama
cd /var/www/simama
```

---

## 3. Siapkan `.env`

```bash
cp .env.example .env
nano .env
```
Isi/ubah **poin penting untuk Docker** (sisanya lihat `DEPLOY_CHECKLIST.md`):
```env
APP_NAME=SIMAMA
APP_ENV=production
APP_DEBUG=false
APP_KEY=                                   # diisi otomatis di langkah 5
APP_URL=https://simama.web.id
APP_DOMAIN=simama.web.id                    # ← dipakai Caddy untuk HTTPS
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=db                                  # ← WAJIB "db" (nama service), bukan 127.0.0.1
DB_PORT=3306
DB_DATABASE=simama
DB_USERNAME=simama
DB_PASSWORD=GANTI_PASSWORD_DB_KUAT
DB_ROOT_PASSWORD=GANTI_PASSWORD_ROOT_KUAT   # ← khusus Docker (untuk MariaDB)

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# Akun Kaprodi awal (dibuat saat seed) — GANTI kuat. Bungkus tanda kutip bila ada # / spasi.
SEED_KAPRODI_USERNAME=kaprodi
SEED_KAPRODI_EMAIL=kaprodi@contoh.ac.id
SEED_KAPRODI_PASSWORD="GANTI#Password!Kuat"
```

---

## 4. Nyalakan container

```bash
docker compose up -d --build
```
Cek status (tunggu `db` sehat): `docker compose ps`

---

## 5. Inisialisasi aplikasi (sekali)

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force      # Kaprodi + rubrik nilai + KB chatbot
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
# Beri hak tulis ke folder yang dipakai Laravel
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

## 6. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80,443/tcp
sudo ufw enable
```

---

## 7. HTTPS otomatis

Caddy akan **otomatis** mengambil & memperpanjang sertifikat Let's Encrypt selama:
domain sudah mengarah ke IP VPS + port 80/443 terbuka. Cukup tunggu ~1 menit lalu buka
**`https://simama.web.id`**.

> Kalau belum https: lihat `docker compose logs web` — biasanya karena DNS belum menyebar
> atau `APP_DOMAIN` salah.

---

## 8. Verifikasi (smoke test)
Buka `https://simama.web.id` → login Kaprodi. Detail: `DEPLOY_CHECKLIST.md` bagian 6.

---

## Perintah harian

| Tujuan | Perintah |
|---|---|
| Lihat log | `docker compose logs -f` (atau `logs -f app` / `web`) |
| Status | `docker compose ps` |
| Restart | `docker compose restart` |
| Matikan (data tetap aman) | `docker compose down` |
| Nyalakan lagi | `docker compose up -d` |
| Masuk shell app | `docker compose exec app bash` |

### Update aplikasi ke versi terbaru
```bash
cd /var/www/simama
git pull origin web
docker compose up -d --build     # rebuild bila Dockerfile berubah
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## Troubleshooting
- **Belum HTTPS / Caddy error** → `docker compose logs web`. Pastikan A record sudah benar
  (`ping domain`), port 80/443 terbuka, dan `APP_DOMAIN` di `.env` = domainmu.
- **500 / layar putih** → `docker compose logs app`. Cek `.env`: `APP_KEY` terisi,
  `DB_HOST=db`, dan permission `storage` (ulangi perintah `chown` di langkah 5).
- **DB connection refused** → tunggu `db` sehat (`docker compose ps`), pastikan `DB_HOST=db`.
- **Upload gagal** → sudah dibatasi 15MB di Caddyfile; untuk file lebih besar naikkan `max_size`.
- **Reset total (HATI-HATI, hapus DB)** → `docker compose down -v` (menghapus volume database!).
