<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SITAMA — @yield('title', 'Dashboard')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/sitama.css') }}">
  <script src="{{ asset('js/sitama-anim.js') }}" defer></script>
  @stack('styles')
</head>
<body>
<div class="app">
  @include('components.mahasiswa.sidebar')
  <div class="main">
    @include('components.mahasiswa.topbar', ['title' => $title ?? 'Dashboard'])
    <div class="content">
      @yield('content')
    </div>
  </div>
</div>

{{-- Modal Global --}}
@include('mahasiswa.bimbingan.modal')
@include('mahasiswa.logbook.modal')

<script>
function toggle(header) {
  header.parentElement.classList.toggle('open');
}

function openModal(id) {
  document.getElementById(id).classList.add('open');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('open');
    });
  });

  document.querySelectorAll('.chip').forEach(c => {
    c.addEventListener('click', function() {
      document.querySelectorAll('.chip').forEach(x => x.classList.remove('active'));
      this.classList.add('active');
    });
  });

  document.querySelectorAll('.tab').forEach(t => {
    t.addEventListener('click', function() {
      document.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
      this.classList.add('active');
    });
  });
});
</script>
@include('components.confirm-dialog')
@stack('scripts')
</body>
</html>
