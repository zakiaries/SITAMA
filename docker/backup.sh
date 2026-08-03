#!/usr/bin/env bash
#
# Cadangan harian SIMAMA — basis data + berkas unggahan.
#
# Yang dicadangkan:
#   1. Seluruh basis data (mariadb-dump dari container db).
#   2. storage/app — logbook, laporan, sertifikat, bukti magang, foto profil.
#      Berkas ini TIDAK ada di git dan tak bisa dibuat ulang.
#
# Cadangan lama hanya dihapus SETELAH cadangan baru lolos pemeriksaan. Kalau
# dump gagal atau kosong (mis. kata sandi salah), skrip berhenti dan yang lama
# tetap utuh — kegagalan diam-diam yang menghapus riwayat justru bencana yang
# ingin dihindari cadangan.
#
# PENTING: cadangan di VPS yang sama hanya melindungi dari migrasi keliru dan
# penghapusan tak sengaja. Ia TIDAK melindungi dari VPS-nya sendiri hilang.
# Salin berkasnya keluar secara berkala — lihat DEPLOY_DOCKER.md.
#
# Pakai:
#   bash docker/backup.sh              # cadangkan sekarang
#   SIMAMA_KEEP_DAYS=30 bash docker/backup.sh
#
set -euo pipefail

# Akar proyek = folder induk skrip ini (skrip duduk di <akar>/docker/).
AKAR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$AKAR"

TUJUAN="${SIMAMA_BACKUP_DIR:-/var/backups/simama}"
SIMPAN_HARI="${SIMAMA_KEEP_DAYS:-14}"
STEMPEL="$(date +%Y%m%d-%H%M%S)"

pesan() { printf '[%s] %s\n' "$(date +%H:%M:%S)" "$*"; }
gagal() { printf '[%s] GAGAL: %s\n' "$(date +%H:%M:%S)" "$*" >&2; exit 1; }

[ -f .env ] || gagal "Berkas .env tidak ada di $AKAR — jalankan skrip ini di server."

# Baca kredensial dari .env tanpa meng-eval seluruh berkasnya (nilai bisa
# mengandung karakter yang ditafsirkan shell).
baca_env() {
  local kunci="$1"
  sed -n "s/^${kunci}=//p" .env | head -1 | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
}

DB_NAMA="$(baca_env DB_DATABASE)"
DB_USER="$(baca_env DB_USERNAME)"
DB_SANDI="$(baca_env DB_PASSWORD)"

[ -n "$DB_NAMA" ] || gagal "DB_DATABASE kosong di .env."
[ -n "$DB_USER" ] || gagal "DB_USERNAME kosong di .env."

mkdir -p "$TUJUAN"

SQL_GZ="$TUJUAN/db-$STEMPEL.sql.gz"
BERKAS_TAR="$TUJUAN/storage-$STEMPEL.tar.gz"

# ── 1. Basis data ───────────────────────────────────────────────────────────
pesan "Membuat dump basis data '$DB_NAMA'…"

# Kata sandi lewat MYSQL_PWD, bukan argumen: argumen terlihat di daftar proses.
# --single-transaction: konsisten tanpa mengunci tabel (InnoDB).
docker compose exec -T -e MYSQL_PWD="$DB_SANDI" db \
  mariadb-dump --single-transaction --quick --routines \
               --user="$DB_USER" "$DB_NAMA" \
  | gzip -c > "$SQL_GZ" \
  || gagal "mariadb-dump gagal. Periksa kredensial di .env dan 'docker compose ps'."

# Dump kosong adalah kegagalan paling berbahaya: skrip "berhasil", cadangan
# lama terhapus, isinya nihil. Jadi hasilnya diperiksa dulu.
UKURAN=$(stat -c%s "$SQL_GZ" 2>/dev/null || echo 0)
[ "$UKURAN" -gt 1024 ] || gagal "Dump hanya $UKURAN byte — hampir pasti kosong. Cadangan lama TIDAK dihapus."

gzip -t "$SQL_GZ" || gagal "Berkas gzip rusak: $SQL_GZ"
zcat "$SQL_GZ" | grep -q 'CREATE TABLE' \
  || gagal "Dump tak memuat satu pun CREATE TABLE. Cadangan lama TIDAK dihapus."

JML_TABEL=$(zcat "$SQL_GZ" | grep -c 'CREATE TABLE' || true)
pesan "Basis data OK — $JML_TABEL tabel, $(du -h "$SQL_GZ" | cut -f1)."

# ── 2. Berkas unggahan ──────────────────────────────────────────────────────
if [ -d storage/app ]; then
  pesan "Mengarsipkan storage/app…"
  tar -czf "$BERKAS_TAR" storage/app \
    || gagal "Gagal mengarsipkan storage/app."
  tar -tzf "$BERKAS_TAR" >/dev/null || gagal "Arsip storage rusak: $BERKAS_TAR"
  pesan "Berkas OK — $(du -h "$BERKAS_TAR" | cut -f1)."
else
  pesan "PERINGATAN: storage/app tidak ditemukan, dilewati."
fi

# ── 3. Buang yang kedaluwarsa (hanya setelah semua di atas lolos) ───────────
TERHAPUS=$(find "$TUJUAN" -maxdepth 1 -type f \( -name 'db-*.sql.gz' -o -name 'storage-*.tar.gz' \) \
  -mtime "+$SIMPAN_HARI" -print -delete | wc -l)

pesan "Selesai. $TERHAPUS berkas lama dibuang (simpan $SIMPAN_HARI hari)."
pesan "Tersimpan di $TUJUAN:"
ls -lh "$TUJUAN" | tail -6

cat <<'CARA'

--- Cara memulihkan (uji sekali sebelum kamu membutuhkannya) ---
  # Basis data:
  zcat /var/backups/simama/db-STEMPEL.sql.gz \
    | docker compose exec -T -e MYSQL_PWD="$DB_PASSWORD" db \
        mariadb --user=USER NAMA_DB

  # Berkas unggahan (jalankan dari akar proyek):
  tar -xzf /var/backups/simama/storage-STEMPEL.tar.gz
  docker compose exec app chown -R www-data:www-data storage
CARA
