<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMAMA Dosen — @yield('title', 'Dashboard')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  {{-- ?v= waktu-ubah berkas: tanpa ini peramban menyajikan salinan lama
       setelah deploy, dan perbaikan tampilan tak pernah sampai ke pengguna. --}}
  <link rel="stylesheet" href="{{ asset('css/simama.css') }}?v={{ @filemtime(public_path('css/simama.css')) }}">
  <script src="{{ asset('js/simama-anim.js') }}?v={{ @filemtime(public_path('js/simama-anim.js')) }}" defer></script>
  @stack('styles')
</head>
<body>
<div class="app">
  {{-- Laci menu layar sempit. Kotak centang ini mekanismenya, bukan JavaScript:
       simama-anim.js berhenti lebih awal bila pengguna memilih "kurangi animasi",
       sehingga tombol menu yang menumpang di sana akan mati justru bagi orang itu.
       Letaknya WAJIB sebelum sidebar & kelambu — pemilih `~` hanya menjangkau
       saudara yang datang sesudahnya. --}}
  <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Buka menu navigasi">
  @include('components.dosen.sidebar')
  <label for="nav-toggle" class="nav-scrim" aria-hidden="true"></label>
  <div class="main">
    @include('components.dosen.topbar', ['title' => $title ?? 'Dashboard'])
    <div class="content">
      @yield('content')
    </div>
  </div>
</div>

<script>
function toggle(header) {
  header.parentElement.classList.toggle('open');
}
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function (e) {
      if (e.target === this) this.classList.remove('open');
    });
  });
});
</script>
@include('components.confirm-dialog')
@stack('scripts')
</body>
</html>
