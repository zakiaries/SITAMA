# ⚡ QUICK START - Menjalankan Laravel dengan Database

## 🏃 Langkah Cepat (5 Menit)

### ✅ Checklist Setup

- [ ] **Pastikan MySQL Running**
  ```bash
  # Cek di Task Manager atau jalankan:
  # Windows: net start MySQL80
  ```

- [ ] **Update `.env` (jika ada perubahan)**
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=sitama
  DB_USERNAME=root
  DB_PASSWORD=
  ```

- [ ] **Create Database di MySQL**
  ```sql
  CREATE DATABASE sitama;
  ```

- [ ] **Jalankan Migrasi**
  ```bash
  php artisan migrate
  ```

- [ ] **Clear Cache**
  ```bash
  php artisan cache:clear
  php artisan config:clear
  ```

---

## 🚀 Jalankan Aplikasi

### Terminal 1 - Development Server
```bash
cd "c:\Users\ASUS\OneDrive\Documents\informatika\TA\SITAMA WEB"
php artisan serve
```

**Harusnya:**
```
   INFO  Server running on [http://127.0.0.1:8000].
```

### Buka di Browser
- http://127.0.0.1:8000/
- http://127.0.0.1:8000/about
- http://127.0.0.1:8000/contact

---

## 🔄 Development Workflow

**Setiap kali membuka project:**

```bash
# 1. Navigate ke project
cd "c:\Users\ASUS\OneDrive\Documents\informatika\TA\SITAMA WEB"

# 2. Clear cache
php artisan config:clear

# 3. Jalankan server
php artisan serve

# 4. Akses: http://127.0.0.1:8000
```

---

## 📊 Perintah Database Useful

| Situasi | Perintah |
|---------|----------|
| Jalankan migrasi pertama kali | `php artisan migrate` |
| Reset database total | `php artisan migrate:refresh` |
| Rollback migrasi terakhir | `php artisan migrate:rollback` |
| Isi data test | `php artisan db:seed` |
| Lihat struktur database | Buka MySQL Workbench / phpMyAdmin |

---

## ✨ Sekarang Siap!

Database sudah terhubung, server running, sekarang bisa develop features! 🎉
