@extends('layouts.dosen-industri')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.industri-hero {
  background: var(--warm);
  border-radius: 14px; padding: 24px 26px;
  margin-bottom: 20px; display: flex; align-items: center; gap: 18px;
}
.hero-av { width:64px;height:64px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;flex-shrink:0; }
.hero-role { font-size:11px;font-weight:700;letter-spacing:.06em;color:var(--primary);text-transform:uppercase; }
.hero-name  { font-size:26px;font-weight:800;color:var(--text);margin-top:3px;line-height:1.1;letter-spacing:-0.02em; }
.hero-search {
  margin-top:14px;display:flex;align-items:center;gap:9px;
  background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:11px 14px;color:var(--text-muted);max-width:420px;
}
.hero-search svg { flex-shrink:0; }
.hero-search input { border:none;outline:none;background:transparent;font-size:14px;color:var(--text);width:100%;font-family:inherit; }

.stat-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px; }
/* Dua berjajar, bukan satu: kartu angka masih terbaca di lebar segitu. */
@media (max-width: 760px) { .stat-grid { grid-template-columns:repeat(2,1fr); } }
.stat-industri {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:16px;text-align:center;
}
.stat-industri .icon { font-size:24px;margin-bottom:6px; }
.stat-industri .val  { font-size:26px;font-weight:800;color:var(--primary); }
.stat-industri .lbl  { font-size:11px;color:var(--text-muted);margin-top:2px; }

.student-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;
  gap:14px;cursor:pointer;transition:all .15s;text-decoration:none;
}
.student-card:hover { border-color:var(--primary);box-shadow:0 2px 12px rgba(45,62,110,0.1);transform:translateY(-1px); }
.student-av   { width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px; }
.student-info { flex:1;min-width:0; }
.student-name { font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px; }
.student-nim  { font-size:12px;color:var(--text-muted);margin-bottom:4px; }
.student-meta { font-size:11px;color:var(--text-muted);display:flex;gap:8px;flex-wrap:wrap; }
.student-meta span { background:var(--warm);padding:2px 8px;border-radius:20px; }

.logbook-bar { margin-top:8px; }
.logbook-bar-label { display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.logbook-bar-track { height:5px;background:var(--border);border-radius:4px;overflow:hidden; }
.logbook-bar-fill  { height:100%;background:var(--primary);border-radius:4px;transition:width .3s; }

.badge-selesai { background:var(--success-bg);color:var(--success-text);border:1px solid #A7E8CF;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.badge-aktif   { background:var(--blue-tint);color:var(--primary);border:1px solid #C7DCFF;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="industri-hero">
  <x-avatar :user="$user" class="hero-av" />
  <div style="flex:1;">
    <div class="hero-role">Pembimbing Industri</div>
    <div class="hero-name">{{ $user->name }}</div>
    <form method="GET" action="{{ route('dosen-industri.dashboard') }}">
      <input type="hidden" name="periode" value="{{ $periode }}">
      <div class="hero-search">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input name="search" placeholder="Cari nama mahasiswa atau jurusan..." value="{{ request('search') }}">
      </div>
    </form>

    {{-- Hanya muncul bila pembimbing ini pernah membimbing lebih dari satu
         angkatan; kalau cuma satu, dropdown-nya tak menawarkan pilihan apa pun. --}}
    @if($periodeList->count() > 1)
      <form method="GET" action="{{ route('dosen-industri.dashboard') }}" style="margin-top:10px;">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <x-periode-select :periode="$periode" :list="$periodeList"/>
      </form>
    @endif
  </div>
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-industri">
    <div class="icon" style="color:var(--primary);"><x-icon name="cap" :size="24"/></div>
    <div class="val">{{ $totalMahasiswa }}</div>
    <div class="lbl">Total Mahasiswa</div>
  </div>
  <div class="stat-industri">
    <div class="icon" style="color:var(--success-text);"><x-icon name="refresh" :size="24"/></div>
    <div class="val">{{ $aktif }}</div>
    <div class="lbl">Sedang Aktif</div>
  </div>
  <div class="stat-industri">
    <div class="icon" style="color:var(--primary);"><x-icon name="message" :size="24"/></div>
    <div class="val">{{ $belumDikomen }}</div>
    <div class="lbl">Belum Dikomen</div>
  </div>
</div>

{{-- Daftar Mahasiswa --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
  <div class="card-title" style="margin:0;">
    Mahasiswa Magang
    <span style="background:var(--primary);color:#fff;font-size:11px;padding:2px 9px;border-radius:20px;margin-left:8px;font-weight:600;">
      {{ $students->count() }}
    </span>
  </div>
</div>

@forelse($students as $student)
@php
  $internship  = $student->internships->first();
  $isFinished  = $internship?->is_finished ?? false;
  $totalLog    = $student->logBooks->count();
  $dikomen     = $student->logBooks->whereNotNull('industry_note')->count();
  $pct         = $totalLog > 0 ? round($dikomen / $totalLog * 100) : 0;
  $colors = [
    ['bg'=>'var(--success-bg)','text'=>'var(--success-text)'],['bg'=>'var(--blue-tint)','text'=>'var(--primary)'],
    ['bg'=>'var(--warn-bg)','text'=>'var(--warn-text)'],['bg'=>'var(--purple-bg)','text'=>'var(--purple-text)'],
    ['bg'=>'var(--danger-bg)','text'=>'var(--danger)'],
  ];
  $color   = $colors[$student->id % count($colors)];
@endphp
<a href="{{ route('dosen-industri.mahasiswa.detail', $student) }}" class="student-card">
  <x-avatar :user="$student->user" class="student-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};" />
  <div class="student-info">
    <div class="student-name">
      {{ $student->user->name }}
      @if($totalLog - $dikomen > 0)
        <span class="dot-baru" title="{{ $totalLog - $dikomen }} logbook belum kamu komentari"></span>
      @endif
    </div>
    <div class="student-nim">{{ $student->user->username }}</div>
    <div class="student-meta">
      <span>{{ $internship?->position ?? '-' }}</span>
      <span>{{ $student->the_class }}</span>
      @if($internship?->company)
        <span>{{ $internship->company->name }}</span>
      @endif
    </div>
    @if($totalLog - $dikomen > 0)
      <div style="margin-top:6px;">
        <span class="tag-baru">{{ $totalLog - $dikomen }} logbook belum dikomentari</span>
      </div>
    @endif
    <div class="logbook-bar">
      <div class="logbook-bar-label">
        <span>Logbook dikomentari</span>
        <span>{{ $dikomen }}/{{ $totalLog }}</span>
      </div>
      <div class="logbook-bar-track">
        <div class="logbook-bar-fill" style="width:{{ $pct }}%;"></div>
      </div>
    </div>
  </div>
  @if($isFinished)
    <span class="badge-selesai">Selesai</span>
  @else
    <span class="badge-aktif">Aktif</span>
  @endif
</a>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="factory" :size="40"/></div>
  <p>Belum ada mahasiswa yang ditugaskan.</p>
</div>
@endforelse

@endsection
