@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-title">Tentang Kami</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="content-section">
                <h2 class="mb-3" style="color: #2c3e50;">Siapa Kami?</h2>
                <p>
                    SITAMA adalah sebuah inisiatif inovatif yang didedikasikan untuk menghadirkan solusi web terbaik 
                    bagi komunitas Indonesia. Kami percaya bahwa teknologi harus dapat diakses oleh semua orang, 
                    terlepas dari latar belakang atau keahlian mereka.
                </p>
                <p>
                    Tim kami terdiri dari developer berpengalaman, designer kreatif, dan profesional IT yang bersemangat 
                    dalam menciptakan aplikasi berkualitas tinggi. Kami terus berinovasi dan belajar untuk memberikan 
                    produk terbaik kepada pengguna kami.
                </p>
            </div>

            <div class="content-section">
                <h3 class="mb-3" style="color: #3498db;">Visi & Misi</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background-color: #e8f4f8; border: 2px solid #3498db;">
                            <h5 style="color: #2c3e50; margin-bottom: 15px;">👁️ Visi</h5>
                            <p>
                                Menjadi platform web terdepan yang memberdayakan bisnis dan individu melalui teknologi 
                                yang inovatif dan terpercaya.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background-color: #f0f8e8; border: 2px solid #27ae60;">
                            <h5 style="color: #2c3e50; margin-bottom: 15px;">🎯 Misi</h5>
                            <p>
                                Mengembangkan solusi web yang user-friendly, scalable, dan secure untuk membantu 
                                klien kami mencapai kesuksesan digital.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <h3 class="mb-3" style="color: #3498db;">Nilai-Nilai Kami</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #fff3e0; border-left: 4px solid #ff9800;">
                            <h5 style="color: #2c3e50;">💡 Inovasi</h5>
                            <p class="mb-0">Kami selalu mencari cara baru untuk mengatasi tantangan dan meningkatkan produk.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #fce4ec; border-left: 4px solid #e91e63;">
                            <h5 style="color: #2c3e50;">🤝 Integritas</h5>
                            <p class="mb-0">Kami menjalankan bisnis dengan transparansi dan kejujuran.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #f3e5f5; border-left: 4px solid #9c27b0;">
                            <h5 style="color: #2c3e50;">👥 Kolaborasi</h5>
                            <p class="mb-0">Kami percaya pada kekuatan kerja sama tim untuk mencapai hasil terbaik.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #e0f2f1; border-left: 4px solid #009688;">
                            <h5 style="color: #2c3e50;">📈 Pertumbuhan</h5>
                            <p class="mb-0">Kami berkomitmen pada pembelajaran berkelanjutan dan peningkatan diri.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <h3 class="mb-3" style="color: #3498db;">Teknologi yang Kami Gunakan</h3>
                <p>Kami membangun aplikasi menggunakan teknologi modern dan terbukti:</p>
                <ul style="line-height: 2;">
                    <li><strong>Backend:</strong> Laravel, PHP 8.x</li>
                    <li><strong>Frontend:</strong> Blade Template, Bootstrap 5, JavaScript</li>
                    <li><strong>Database:</strong> MySQL/PostgreSQL</li>
                    <li><strong>Deployment:</strong> Docker, Git, CI/CD</li>
                    <li><strong>Tools:</strong> Composer, NPM, Webpack</li>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="content-section">
                <h4 style="color: #2c3e50; margin-bottom: 20px;">📊 Statistik</h4>
                <div style="text-align: center;">
                    <div class="mb-3">
                        <h3 style="color: #3498db; font-weight: 700;">50+</h3>
                        <p class="text-muted">Proyek Selesai</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h3 style="color: #3498db; font-weight: 700;">30+</h3>
                        <p class="text-muted">Klien Puas</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h3 style="color: #3498db; font-weight: 700;">100%</h3>
                        <p class="text-muted">Kepuasan Pelanggan</p>
                    </div>
                </div>
            </div>

            <div class="content-section">
                <h4 style="color: #2c3e50; margin-bottom: 20px;">🔗 Tautan Cepat</h4>
                <div class="d-grid gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">Home</a>
                    <a href="{{ route('contact') }}" class="btn btn-primary">Hubungi Kami</a>
                </div>
            </div>
        </div>
    </div>
@endsection
