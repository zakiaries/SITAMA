# Handoff — Sesi Berikutnya (SIMAMA)

Tempel isi di bawah (mulai dari "Lanjutkan pengerjaan") sebagai pesan pertama di chat baru.

---

Lanjutkan pengerjaan **SIMAMA** (sistem informasi magang, Laravel 9 + Flutter, TA D3 Polines).
Kode di `D:\SITAMA\SITAMA-web`, branch produksi **`web`** (repo `zakiaries/SITAMA`).
Baca dulu memory-ku (MEMORY.md + `project_*.md`, terutama `project_utang_pasca_sidang.md`).

## Status: LIVE, berisi DATA SUNGGUHAN, uji coba dimulai 4 Agustus 2026

- **https://simama.site** — Docker Compose di `/var/www/simama` (Caddy + PHP-FPM + MariaDB).
- VPS **Rumahweb**, IP `202.10.38.76`. Domain + DNS di **Hostinger** (ns `dns-parking.com`).
  Claude tak punya akses SSH — semua perintah server dijalankan user.
- **347 feature test hijau** (`php artisan test`; butuh MySQL XAMPP di `D:\xampp` nyala).
- SMTP aktif (Gmail App Password, pengirim `SIMAMA <lanangbayup@gmail.com>`).

### Isi produksi per 4 Agustus 2026

| Peran | Jumlah | Sandi |
|---|---|---|
| Mahasiswa | 89 (76 hasil impor + 13 sisa uji) | `magang2026` |
| Dosen | 26 | `bimbing2026` (diganti 5 Agt, dulu `dospem2026`) |
| Pembimbing industri | 22 | `magang2026` |
| Kaprodi | 1 (`kaprodi`) | `SimamaKaprodi2026` |

76 mahasiswa D4 Teknologi Rekayasa Komputer (TI-3A/3B/3C), semuanya sudah **diplot dospem**,
**52 di antaranya magangnya sudah tercatat**, 24 mengajukan sendiri lewat aplikasi.
Email mahasiswa memakai pola kampus `namadepan.nimtanpatitik@mhs.polines.ac.id` (terverifikasi
sampai), sehingga **Lupa Password berfungsi untuk mahasiswa**. Email dosen kini **24 dari 26
sudah asli** (`@polines.ac.id`, dikumpulkan dari direktori SDM) → Lupa Password jalan untuk
mereka. Sisa 2 placeholder: Eri Eli Lavindi & Suko Tyas Pernanda → reset lewat Kaprodi.

### Cadangan — sudah berjalan dan TERBUKTI

- `docker/backup.sh`, cron harian **02:00**, simpan 14 hari di `/var/backups/simama`.
- Sudah diuji pulih ke basis data sementara: **29 tabel**.
- Salinan lokal: `C:\Users\ASUS\Downloads\cadangan-simama\`.
- Salinan produksi terimpor di MySQL lokal sebagai basis data **`simama_produksi`**
  (terpisah dari `sitama` yang dipakai ngoding — jangan tertukar).

### Alur deploy
```bash
cd /var/www/simama && git pull origin web \
  && docker compose exec -T app php artisan migrate --force \
  && docker compose exec -T app php artisan config:cache \
  && docker compose exec -T app php artisan route:cache \
  && docker compose exec -T app php artisan view:cache
```

---

## YANG TERSISA

### 1. ~~Belum dideploy — 4 commit~~ SELESAI 5 Agt
`619334c`, `d38c682`, `ec95bbf`, `ef07ccd` sudah live, dan `simama:impor-dosen --prodi=semua`
sudah dijalankan. Terbukti dari halaman Data Dosen: penyaring prodi muncul dengan
**TI D3 = 14, TRK D4 = 8, Belum diisi = 4** (total 26) — cocok dengan roster kode
(11 IK + 8 TI) ditambah Eri, Suko, Muttabik yang prodinya ikut terisi.

Wiktasari **tidak berduplikat**: akunnya di produksi sudah memakai NIP yang benar
(`198703272019032012`), sehingga rerun menemukannya dan melewatinya, bukan membuat akun baru.

### 2. Menunggu keputusan/info user
- **Prodi Tahan Prahara** — partner bilang "kecuali Tahan, ketiganya D3 (IK)", jadi kemungkinan
  D4 TI, tapi belum dikonfirmasi eksplisit. Jangan diisi sebelum dipastikan. Ia salah satu dari
  4 kartu "Belum diisi"; tiga sisanya dosen karangan yang menunggu dibersihkan.
- ~~**Prodi Eri Eli Lavindi, Suko Tyas Pernanda, Muttabik Fathul Lathief**~~ SUDAH terisi
  Teknik Informatika (D3).
- **NIP/NIDN + email Eri & Suko** — keduanya TIDAK ADA di direktori SDM Polines
  (`web.polines.ac.id/id/sdm`); kemungkinan tenaga pengajar non-PNS tanpa NIP. User akan tanya
  di kampus.
- **Pembersihan sampah produksi** — 9 akun mahasiswa uji (`tes1`, `tes3`, `putri maria`,
  `wertyuiop[;lkmnbvcdfghjkl;'`, dll.) + 3 dosen karangan (`dosen1`/ayu lestari,
  `dosen2`/fauzi ali, `widi santoso`). User menunda. **Jangan hapus tanpa perintah eksplisit.**
  JANGAN hapus: akun user (`3.34.23.2.12`), partner (`3.34.23.2.15`),
  `3.34.23.2.55` (simulasi lengkap sampai seminar), `3.34.23.2.99` (dummy 3 tahap).

