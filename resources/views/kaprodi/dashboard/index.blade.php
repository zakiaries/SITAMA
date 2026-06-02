@extends('layouts.kaprodi')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.kaprodi-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px; padding: 26px 24px; margin-bottom: 20px;
  position: relative; overflow: hidden; display: flex; align-items: center; gap: 18px;
}
.kaprodi-hero::before { content:''; position:absolute; inset:0; background:rgba(45,62,110,0.82); }
.kaprodi-hero > * { position:relative; z-index:1; }
.hero-av {
  width:60px;height:60px;border-radius:50%;flex-shrink:0;
  background:rgba(255,255,255,0.2);border:2.5px solid rgba(255,255,255,0.4);
  display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;
}
.hero-role { font-size:12px;color:rgba(255,255,255,0.7);font-weight:600;margin-bottom:2px; }
.hero-name { font-size:20px;font-weight:800;color:#fff;margin-bottom:4px; }
.hero-prog { font-size:12px;color:rgba(255,255,255,0.75); }
.hero-badge { display:inline-block;background:#fbbf24;color:#78350f;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;margin-left:8px; }

.stat-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px; }
.stat-card-k {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;
}
.stat-card-k .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:19px;margin-bottom:10px; }
.stat-card-k .sc-val  { font-size:28px;font-weight:800;color:var(--text);line-height:1; }
.stat-card-k .sc-lbl  { font-size:12px;color:var(--text-muted);margin-top:4px; }
.stat-card-k .sc-sub  { font-size:11px;color:var(--text-muted);margin-top:6px; }

.qa-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:14px; }
.qa-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;
  display:flex;align-items:center;gap:14px;text-decoration:none;transition:all .15s;
}
.qa-card:hover { border-color:var(--primary);box-shadow:0 2px 12px rgba(45,62,110,0.08);transform:translateY(-1px); }
.qa-icon { width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.qa-text { flex:1; }
.qa-title { font-size:14px;font-weight:700;color:var(--text); }
.qa-sub   { font-size:12px;color:var(--text-muted);margin-top:2px; }
.qa-arr   { color:var(--text-muted); }
</style>
@endpush

@section('content')

{{-- Hero --}}
@php $initials = collect(explode(' ', $user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
<div class="kaprodi-hero">
  <div class="hero-av">{{ $initials }}</div>
  <div style="flex:1;">
    <div class="hero-role">KETUA PROGRAM STUDI</div>
    <div class="hero-name">{{ $user->name }}<span class="hero-badge">⭐ Superadmin</span></div>
    <div class="hero-prog">Teknik Informatika · Politeknik</div>
  </div>
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-card-k">
    <div class="sc-icon" style="background:#e8eef8;">🎓</div>
    <div class="sc-val">{{ $totalMahasiswa }}</div>
    <div class="sc-lbl">Total Mahasiswa</div>
    <div class="sc-sub">{{ $aktif }} aktif · {{ $selesai }} selesai</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:#fef9c3;">⚠️</div>
    <div class="sc-val">{{ $belumMagang }}</div>
    <div class="sc-lbl">Belum Magang</div>
    <div class="sc-sub">belum ada data magang</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:#f3e8ff;">🏭</div>
    <div class="sc-val">{{ $verifIndustri }}</div>
    <div class="sc-lbl">Verifikasi Industri</div>
    <div class="sc-sub">menunggu verifikasi</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:#dcfce7;">📅</div>
    <div class="sc-val">{{ $totalSeminar }}</div>
    <div class="sc-lbl">Seminar</div>
    <div class="sc-sub">total terjadwal</div>
  </div>
</div>

{{-- Quick Actions --}}
<div class="card-title" style="margin-bottom:14px;">Aksi Cepat</div>
<div class="qa-grid">
  <a href="{{ route('kaprodi.mahasiswa.index') }}" class="qa-card">
    <div class="qa-icon" style="background:#e8eef8;">👥</div>
    <div class="qa-text">
      <div class="qa-title">Data Mahasiswa</div>
      <div class="qa-sub">{{ $totalMahasiswa }} mahasiswa terdaftar</div>
    </div>
    <span class="qa-arr">›</span>
  </a>
  <a href="{{ route('kaprodi.industri.index') }}" class="qa-card">
    <div class="qa-icon" style="background:#f3e8ff;">🏭</div>
    <div class="qa-text">
      <div class="qa-title">Verifikasi Industri</div>
      <div class="qa-sub">{{ $verifIndustri }} menunggu</div>
    </div>
    <span class="qa-arr">›</span>
  </a>
  <a href="{{ route('kaprodi.dosen.index') }}" class="qa-card">
    <div class="qa-icon" style="background:#dcfce7;">👨‍🏫</div>
    <div class="qa-text">
      <div class="qa-title">Data Dosen</div>
      <div class="qa-sub">{{ $totalDosen }} dosen</div>
    </div>
    <span class="qa-arr">›</span>
  </a>
  <a href="{{ route('kaprodi.mahasiswa.index', ['status' => 'belum_magang']) }}" class="qa-card">
    <div class="qa-icon" style="background:#fef9c3;">📝</div>
    <div class="qa-text">
      <div class="qa-title">Tugaskan Dosen</div>
      <div class="qa-sub">kelola pembimbing mahasiswa</div>
    </div>
    <span class="qa-arr">›</span>
  </a>
</div>

@endsection
