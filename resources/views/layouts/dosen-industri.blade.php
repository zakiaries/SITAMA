<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SITAMA Industri — @yield('title', 'Dashboard')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/sitama.css') }}">
  @stack('styles')
</head>
<body>
<div class="app">
  @include('components.dosen-industri.sidebar')
  <div class="main">
    @include('components.dosen-industri.topbar', ['title' => $title ?? 'Dashboard'])
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