### 3. Flutter — APK jalan di HP, sisa rapi-rapi UI
- Flutter **3.44.8** (sudah di-upgrade dari 3.35.4). `flutter analyze` → **No issues found**.
- `flutter build apk --release` **sudah berhasil** setelah `kotlin.incremental=false` (`778279e`)
  + `flutter clean` — dulu gagal karena kompilasi inkremental Kotlin tak bisa menghitung jalur
  relatif antar-drive (proyek di `D:`, pub cache di `C:`). Aplikasi sudah terpasang & berfungsi
  di HP user; **sisa keluhan hanya UI yang kurang rapi**, belum dirinci bagian mana.
- Temuan penting: `penilaian_screen.dart` **tak pernah bisa dikompilasi** sejak dulu (impor
  `app_theme.dart` hilang) — baru ketahuan setelah analyze pertama kali dijalankan.

### 4. Laporan — partner yang menulis
Prompt untuk Claude-nya sudah diberikan user. Berkas rujukan chatbot:
`C:\Users\ASUS\Downloads\Penjelasan TF-IDF dan Cosine Similarity - SIMAMA.txt` (12 bagian,
angka nyata, siap dibawa bimbingan).

### 5. Utang pasca-sidang
Lihat `project_utang_pasca_sidang.md` — 5 hal yang **sengaja** dibiarkan (sandi seragam, email
dosen placeholder, magang lama tanpa layar edit periode, pengirim email Gmail pribadi, kuota
per perusahaan). Semuanya keputusan sadar, bukan cacat terlewat.

---

## Selesai di sesi 2026-08-03/04 — 19 commit (`5ca0c75` … `ef07ccd`), semua pushed

**Enam bug dari partner + empat cacat kecil**
- `5ca0c75` Nilai baru bisa diisi setelah mahasiswa merampungkan magangnya (logbook lengkap +
  laporan di-ACC). Sertifikat SENGAJA bukan syarat — perusahaan sering terlambat menerbitkan.
- `aca0c68` Notifikasi pindah ke panel melayang di lonceng (keempat peran).
- `5ceaf63` Dropdown pembimbing: `autocomplete="off"` (peramban yang mengisi ulang, bukan server).
- `eca7f59` Kuota lowongan + penanda terisi/penuh. `2ae4b1a` Bonus: 500 saat Tipe dikosongkan.
- `8c619dc` `end_date` akhirnya terisi — diminta sejak pengajuan, terbawa ke magang.
- `b6045a7` Lowongan nonaktif tak lagi hidup lagi saat disunting.
- `bfd71f4` Peran mati `industri` dibuang dari enum.
- `293a03e` Kuota diperjelas: dihitung per perusahaan, bukan per lowongan.

