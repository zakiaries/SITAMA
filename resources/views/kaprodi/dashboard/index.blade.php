@extends('layouts.kaprodi')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.kaprodi-hero {
  background: var(--warm);
  border-radius: 14px; padding: 24px 26px; margin-bottom: 20px;
  display: flex; align-items: center; gap: 18px;
}
.hero-av {
  width:64px;height:64px;border-radius:50%;flex-shrink:0;
  background:var(--primary);color:#fff;
  display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;
}
.hero-role { font-size:11px;font-weight:700;letter-spacing:.06em;color:var(--primary);text-transform:uppercase;margin-bottom:2px; }
.hero-name { font-size:26px;font-weight:800;color:var(--text);letter-spacing:-0.02em;line-height:1.1;margin-bottom:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.hero-prog { font-size:13px;color:var(--text-secondary); }
.hero-badge { display:inline-flex;align-items:center;gap:5px;background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:700;padding:4px 10px;border-radius:9999px; }

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
    <div class="hero-name">{{ $user->name }}<span class="hero-badge"><x-icon name="star" :size="12"/> Superadmin</span></div>
    <div class="hero-prog">Teknik Informatika · Politeknik</div>
  </div>
</div>

{{-- Banner Notifikasi: Mahasiswa Menunggu Persetujuan --}}
@if($pendingMahasiswa > 0)
<div style="background:var(--warn-bg);border:1.5px solid #F3D9A0;border-radius:14px;padding:18px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
  <div style="width:48px;height:48px;border-radius:12px;background:#FBEBC8;color:var(--warn-text);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <x-icon name="bell" :size="22"/>
  </div>
  <div style="flex:1;min-width:200px;">
    <div style="font-size:15px;font-weight:800;color:var(--warn-text);margin-bottom:2px;">
      {{ $pendingMahasiswa }} pendaftar baru menunggu persetujuan
    </div>
    <div style="font-size:12px;color:var(--warn-text);">
      @foreach($pendingList as $p){{ $p->user->name }}@if(!$loop->last), @endif @endforeach
      @if($pendingMahasiswa > 3) dan {{ $pendingMahasiswa - 3 }} lainnya @endif
      perlu Anda tinjau sebelum bisa masuk ke sistem.
    </div>
  </div>
  <a href="{{ route('kaprodi.mahasiswa.index', ['status' => 'pending']) }}"
     style="background:var(--warn-text);color:#fff;font-size:13px;font-weight:700;padding:10px 20px;border-radius:10px;text-decoration:none;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:6px;">
    Tinjau Sekarang <x-icon name="arrow-right" :size="15"/>
  </a>
</div>
@endif

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--blue-tint);color:var(--primary);"><x-icon name="cap" :size="20"/></div>
    <div class="sc-val">{{ $totalMahasiswa }}</div>
    <div class="sc-lbl">Total Mahasiswa</div>
    <div class="sc-sub">{{ $aktif }} aktif · {{ $selesai }} selesai</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--warn-bg);color:var(--warn-text);"><x-icon name="alert" :size="20"/></div>
    <div class="sc-val">{{ $belumMagang }}</div>
    <div class="sc-lbl">Belum Magang</div>
    <div class="sc-sub">belum ada data magang</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--success-bg);color:var(--success-text);"><x-icon name="calendar" :size="20"/></div>
    <div class="sc-val">{{ $totalSeminar }}</div>
    <div class="sc-lbl">Seminar</div>
    <div class="sc-sub">total terjadwal</div>
  </div>
</div>

{{-- Quick Actions --}}
<div class="card-title" style="margin-bottom:14px;">Aksi Cepat</div>
<div class="qa-grid">
  <a href="{{ route('kaprodi.mahasiswa.index') }}" class="qa-card">
    <div class="qa-icon" style="background:var(--blue-tint);color:var(--primary);"><x-icon name="users" :size="22"/></div>
    <div class="qa-text">
      <div class="qa-title">Data Mahasiswa</div>
      <div class="qa-sub">{{ $totalMahasiswa }} mahasiswa terdaftar</div>
    </div>
    <span class="qa-arr"><x-icon name="chevron-right" :size="18"/></span>
  </a>
  <a href="{{ route('kaprodi.dosen.index') }}" class="qa-card">
    <div class="qa-icon" style="background:var(--success-bg);color:var(--success-text);"><x-icon name="cap" :size="22"/></div>
    <div class="qa-text">
      <div class="qa-title">Data Dosen</div>
      <div class="qa-sub">{{ $totalDosen }} dosen</div>
    </div>
    <span class="qa-arr"><x-icon name="chevron-right" :size="18"/></span>
  </a>
  <a href="{{ route('kaprodi.mahasiswa.index', ['status' => 'belum_magang']) }}" class="qa-card">
    <div class="qa-icon" style="background:var(--warn-bg);color:var(--warn-text);"><x-icon name="pencil" :size="22"/></div>
    <div class="qa-text">
      <div class="qa-title">Tugaskan Dosen</div>
      <div class="qa-sub">kelola pembimbing mahasiswa</div>
    </div>
    <span class="qa-arr"><x-icon name="chevron-right" :size="18"/></span>
  </a>
</div>

@endsection
