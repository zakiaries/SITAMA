<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotKnowledge;
use Illuminate\Support\Facades\Schema;

/**
 * Basis pengetahuan (knowledge base) chatbot SIMAMA.
 *
 * Tiap entri berisi:
 *   - pertanyaan : pertanyaan utama (representatif) yang ditampilkan ke pengguna.
 *   - kata_kunci : variasi kata/frasa yang memperkaya dokumen agar pencocokan
 *                  TF-IDF lebih tahan terhadap perbedaan gaya bahasa pengguna.
 *   - jawaban    : jawaban/panduan yang direkomendasikan bila entri ini paling mirip.
 *   - kategori   : label pengelompokan untuk ditampilkan.
 *
 * Dokumen korpus yang divektorkan = gabungan "pertanyaan" + "kata_kunci".
 *
 * Sumber data: tabel `chatbot_knowledges` (dikelola Kaprodi). Bila tabel belum
 * ada atau masih kosong, dipakai daftar bawaan (defaultEntries) sebagai
 * fallback sekaligus sumber data untuk seeder.
 */
class KnowledgeBase
{
    /**
     * Entri aktif untuk melatih chatbot — dari DB, fallback ke bawaan.
     *
     * @return array<int, array{pertanyaan:string, kata_kunci:string, jawaban:string, kategori:string}>
     */
    public static function entries(): array
    {
        try {
            if (Schema::hasTable('chatbot_knowledges')) {
                $rows = ChatbotKnowledge::where('is_active', true)
                    ->orderBy('id')
                    ->get(['pertanyaan', 'kata_kunci', 'jawaban', 'kategori']);

                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => [
                        'pertanyaan' => $r->pertanyaan,
                        'kata_kunci' => $r->kata_kunci,
                        'jawaban'    => $r->jawaban,
                        'kategori'   => $r->kategori,
                    ])->all();
                }
            }
        } catch (\Throwable $e) {
            // Abaikan (mis. DB belum siap) dan pakai fallback bawaan.
        }

        return self::defaultEntries();
    }

    /**
     * Daftar FAQ bawaan (fallback + sumber seeder awal).
     *
     * @return array<int, array{pertanyaan:string, kata_kunci:string, jawaban:string, kategori:string}>
     */
    public static function defaultEntries(): array
    {
        return [
            [
                'pertanyaan' => 'Bagaimana cara masuk (login) ke SIMAMA?',
                'kata_kunci' => 'login masuk akun sign in NIM nomor induk mahasiswa kata sandi password halaman awal',
                'jawaban'    => 'Masuk melalui halaman login menggunakan NIM sebagai username dan kata sandimu. Setelah berhasil, kamu diarahkan ke dashboard mahasiswa.',
                'kategori'   => 'Akun',
            ],
            [
                'pertanyaan' => 'Saya lupa kata sandi, bagaimana cara reset password?',
                'kata_kunci' => 'lupa password kata sandi reset ganti ubah tidak bisa masuk akun terkunci',
                'jawaban'    => 'Jika lupa kata sandi, hubungi Kaprodi/admin prodi. Kaprodi memiliki fitur reset password untuk semua akun (mahasiswa, dosen, dan pembimbing industri).',
                'kategori'   => 'Akun',
            ],
            [
                'pertanyaan' => 'Kenapa akun saya masih berstatus menunggu dan belum bisa mengakses fitur?',
                'kata_kunci' => 'status menunggu pending akun belum aktif verifikasi disetujui approve registrasi daftar akun baru',
                'jawaban'    => 'Setelah registrasi, statusmu "menunggu" sampai Kaprodi menyetujui akunmu. Begitu disetujui, kamu bisa mengakses semua fitur dan Kaprodi akan menetapkan dosen pembimbingmu.',
                'kategori'   => 'Akun',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengajukan magang di SIMAMA?',
                'kata_kunci' => 'ajukan mengajukan magang daftar lapor sudah diterima perusahaan tempat magang pengajuan bukti penerimaan posisi tanggal mulai',
                'jawaban'    => 'Buka menu Ajukan Magang, lalu isi data perusahaan, posisi, tanggal mulai, data pembimbing industri (nama, email, no. HP), dan unggah bukti penerimaan magang. Pengajuan dikirim ke Kaprodi untuk disetujui.',
                'kategori'   => 'Magang',
            ],
            [
                'pertanyaan' => 'Saya diterima magang sendiri di luar platform, apakah bisa dilaporkan?',
                'kata_kunci' => 'magang mandiri luar platform cari sendiri perusahaan sendiri lowongan tidak ada bukti surat penerimaan',
                'jawaban'    => 'Bisa. SIMAMA tidak menyediakan lowongan — kamu mencari dan mendaftar magang di luar sistem. Setelah diterima, laporkan lewat menu Ajukan Magang dan unggah bukti penerimaan untuk diverifikasi Kaprodi.',
                'kategori'   => 'Magang',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengajukan bimbingan ke dosen pembimbing?',
                'kata_kunci' => 'bimbingan konsultasi dosen pembimbing ajukan tambah judul aktivitas unggah file laporan pdf',
                'jawaban'    => 'Buka menu Bimbingan lalu tambah bimbingan: isi judul, tanggal, aktivitas, dan unggah file laporan (PDF). Dosen pembimbing akan menyetujui atau meminta revisi.',
                'kategori'   => 'Bimbingan',
            ],
            [
                'pertanyaan' => 'Bimbingan saya diminta revisi, apa yang harus dilakukan?',
                'kata_kunci' => 'bimbingan revisi ditolak rejected updated perbaiki edit unggah ulang file terbaru status',
                'jawaban'    => 'Jika dosen meminta revisi, edit bimbinganmu dan unggah file terbaru untuk diperiksa kembali. Status bimbingan dapat berupa: disetujui, in-progress, rejected, atau updated.',
                'kategori'   => 'Bimbingan',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengisi logbook harian?',
                'kata_kunci' => 'logbook log book catatan harian aktivitas kegiatan isi tambah judul deskripsi urutkan sortir komentar',
                'jawaban'    => 'Buka menu Log Book lalu tambah entri: isi judul, tanggal, dan deskripsi kegiatan harian. Dosen kampus dan pembimbing industri dapat memberi komentar pada tiap entri. Daftar logbook bisa diurutkan.',
                'kategori'   => 'Log Book',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengunggah laporan akhir magang?',
                'kata_kunci' => 'laporan akhir magang unggah upload file pdf word acc disetujui dosen revisi',
                'jawaban'    => 'Buka menu Laporan Akhir lalu unggah file laporan (PDF/Word). Dosen kampus akan menyetujui (ACC) atau meminta revisi. Laporan yang sudah di-ACC menjadi salah satu syarat mengajukan seminar.',
                'kategori'   => 'Laporan',
            ],
            [
                'pertanyaan' => 'Di mana saya mengunggah sertifikat magang?',
                'kata_kunci' => 'sertifikat magang unggah upload surat keterangan selesai perusahaan bukti magang selesai',
                'jawaban'    => 'Pada halaman Magang Saya terdapat kartu Sertifikat Magang untuk mengunggah sertifikat (PDF/JPG/PNG) dari perusahaan. Sertifikat ini menjadi salah satu syarat mengajukan seminar.',
                'kategori'   => 'Magang',
            ],
            [
                'pertanyaan' => 'Bagaimana cara melihat nilai magang saya?',
                'kata_kunci' => 'nilai lihat hasil penilaian skor komponen rata rata dosen kampus pembimbing industri gabungan',
                'jawaban'    => 'Buka menu Nilai untuk melihat rata-rata nilai akhir beserta rincian per komponen. Nilai berasal dari dosen kampus dan pembimbing industri yang menilai secara terpisah, lalu digabungkan.',
                'kategori'   => 'Nilai',
            ],
            [
                'pertanyaan' => 'Apa saja syarat agar bisa mengajukan seminar magang?',
                'kata_kunci' => 'syarat seminar mengajukan tidak bisa tombol tidak aktif belum lengkap kelayakan magang selesai nilai industri sertifikat laporan acc',
                'jawaban'    => 'Kamu bisa mengajukan seminar bila 4 syarat terpenuhi: (1) magang sudah ditandai selesai, (2) nilai dari pembimbing industri sudah diisi, (3) sertifikat magang sudah diunggah, dan (4) laporan akhir sudah di-ACC dosen.',
                'kategori'   => 'Seminar',
            ],
            [
                'pertanyaan' => 'Berapa jumlah audiens minimal untuk seminar dan bagaimana mendaftarnya?',
                // "seminar" sempat tertulis dua kali di sini. Pengulangan itu
                // menaikkan bobot TF-nya sehingga entri ini memenangi kueri yang
                // hanya menyisakan kata "seminar" — misalnya "Kapan saya boleh
                // seminar?", yang seharusnya dijawab entri syarat seminar.
                'kata_kunci' => 'audiens peserta seminar minimal sepuluh 10 adik tingkat daftar mendaftar mahasiswa lain kuota',
                'jawaban'    => 'Seminar membutuhkan minimal 10 audiens (adik tingkat) yang mendaftar. Kamu juga dapat mendaftar sebagai audiens pada seminar mahasiswa lain melalui bagian "Seminar Mahasiswa Lain".',
                'kategori'   => 'Seminar',
            ],
            [
                'pertanyaan' => 'Bagaimana proses pengajuan jadwal seminar disetujui?',
                'kata_kunci' => 'jadwal seminar tanggal waktu tempat ajukan disetujui kaprodi acc persetujuan final',
                'jawaban'    => 'Setelah syarat lengkap, ajukan jadwal seminar (tanggal, waktu, tempat). Kaprodi akan menyetujui atau menolak jadwal tersebut sebelum menjadi final.',
                'kategori'   => 'Seminar',
            ],
            [
                'pertanyaan' => 'Bagaimana absensi seminar dengan QR Code dan berita acara?',
                // "absen" ditulis terpisah dari "absensi": stemmer tidak
                // memotong akhiran -si, sehingga tanpa kata ini pertanyaan
                // "Absen seminar pakai apa?" kehilangan kata pembedanya dan
                // menyusut menjadi "seminar" saja.
                'kata_kunci' => 'qr code seminar absensi absen kehadiran scan pindai berita acara tamu tanda tangan identitas hadir',
                'jawaban'    => 'Kehadiran audiens seminar dikonfirmasi melalui pemindaian QR Code. Tamu mengisi identitas dan tanda tangan pada halaman berita acara yang terbuka saat QR Code dipindai.',
                'kategori'   => 'Seminar',
            ],
            [
                'pertanyaan' => 'Bagaimana cara menandai magang saya selesai?',
                'kata_kunci' => 'selesai magang ajukan selesai tandai finish syarat sertifikat laporan nilai logbook minimal 20 kaprodi',
                'jawaban'    => 'Di halaman Magang Saya, ajukan "Selesai" bila syaratnya terpenuhi (sertifikat terunggah, laporan di-ACC, nilai kampus & industri terisi, serta minimal 20 logbook). Setelah itu Kaprodi yang menandai magangmu selesai.',
                'kategori'   => 'Magang',
            ],
            [
                'pertanyaan' => 'Siapa yang membuat akun pembimbing industri dan bagaimana aktivasinya?',
                'kata_kunci' => 'akun pembimbing industri dosen industri login dibuat kaprodi aktivasi email link username password',
                'jawaban'    => 'Akun pembimbing industri dibuat oleh Kaprodi berdasarkan data yang kamu isi saat Ajukan Magang. Pembimbing menerima email berisi link aktivasi untuk mengatur username dan password sendiri sebelum bisa login.',
                'kategori'   => 'Magang',
            ],
            [
                'pertanyaan' => 'Di mana saya melihat notifikasi atau pemberitahuan?',
                'kata_kunci' => 'notifikasi pemberitahuan lonceng pesan info status update tandai dibaca',
                'jawaban'    => 'Ikon lonceng di kanan atas dan menu Notifikasi menampilkan pemberitahuan seperti status pengajuan magang, komentar, dan persetujuan. Kamu bisa menandainya sudah dibaca.',
                'kategori'   => 'Umum',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengubah data profil saya?',
                'kata_kunci' => 'profil ubah edit data diri perbarui akun informasi pribadi ganti',
                'jawaban'    => 'Buka menu Profil dengan mengklik namamu di bagian bawah sidebar untuk melihat dan memperbarui data dirimu.',
                'kategori'   => 'Akun',
            ],
            [
                'pertanyaan' => 'Saya butuh bantuan lain, ke mana harus menghubungi?',
                'kata_kunci' => 'bantuan hubungi kontak admin kaprodi tanya lebih lanjut masalah keluhan help',
                'jawaban'    => 'Untuk hal yang tidak bisa diselesaikan sendiri (misalnya reset password atau verifikasi akun), hubungi Kaprodi/admin prodi. Halaman Bantuan dan Kontak juga tersedia untuk informasi lebih lanjut.',
                'kategori'   => 'Umum',
            ],
        ];
    }
}