**Chatbot**
- `364d66c` Pemicu `'magang di '` dibuang. Sebelumnya entri KB sendiri ("Bagaimana cara
  mengajukan magang di SIMAMA?") dijawab daftar lowongan padahal TF-IDF sudah menghitung 0,63.
  TF-IDF/cosine/ambang **tidak disentuh** — Bab 3 tetap berlaku. Tabel 4.6 & 4.7 diuji ulang:
  6/6, angka identik.

**Operasional**
- `17f3b24`/`2060256` Skrip cadangan harian + perbaikan pemeriksanya (jebakan
  `set -o pipefail` + `grep -q`: grep berhenti di kecocokan pertama → SIGPIPE → pipa dilaporkan
  gagal JUSTRU karena polanya ketemu).
- `249099e` `simama:impor-peserta` — impor 76 peserta dari CSV plotting prodi.
- `6bb7c5e` Opsi `--email-kampus`. `d4493cd` Email placeholder, bukan domain kampus.
- `619334c` Kredensial dosen & pembimbing industri ikut dicetak.
- `a02e9c7` Tahap dummy `siap-dinilai`.
- `d38c682` NIP Wiktasari dibetulkan (TMT 201903, bukan 201902).
- `ec95bbf` Penyaring prodi di Data Dosen + urut nama + placeholder sebut NIP.

**Mobile**
- `67964df` Disamakan dengan web (end_date wajib, `can_score`/`score_locked`, kuota lowongan).
- `8cb0a8b` Impor tema yang hilang. `778279e` `kotlin.incremental=false`.

---

## Catatan penting (mahal didapat, jangan diulang)

### Kesalahan yang SUDAH pernah dibuat sesi ini
- **JANGAN menyimpulkan prodi dosen dari prodi mahasiswanya.** Pembimbingan lintas prodi hal
  biasa — user sendiri D3 (IK) dibimbing dosen D4. Sempat ditulis begitu, lalu dicabut
  (`ef07ccd`). Kalau tak diketahui, biarkan kosong dan tampilkan "Belum diisi".
- **JANGAN mengarang alamat email di domain milik institusi.** Sempat memakai
  `@student.polines.ac.id`; kalau alamat karangan itu ternyata ada dan dipakai orang lain,
  tautan reset kata sandi terkirim ke pihak tak berhak. `@simama.local` aman karena pengiriman
  mustahil. Pola kampus (`--email-kampus`) aman **karena NIM ikut di dalam alamat** sehingga
  unik per orang.
- **Perbaikan chatbot "dahulukan FAQ bila skor unggul" TERBUKTI LEBIH BURUK** — merusak tiga
  permintaan rekomendasi. Diuji sebelum dipakai, lalu dibuang. Yang benar: buang pemicunya.

### Lingkungan (Windows)
- `mysql.exe` ada di **`D:\xampp\mysql\bin\`**, tidak di PATH. Laragon di `C:\` kosong.
- Perintah berawalan `/d/...` untuk **Git Bash**; di PowerShell pakai `D:\...` dan `&` bila dikutip.
  PowerShell 5.1 **tidak mengenal `&&`**.
- `scp` Windows: satu berkas per perintah. `scp host:"/a /b" tujuan` dibaca sebagai SATU nama.
- Dump MariaDB diawali `/*M!999999\- enable the sandbox mode */` yang **ditolak klien MySQL**.
  Buang baris itu sebelum impor.
- Excel merusak NIP 18 digit & nomor HP jadi notasi ilmiah. Buka `.tsv` lewat
  Data → From Text/CSV, set kolomnya sebagai **Text**.

### Domain & alur yang mudah keliru
- **Dua kolom dospem**: `students.lecturer_id` (plot Kaprodi, sumber kebenaran) dan
  `internships.lecturer_id` (dipakai portal dosen). `assignLecturer` menyinkronkan keduanya.
- **Bimbingan butuh dospem, BUKAN magang** — ini disengaja (dospem membimbing proposal sebelum
  magang ada). Pernah dilaporkan sebagai bug, lalu dikonfirmasi user sebagai rancangan.
- **NIP disimpan sebagai `users.username`** — tak ada kolom NIP tersendiri. Pencarian di Data
  Dosen mencakup name ATAU username, jadi cari-per-NIP sudah lama bekerja.
- **Ganti sandi massal: WAJIB `->get()->each()`, JANGAN `->update()` massal.** `User::booted()`
  mencabut token Sanctum tiap sandi berubah, dan itu model event — update query-builder
  melewatinya diam-diam, menyisakan orang yang tetap bisa jalan di aplikasi HP dengan sandi
  lama. `$casts` juga tak punya `password => hashed`, jadi `bcrypt()` manual memang perlu.
  `laravel/tinker` ada di `require` (bukan `require-dev`), jadi selamat dari `--no-dev`:
  ```bash
  docker compose exec -T app php artisan tinker --execute='App\Models\User::where("role","lecturer")->get()->each(fn($u)=>$u->update(["password"=>bcrypt("SANDI")]));'
  ```
  **`simama:impor-dosen --setel-ulang --password=` hanya menyentuh 19 dosen di roster kode,
  bukan 26** — 4 dosen sungguhan (Eri, Suko, Muttabik, Tahan) lahir dari `impor-peserta` dan
  tak ada di roster. Jangan pakai perintah itu untuk operasi sandi menyeluruh.
- Gerbang yang ada: logbook butuh magang aktif; bimbingan butuh dospem; ajukan magang butuh
  dospem; nilai butuh logbook lengkap + laporan di-ACC. Kalau ada laporan "tak bisa mengisi",
  cek dulu syaratnya — biasanya bukan bug.
- `LOG_LEVEL` server sudah `warning`. **Jangan turunkan.** Cek `storage/logs/laravel.log` dulu
  sebelum menebak.
- Saat mengubah banyak blade, **jangan pakai regex otomatis** — pernah merusak layout.

### Cara kerja yang diminta user
- **Verifikasi manual milik user & partner.** Claude cukup: perbaiki → `php artisan test` → commit
  → push. Jangan menyuruh user membuka browser untuk mengecek.
- **Satu bug satu commit**, supaya user bisa memeriksa bertahap.
- Badan commit menjelaskan **sebab**, bukan daftar perubahan.
