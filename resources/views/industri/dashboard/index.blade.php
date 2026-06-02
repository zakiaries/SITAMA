@extends('layouts.industri')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.ind-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px; padding: 26px 24px; margin-bottom: 20px;
  position: relative; overflow: hidden; display: flex; align-items: center; gap: 18px;
}
.ind-hero::before { content:''; position:absolute; inset:0; background:rgba(45,62,110,0.82); }
.ind-hero > * { position:relative; z-index:1; }
.hero-av { width:60px;height:60px;border-radius:14px;flex-shrink:0;background:rgba(255,255,255,0.2);border:2.5px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff; }
.hero-name { font-size:20px;font-weight:800;color:#fff;margin-bottom:3px; }
.hero-addr { font-size:12px;color:rgba(255,255,255,0.75);margin-bottom:6px; }

.stat-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px; }
.stat-card-i { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px; }
.stat-card-i .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:19px;margin-bottom:10px; }
.stat-card-i .sc-val { font-size:28px;font-weight:800;color:var(--text);line-height:1; }
.stat-card-i .sc-lbl { font-size:12px;color:var(--text-muted);margin-top:4px; }

.qa-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:14px; }
.qa-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:all .15s; }
.qa-card:hover { border-color:var(--primary);box-shadow:0 2px 12px rgba(45,62,110,0.08);transform:translateY(-1px); }
.qa-icon { width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0; }
.qa-title { font-size:14px;font-weight:700;color:var(--text); }
.qa-sub { font-size:12px;color:var(--text-muted);margin-top:2px; }
</style>
@endpush

@section('content')

@php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $company->name)))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
<div class="ind-hero">
  <div class="hero-av">{{ $initials }}</div>
  <div style="flex:1;">
    <div class="hero-name">{{ $company->name }}</div>
    <div class="hero-addr">📍 {{ $company->address ?? 'Alamat belum diisi' }}</div>
    @if($company->verification_status === 'verified')
      <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px;">✓ Akun Terverifikasi</span>
    @else
      <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px;">⏳ Menunggu Verifikasi</span>
    @endif
  </div>
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#e8eef8;">📋</div>
    <div class="sc-val">{{ $lowonganAktif }}</div>
    <div class="sc-lbl">Lowongan Aktif</div>
  </div>
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#fef9c3;">⏳</div>
    <div class="sc-val">{{ $pelamarPending }}</div>
    <div class="sc-lbl">Pelamar Menunggu</div>
  </div>
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#dcfce7;">✓</div>
    <div class="sc-val">{{ $pelamarDiterima }}</div>
    <div class="sc-lbl">Pelamar Diterima</div>
  </div>
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#f3e8ff;">👨‍💼</div>
    <div class="sc-val">{{ $magangAktif }}</div>
    <div class="sc-lbl">Magang Aktif</div>
  </div>
</div>

{{-- Quick Actions --}}
<div class="card-title" style="margin-bottom:14px;">Aksi Cepat</div>
<div class="qa-grid">
  <a href="{{ route('industri.lowongan.create') }}" class="qa-card">
    <div class="qa-icon" style="background:#e8eef8;">➕</div>
    <div style="flex:1;">
      <div class="qa-title">Buat Lowongan</div>
      <div class="qa-sub">Terbitkan lowongan magang baru</div>
    </div>
    <span style="color:var(--text-muted);">›</span>
  </a>
  <a href="{{ route('industri.pelamar.index') }}" class="qa-card">
    <div class="qa-icon" style="background:#fef9c3;">👥</div>
    <div style="flex:1;">
      <div class="qa-title">Review Pelamar</div>
      <div class="qa-sub">{{ $pelamarPending }} menunggu keputusan</div>
    </div>
    <span style="color:var(--text-muted);">›</span>
  </a>
  <a href="{{ route('industri.lowongan.index') }}" class="qa-card">
    <div class="qa-icon" style="background:#dcfce7;">📋</div>
    <div style="flex:1;">
      <div class="qa-title">Kelola Lowongan</div>
      <div class="qa-sub">{{ $totalLowongan }} lowongan total</div>
    </div>
    <span style="color:var(--text-muted);">›</span>
  </a>
  <a href="{{ route('industri.profile') }}" class="qa-card">
    <div class="qa-icon" style="background:#f3e8ff;">🏢</div>
    <div style="flex:1;">
      <div class="qa-title">Profil Perusahaan</div>
      <div class="qa-sub">Kelola data perusahaan</div>
    </div>
    <span style="color:var(--text-muted);">›</span>
  </a>
</div>

@endsection
