# Panduan Deploy SIMAMA di VPS (Ubuntu + Nginx + PHP 8.2 + MySQL)

Panduan langkah demi langkah men-deploy web SIMAMA (Laravel) ke VPS. Ditulis untuk
**Ubuntu 22.04/24.04 LTS**. Semua perintah dijalankan lewat SSH sebagai user dengan sudo.

> Ringkas: pasang stack → ambil kode → `.env` → `migrate`+`seed`+`storage:link` → Nginx →
> SSL → selesai. Detail `.env` & smoke test lihat juga `DEPLOY_CHECKLIST.md`.

---

## 0. Prasyarat
- VPS Ubuntu 22.04/24.04, **min. 2 GB RAM** (1 GB muat tapi mepet untuk MySQL+PHP).
- **Domain** (atau subdomain) sudah diarahkan (A record) ke **IP publik VPS**. HTTPS wajib
  (kamera scan QR). Contoh di sini: `simama.contoh.ac.id`.
- Akses SSH ke VPS.

---

## 1. Update & pasang stack

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.2 (repo ondrej agar versi pasti tersedia)
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# PHP 8.2 + ekstensi yang dibutuhkan SIMAMA
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl

# Web server, database, git, unzip
sudo apt install -y nginx mariadb-server git unzip

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Cek: `php -v` → 8.2.x, `composer --version`, `nginx -v`.

---

## 2. Buat database & user MySQL

```bash
sudo mysql
```
Di prompt MySQL:
```sql
CREATE DATABASE simama CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'simama'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_DB_KUAT';
GRANT ALL PRIVILEGES ON simama.* TO 'simama'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Ambil kode aplikasi

```bash
sudo mkdir -p /var/www
cd /var/www
# branch 'web' adalah branch produksi
sudo git clone -b web https://github.com/zakiaries/SITAMA.git simama
sudo chown -R $USER:$USER /var/www/simama
cd /var/www/simama
```
> Kalau repo privat, pakai deploy key / token, atau upload manual via SCP.

Install dependensi (mode produksi):
```bash
composer install --no-dev --optimize-autoloader
```

---

## 4. Konfigurasi `.env` produksi

```bash
cp .env.example .env   # bila ada; kalau tidak, buat baru
nano .env
```
Isi minimal (lihat template lengkap di bawah). Lalu generate key:
```bash
php artisan key:generate
```

### Template `.env` produksi
```env
APP_NAME=SIMAMA
APP_ENV=production
APP_DEBUG=false
APP_KEY=                                  # diisi oleh key:generate
APP_URL=https://simama.contoh.ac.id       # WAJIB benar (isi QR & tautan)
APP_TIMEZONE=Asia/Jakarta                 # WIB

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simama
DB_USERNAME=simama
DB_PASSWORD=GANTI_PASSWORD_DB_KUAT

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# Akun Kaprodi awal (dibuat saat db:seed) — GANTI ke nilai kuat!
# Bila password mengandung # / spasi / karakter khusus, BUNGKUS tanda kutip.
SEED_KAPRODI_USERNAME=kaprodi
SEED_KAPRODI_EMAIL=kaprodi@contoh.ac.id
SEED_KAPRODI_PASSWORD="GANTI#Password!Kuat"

# Email aktivasi pembimbing industri (opsional; kosong = pakai fallback link manual)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@contoh.ac.id
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 5. Migrasi, seed, storage link, cache

```bash
php artisan migrate --force
php artisan db:seed --force        # 1 akun Kaprodi (dari .env) + rubrik nilai + KB chatbot
php artisan storage:link           # WAJIB — tanpa ini foto/file/logo PDF 404
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Data awal & verifikasi: lihat `DEPLOY_CHECKLIST.md` bagian 4 & 6.

---

## 6. Hak akses folder (agar web server bisa menulis)

```bash
sudo chown -R www-data:www-data /var/www/simama/storage /var/www/simama/bootstrap/cache
sudo find /var/www/simama/storage -type d -exec chmod 775 {} \;
sudo find /var/www/simama/storage -type f -exec chmod 664 {} \;
```

---

## 7. Naikkan batas upload PHP (laporan 10MB / foto 4MB)

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```
Ubah:
```
upload_max_filesize = 12M
post_max_size = 15M
```
Lalu:
```bash
sudo systemctl restart php8.2-fpm
```

---

## 8. Server block Nginx (docroot ke `public/`)

```bash
sudo nano /etc/nginx/sites-available/simama
```
Isi:
```nginx
server {
    listen 80;
    server_name simama.contoh.ac.id;
    root /var/www/simama/public;

    index index.php;
    charset utf-8;
    client_max_body_size 15M;   # samakan dgn post_max_size

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
Aktifkan + reload:
```bash
sudo ln -s /etc/nginx/sites-available/simama /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 9. HTTPS (Let's Encrypt gratis)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d simama.contoh.ac.id
```
Certbot mengubah server block ke 443 + auto-redirect http→https + perpanjangan otomatis.
Pastikan `APP_URL` di `.env` sudah `https://...`, lalu:
```bash
php artisan config:cache
```

---

## 10. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

---

## 11. Smoke test (buka di browser)
- `https://simama.contoh.ac.id` → halaman muncul, login Kaprodi (kredensial dari `.env`).
- Detail lengkap: `DEPLOY_CHECKLIST.md` bagian 6 (login tiap peran, 403 lintas peran,
  chatbot, upload file, **scan QR dari HP asli**, input nilai, ekspor PDF berita acara).

---

## 12. Update aplikasi di kemudian hari

```bash
cd /var/www/simama
php artisan down                 # mode maintenance
git pull origin web
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

---

## 13. Mobile (partner)
- Arahkan base URL API di `mobile/lib/config/app_config.dart` ke `https://simama.contoh.ac.id`
  (bukan `10.0.2.2`/localhost).
- `flutter pub get` (ada dependensi baru `mobile_scanner`) → build APK rilis.

---

## Troubleshooting cepat
- **500 / layar putih** → cek `storage/logs/laravel.log`. Sering: `APP_KEY` kosong, permission `storage/`, atau `config:cache` dengan `.env` salah (jalankan `php artisan config:clear` lalu perbaiki `.env`).
- **Foto/file 404** → `php artisan storage:link` belum jalan, atau permission `storage/`.
- **Upload gagal** → `upload_max_filesize`/`post_max_size` php.ini + `client_max_body_size` Nginx.
- **QR tak bisa discan** → `APP_URL` bukan https, atau belum SSL.
- **Mail tak terkirim** → SMTP kosong; pakai fallback link manual aktivasi.
