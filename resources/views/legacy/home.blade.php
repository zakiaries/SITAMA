@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-title">Selamat Datang</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="content-section">
                <h2 class="mb-3" style="color: #2c3e50;">Halaman Beranda</h2>
                <p>
                    Selamat datang di SIMAMA Web, sebuah platform modern yang dirancang untuk memberikan pengalaman terbaik 
                    kepada pengguna. Kami berkomitmen untuk menyediakan layanan berkualitas tinggi dengan antarmuka yang 
                    intuitif dan responsif.
                </p>
                <p>
                    Dengan menggunakan teknologi terkini dan best practices dalam pengembangan web, kami memastikan bahwa 
                    setiap aspek dari aplikasi ini dioptimalkan untuk kecepatan, keamanan, dan kemudahan penggunaan.
                </p>
                <p>
                    Jelajahi lebih lanjut dengan mengunjungi halaman <a href="{{ route('about') }}" class="text-decoration-none">About</a> 
                    untuk mempelajari lebih banyak tentang kami, atau hubungi kami melalui halaman <a href="{{ route('contact') }}" class="text-decoration-none">Contact</a>.
                </p>
            </div>

            <div class="content-section">
                <h3 class="mb-3" style="color: #3498db;">Fitur Utama</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #f0f4f8; border-left: 4px solid #3498db;">
                            <h5 style="color: #2c3e50;">📱 Responsive Design</h5>
                            <p class="mb-0">Desain yang responsif dan menyesuaikan dengan semua ukuran perangkat.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #f0f4f8; border-left: 4px solid #3498db;">
                            <h5 style="color: #2c3e50;">⚡ Performa Tinggi</h5>
                            <p class="mb-0">Aplikasi yang cepat dan efisien dengan optimasi maksimal.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #f0f4f8; border-left: 4px solid #3498db;">
                            <h5 style="color: #2c3e50;">🔒 Aman</h5>
                            <p class="mb-0">Keamanan data dengan enkripsi dan perlindungan maksimal.</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded" style="background-color: #f0f4f8; border-left: 4px solid #3498db;">
                            <h5 style="color: #2c3e50;">🎨 Modern UI</h5>
                            <p class="mb-0">Antarmuka pengguna yang modern dan mudah digunakan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="content-section">
                <h4 style="color: #2c3e50; margin-bottom: 20px;">Navigasi Cepat</h4>
                <div class="list-group">
                    <a href="{{ route('home') }}" class="list-group-item list-group-item-action">
                        <strong>🏠 Home</strong>
                        <p class="mb-0 small text-muted">Kembali ke halaman utama</p>
                    </a>
                    <a href="{{ route('about') }}" class="list-group-item list-group-item-action">
                        <strong>ℹ️ About</strong>
                        <p class="mb-0 small text-muted">Pelajari tentang kami</p>
                    </a>
                    <a href="{{ route('contact') }}" class="list-group-item list-group-item-action">
                        <strong>📧 Contact</strong>
                        <p class="mb-0 small text-muted">Hubungi kami</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
