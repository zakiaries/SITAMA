@extends('layouts.app')

@section('title', 'Hubungi Kami')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-title">Hubungi Kami</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="content-section">
                <h2 class="mb-3" style="color: #2c3e50;">Formulir Kontak</h2>
                <p class="mb-4">
                    Kami senang mendengar dari Anda! Silakan isi formulir di bawah ini dan kami akan merespons secepat mungkin.
                </p>

                <form method="POST" action="#" id="contactForm" novalidate>
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span style="color: #e74c3c;">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Masukkan nama Anda" required>
                        <small class="text-muted">Masukkan nama lengkap Anda</small>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span style="color: #e74c3c;">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Masukkan email Anda" required>
                        <small class="text-muted">Kami akan menggunakan email ini untuk menghubungi Anda kembali</small>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="08xx xxxx xxxx">
                        <small class="text-muted">Opsional - Nomor telepon untuk kontak lebih cepat</small>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subjek <span style="color: #e74c3c;">*</span></label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="">Pilih Subjek</option>
                            <option value="pertanyaan_umum">Pertanyaan Umum</option>
                            <option value="konsultasi">Konsultasi Bisnis</option>
                            <option value="keluhanBug">Laporan Bug</option>
                            <option value="feedback">Feedback & Saran</option>
                            <option value="kerjasama">Kerjasama</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan <span style="color: #e74c3c;">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="6" 
                                  placeholder="Tulis pesan Anda di sini..." required></textarea>
                        <small class="text-muted">Minimal 10 karakter - Jelaskan pertanyaan atau kebutuhan Anda</small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                        <label class="form-check-label" for="agree">
                            Saya setuju dengan <a href="#">Kebijakan Privasi</a> dan <a href="#">Syarat & Ketentuan</a>
                        </label>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Kirim Pesan
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-clockwise"></i> Bersihkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="content-section">
                <h4 style="color: #2c3e50; margin-bottom: 20px;">📍 Informasi Kontak</h4>
                
                <div class="mb-4">
                    <h6 style="color: #3498db; margin-bottom: 10px;">📧 Email</h6>
                    <p class="mb-0">
                        <a href="mailto:info@sitama.com" style="color: #3498db; text-decoration: none;">
                            info@sitama.com
                        </a>
                    </p>
                    <small class="text-muted">Respon dalam 24 jam kerja</small>
                </div>

                <div class="mb-4">
                    <h6 style="color: #3498db; margin-bottom: 10px;">📞 Telepon</h6>
                    <p class="mb-0">
                        <a href="tel:+6281234567890" style="color: #3498db; text-decoration: none;">
                            +62 812 3456 7890
                        </a>
                    </p>
                    <small class="text-muted">Senin - Jumat, 09:00 - 17:00 WIB</small>
                </div>

                <div class="mb-4">
                    <h6 style="color: #3498db; margin-bottom: 10px;">📍 Alamat</h6>
                    <p>
                        Jl. Merdeka No. 123<br>
                        Jakarta Pusat<br>
                        Indonesia
                    </p>
                    <small class="text-muted">Kantor Pusat</small>
                </div>

                <hr>

                <div class="mb-4">
                    <h6 style="color: #3498db; margin-bottom: 15px;">🌐 Media Sosial</h6>
                    <div class="d-flex gap-2">
                        <a href="https://facebook.com/sitama" class="btn btn-sm btn-outline-primary" target="_blank">
                            Facebook
                        </a>
                        <a href="https://twitter.com/sitama" class="btn btn-sm btn-outline-info" target="_blank">
                            Twitter
                        </a>
                        <a href="https://instagram.com/sitama" class="btn btn-sm btn-outline-danger" target="_blank">
                            Instagram
                        </a>
                    </div>
                </div>

                <hr>

                <div class="p-3 rounded" style="background-color: #e8f4f8; border-left: 4px solid #3498db;">
                    <h6 style="color: #2c3e50; margin-bottom: 10px;">⏱️ Jam Operasional</h6>
                    <ul class="mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                        <li><strong>Senin - Jumat:</strong> 09:00 - 17:00</li>
                        <li><strong>Sabtu:</strong> 10:00 - 14:00</li>
                        <li><strong>Minggu & Libur:</strong> Tutup</li>
                    </ul>
                </div>
            </div>

            <div class="content-section">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">❓ FAQ</h4>
                <p style="font-size: 0.9rem;">
                    Pertanyaan umum? Kunjungi halaman <a href="#" style="color: #3498db;">FAQ kami</a> 
                    untuk menemukan jawaban cepat.
                </p>
                <a href="#" class="btn btn-sm btn-outline-primary w-100">Lihat FAQ</a>
            </div>
        </div>
    </div>
@endsection

@section('extra-js')
    <script>
        // Simple form validation
        const form = document.getElementById('contactForm');
        
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity() === false) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    </script>
@endsection
