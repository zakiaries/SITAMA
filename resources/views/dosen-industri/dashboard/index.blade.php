@extends('layouts.dosen-industri')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.industri-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px; padding: 28px 24px 24px;
  margin-bottom: 20px; position: relative; overflow: hidden;
}
.industri-hero::before {
  content:''; position:absolute; inset:0;
  background:rgba(45,62,110,0.82);
}
.industri-hero > * { position:relative; z-index:1; }
.hero-hello { font-size:12px;color:rgba(255,255,255,0.7);letter-spacing:1px;font-weight:600;margin-bottom:4px; }
.hero-name  { font-size:22px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.3; }
.hero-sub   { font-size:12px;color:rgba(255,255,255,0.6);margin-bottom:16px; }
.hero-search {
  display:flex;align-items:center;gap:10px;
  background:rgba(255,255,255,0.95);border-radius:10px;padding:10px 14px;
}
.hero-search svg { flex-shrink:0;color:#9ca3af; }
.hero-search input { border:none;outline:none;background:transparent;font-size:13px;color:#1e2a4a;width:100%;font-family:inherit; }

.stat-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px; }
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
.student-meta span { background:#f1f5f9;padding:2px 8px;border-radius:20px; }

.logbook-bar { margin-top:8px; }
.logbook-bar-label { display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.logbook-bar-track { height:5px;background:#e5e7eb;border-radius:4px;overflow:hidden; }
.logbook-bar-fill  { height:100%;background:var(--primary);border-radius:4px;transition:width .3s; }

.badge-selesai { background:#f0fdf4;color:#16a34a;border:1px solid #86efac;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.badge-aktif   { background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="industri-hero">
  <div class="hero-hello">HELLO,</div>
  <div class="hero-name">{{ $user->name }}</div>
  <div class="hero-sub">🏭 Pembimbing Industri</div>
  <form method="GET" action="{{ route('dosen-industri.dashboard') }}">
    <div class="hero-search">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <input name="search" placeholder="Cari nama mahasiswa atau jurusan..." value="{{ request('search') }}">
    </div>
  </form>
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-industri">
    <div class="icon">👨‍🎓</div>
    <div class="val">{{ $totalMahasiswa }}</div>
    <div class="lbl">Total Mahasiswa</div>
  </div>
  <div class="stat-industri">
    <div class="icon">🔄</div>
    <div class="val">{{ $aktif }}</div>
    <div class="lbl">Sedang Aktif</div>
  </div>
  <div class="stat-industri">
    <div class="icon">💬</div>
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
  $dikomen     = $student->logBooks->whereNotNull('lecturer_note')->count();
  $pct         = $totalLog > 0 ? round($dikomen / $totalLog * 100) : 0;
  $colors = [
    ['bg'=>'#d1fae5','text'=>'#065f46'],['bg'=>'#e0f2fe','text'=>'#075985'],
    ['bg'=>'#fef9c3','text'=>'#713f12'],['bg'=>'#f3e8ff','text'=>'#6b21a8'],
    ['bg'=>'#fee2e2','text'=>'#991b1b'],
  ];
  $color   = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<a href="{{ route('dosen-industri.mahasiswa.detail', $student) }}" class="student-card">
  <div class="student-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
  <div class="student-info">
    <div class="student-name">{{ $student->user->name }}</div>
    <div class="student-nim">{{ $student->user->username }}</div>
    <div class="student-meta">
      <span>{{ $internship?->position ?? '-' }}</span>
      <span>{{ $student->the_class }}</span>
      @if($internship?->company)
        <span>{{ $internship->company->name }}</span>
      @endif
    </div>
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
  <div style="font-size:40px;margin-bottom:12px;">🏭</div>
  <p>Belum ada mahasiswa yang ditugaskan.</p>
</div>
@endforelse

@endsection
