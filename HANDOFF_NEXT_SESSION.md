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
- **472 feature test hijau** (`php artisan test`; butuh MySQL XAMPP di `D:\xampp` nyala).
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
Kode duduk di *bind mount* host, jadi `git pull` sudah memperbarui berkasnya; `--build` hanya
perlu bila `Dockerfile` berubah.
```bash
cd /var/www/simama && git pull origin web \
  && docker compose exec app php artisan migrate --force \
  && docker compose restart app
```

**Bila commit-nya menyentuh berkas `.blade.php`, tambahkan ini** — kalau tidak, halaman itu
(dan hanya halaman itu) akan 500. Lihat "Izin storage produksi" di bawah.
```bash
docker compose exec app sh -c "chown -R www-data:www-data storage bootstrap/cache"
```

---

## YANG TERSISA

### 0. PRIORITAS — sidang dulu, revisi belakangan
Keputusan user 7 Agt: **"penting sidang, dimasak di sidang, lalu revisi."** Jangan memoles
laporan lagi tanpa diminta. Yang benar-benar berisiko kalau dilewat:

1. **Enam diagram `.drawio` belum ditempel ke laporan.** Ini yang paling rawan — teks v62 sudah
   menyebut kelas `Period`, entitas `periods`, dan tabel `periods`, jadi kalau gambarnya masih
   yang lama, narasi dan gambar saling bertentangan di depan penguji. Ekspor PNG dari draw.io
   lalu ganti Gambar 3.3, 3.9, 3.13, 3.14, 3.15, 3.16.
2. **Ctrl+A lalu F9 di Word** — Daftar Isi/Gambar/Tabel masih memuat nomor halaman lama.
3. Semua commit sudah live per 7 Agt 02:30. Tidak ada yang menunggu deploy.

**Yang mungkin ditanya penguji, jawabannya sudah ada di data:** tidak ada responden dosen
(rekomendasi dospem); 21 responden bukan 22 (satu orang mengirim dua kali, NIM & skor sama);
keterbatasan chatbot yang tersisa = pencocokan berbasis kata, bergantung kelengkapan kata kunci
manual; angka Bab IV bisa dijalankan ulang di depan penguji dengan `php artisan test`.
**Titik lemah yang sebaiknya diakui duluan:** pembimbing industri & Kaprodi masing-masing hanya
1 responden — laporan sudah menyebutnya penilaian kualitatif yang tidak digeneralisasi.

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

### 4. Laporan — sekarang dikerjakan di sesi ini, bukan partner
Versi cetak: **`Laporan TA SIMAMA (revisi dosen) v62.docx`** di `Downloads`. Rantai v56→v62
lengkap di sana; jangan mundur ke versi lama. Skrip penyuntingnya ada di scratchpad sesi
(`terapkan58.py`, `terapkan59.py`, `terapkan60.py`, `perbaiki_miring.py`) — pola yang dipakai:
sunting `word/document.xml` langsung, tiap sasaran diperiksa lewat `assert` sebelum diganti.

Berkas rujukan chatbot: `C:\Users\ASUS\Downloads\Penjelasan TF-IDF dan Cosine Similarity -
SIMAMA.txt`.

### 5. Utang teknis yang ditunda sampai sesudah sidang
- **Izin `storage/` di produksi** — tambalan permanennya (skrip entrypoint yang menjalankan
  `chown` tiap kontainer start) ditunda karena menyentuh `Dockerfile` menuntut deploy
  `--build`. Sementara pakai perintah manual di bagian Alur deploy.
- **Basis data lokal `sitama` tertinggal 12 migrasi**, tersandung penjaga
  `drop_industri_role_from_users_table`: masih ada akun berperan `industri` dan migrasi
  **sengaja berhenti** daripada menebak peran penggantinya. Keputusan user, bukan bug.
  Akibatnya halaman yang menyentuh `periods` akan 500 di lokal.
- **`seminar_registrations`** masih ada di basis data, sisa rancangan seminar lama, tak
  terdokumentasi di laporan. Pilihannya: dokumentasikan sebagai tabel warisan, atau hapus.
  Belum diputuskan.
- **UI mobile & web** masih ada yang mau dirapikan user, belum dirinci.

### 6. Utang pasca-sidang
Lihat `project_utang_pasca_sidang.md` — 5 hal yang **sengaja** dibiarkan (sandi seragam, email
dosen placeholder, magang lama tanpa layar edit periode, pengirim email Gmail pribadi, kuota
per perusahaan). Semuanya keputusan sadar, bukan cacat terlewat.

---

## Selesai di sesi 2026-08-05/07 — 30 commit (`9daea26` … `7bf74e6`), semua pushed & live

**Periode magang — mekanik inti yang selama ini hilang** (`db55619` dst.)
Sebelumnya `students.academic_year` teks bebas yang DIKETIK mahasiswa, menghasilkan nilai
seperti "2023/2026" sehingga penyaring Kaprodi tak berguna. Sekarang ada tabel `periods`
(satu semester = satu periode; Gasal Agustus–Januari, Genap Februari–Juli, lama 5 bulan) yang
**dibangkitkan sistem mengikuti kalender** — Kaprodi hanya memilih prodi peserta dan periode
yang berjalan, tidak pernah mengetik tahun. Prodi berselang-seling: bila IK magang, TI tidak.
`period_id` menyusul ke `students` dan `seminars`. Penyaring periode dipasang di portal
Kaprodi, dosen, dan pembimbing industri; pendaftar tak lagi mengetik prodi maupun tahun.

**Seminar**
- `a86a6f6` Daftar hadir QR hanya terbuka **hari-H pada jamnya** (toleransi 30 menit sebelum,
  60 sesudah). Sebelumnya bisa dipindai sejak jadwal ditetapkan — syarat jumlah audiens jadi
  tak bermakna.
- `6f93644`/`3d6ec36` Dosen bisa menjadwalkan ulang sesi yang belum berlangsung, dan
  **jumlah audiens minimal kini per sesi** (kolom `min_guests`, bawaan 15), bukan tetapan kode.
  Web dan API disamakan — sempat lupa API-nya, ketahuan dan dibetulkan di `3d6ec36`.
- `0a84b8b` Berita acara mencetak periode magangnya, bukan hasil hitungan dari tanggal seminar.
  Yang magang Genap 2025/2026 lalu seminar Agustus 2026 dulu tercetak "Gasal 2026/2027".

**Nilai & berkas**
- `9b781c4`/`1e9865f`/`1ffe926` Lembar nilai PDF, dipecah dua (dosen & industri), disalin
  persis dari form resmi Polines termasuk kisi centang 1–10.
- `739ad77` Kolom Keterangan penilaian industri akhirnya tersimpan (`student_scores.note`).
- `034688a` Sertifikat/laporan/bimbingan bisa dibuka dari layar yang membutuhkannya —
  izinnya sudah ada dan teruji, yang hilang hanya tautannya. Kaprodi dulu menyetujui
  "selesai magang" tanpa bisa melihat berkas syaratnya.

**Chatbot** — dua perbaikan, keduanya sudah live
- `e1c47d7` Nama kota saja tak lagi memicu rekomendasi. Teks dokumen lowongan memuat kolom
  lokasi, sehingga "Cuaca Semarang hari ini" mencetak cosine **0,4313** di data produksi lalu
  dijawab daftar tempat magang. Penjagaannya sempit: hanya jalur tanpa maksud eksplisit, dan
  hanya bila irisan katanya **seluruhnya** token lokasi.
- `e516495` Kata `absen` didaftarkan pada entri absensi QR (stemmer tak memotong `-si`), dan
  pengulangan kata `seminar` pada entri jumlah audiens dibuang. **Perbaikan di basis
  pengetahuan, bukan algoritma.** Ada migrasi `2026_08_06_000004` karena seeder-nya
  `firstOrCreate` dan tak menyentuh entri yang sudah ada.
- Tabel 4.6 kini **6/6**, Tabel 4.7 **6/6**, Tabel 4.8 **18/18**. Tabel 4.8 dulu tak punya tes
  sama sekali (angkanya dirakit tangan) — sekarang dicetak `PengujianChatbotTest`.

**UI** — `a92b6de`/`6b69d5e`/`1dc30dc` pesan galat muncul di 15 halaman berformulir yang dulu
menolak diam-diam; `81dd79b`/`71f6739`/`4e315a7` garis sidebar sejajar, ikon tak berulang,
judul & menu ganda dihapus; `7bf74e6` dropdown prodi di halaman daftar disamakan dengan kolom
lain (dulu `<select>` polos bawaan peramban, tinggi 18px lawan 46px).

**Laporan TA** — dikerjakan di sesi ini, bukan oleh partner
Rantai versi `v56 → v62` di `C:\Users\ASUS\Downloads\`. **v62 yang dicetak.** Isinya: klaim
Bootstrap dicabut (nyatanya CSS sendiri), Batasan Masalah diselaraskan dengan
`syncDirectoryListing()`, tabel `periods` didokumentasikan, **seluruh responden dosen dihapus**
(rekomendasi dospem — tidak memungkinkan mengisi kuesioner), data 21 responden mahasiswa
dimasukkan (92,45%; 22 baris dikurangi 1 kiriman ganda), dan angka chatbot disamakan dengan
kode. Enam diagram dibangun ulang sebagai `.drawio` di `Downloads` (Gambar 3.3, 3.9, 3.13,
3.14, 3.15, 3.16) — **belum ditempel ke laporan**, padahal teksnya sudah menyebut kelas
`Period` dan tabel `periods`.

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

### Izin storage produksi — gejalanya menipu (7 Agt)
`storage/framework/views` tidak bisa ditulisi `www-data`: kode di *bind mount*, `git pull`
dijalankan root. **Hanya halaman yang berkas `.blade.php`-nya baru berubah yang jatuh 500**;
sisanya normal karena disajikan dari kompilasi lama. Halaman 500-nya generik
(`APP_DEBUG=false`), galat aslinya cuma di log. Pernah 5 Agt (halaman untuk pengguna login) dan
7 Agt (`/register`). **Bukan cacat kode — me-revert commit tidak menolong.**

Membaca galat produksi tanpa perlu menggulung terminal:
```bash
docker compose exec app sh -c "cut -c1-260 storage/logs/laravel.log | grep 'production.ERROR' | tail -5"
```

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
- **Kalau perlu melihat sendiri hasil perubahan UI, buka `https://simama.site` — situsnya hidup.**
  JANGAN menyalakan server lokal lebih dulu: basis data `sitama` tertinggal migrasi (lihat
  Utang teknis) sehingga halaman yang menyentuh `periods` akan 500. Kalau benar-benar butuh
  lokal, `sitama_testing` punya skema lengkap:
  `DB_DATABASE=sitama_testing php artisan serve --port=8002`.
  Kesalahan ini nyata terjadi 7 Agt — habis waktu menyalakan server lokal, memigrasi, dan
  menyunting `.env`, padahal cukup membuka halaman produksinya.
- **Satu bug satu commit**, supaya user bisa memeriksa bertahap.
- Badan commit menjelaskan **sebab**, bukan daftar perubahan.
