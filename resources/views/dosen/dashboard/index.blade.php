@extends('layouts.dosen')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.dosen-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px;
  padding: 28px 24px 24px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
}
.dosen-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(45,62,110,0.82);
}
.dosen-hero > * { position: relative; z-index: 1; }
.hero-hello { font-size: 12px; color: rgba(255,255,255,0.7); letter-spacing: 1px; font-weight: 600; margin-bottom: 4px; }
.hero-name  { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 16px; line-height: 1.3; }
.hero-search {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.95); border-radius: 10px; padding: 10px 14px;
}
.hero-search svg { flex-shrink: 0; color: #9ca3af; }
.hero-search input {
  border: none; outline: none; background: transparent;
  font-size: 13px; color: #1e2a4a; width: 100%; font-family: inherit;
}
.filter-row { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-select {
  padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 12px; font-family: inherit; color: var(--text); background: #fff;
  outline: none; cursor: pointer; min-width: 140px;
}
.filter-select:focus { border-color: var(--primary); }
.student-card {
  background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
  padding: 14px 16px; margin-bottom: 10px; display: flex; align-items: center;
  gap: 14px; cursor: pointer; transition: all .15s; text-decoration: none;
}
.student-card:hover { border-color: var(--primary); box-shadow: 0 2px 12px rgba(45,62,110,0.08); transform: translateY(-1px); }
.student-av {
  width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px;
}
.student-info { flex: 1; min-width: 0; }
.student-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
.student-nim  { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.student-meta { font-size: 11px; color: var(--text-muted); display: flex; gap: 8px; flex-wrap: wrap; }
.student-meta span { background: #f1f5f9; padding: 2px 8px; border-radius: 20px; }
.student-stats { display: flex; gap: 12px; flex-shrink: 0; text-align: center; }
.student-stat-item { font-size: 11px; color: var(--text-muted); }
.student-stat-item strong { display: block; font-size: 15px; font-weight: 700; color: var(--primary); }
.badge-selesai { background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.badge-aktif   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.badge-dinilai { background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.dz-tabs { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.dz-tab {
  padding: 8px 16px; border: 1.5px solid var(--border); border-radius: 20px;
  font-size: 12px; font-weight: 600; color: var(--text-muted); background: #fff;
  cursor: pointer; text-decoration: none; transition: all .15s;
}
.dz-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
</style>
@endpush

@section('content')

{{-- Hero Header --}}
<div class="dosen-hero">
  <div class="hero-hello">HELLO,</div>
  <div class="hero-name">{{ $user->name }}</div>
  <form method="GET" action="{{ route('dosen.dashboard') }}">
    <div class="hero-search">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <input name="search" placeholder="Pencarian nama atau jurusan..." value="{{ request('search') }}">
    </div>
  </form>
</div>

{{-- Section header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
  <div class="card-title" style="margin:0;">
    Mahasiswa Bimbingan
    <span style="background:var(--primary);color:#fff;font-size:11px;padding:2px 9px;border-radius:20px;margin-left:8px;font-weight:600;">
      {{ $students->count() }}
    </span>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('dosen.dashboard') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
    @if(request('search'))
      <input type="hidden" name="search" value="{{ request('search') }}">
    @endif
    <select name="jurusan" class="filter-select" onchange="this.form.submit()">
      <option value="">Semua Jurusan</option>
      @foreach($majors as $major)
        <option value="{{ $major }}" {{ request('jurusan') === $major ? 'selected' : '' }}>{{ $major }}</option>
      @endforeach
    </select>
    <select name="tahun" class="filter-select" onchange="this.form.submit()">
      <option value="">Semua Tahun</option>
      @foreach($years as $year)
        <option value="{{ $year }}" {{ request('tahun') === $year ? 'selected' : '' }}>{{ $year }}</option>
      @endforeach
    </select>
  </form>
</div>

{{-- Tab status penilaian --}}
<div class="dz-tabs">
  @php $statusTabs = ['semua' => 'Semua', 'belum' => 'Belum Dinilai', 'dinilai' => 'Sudah Dinilai']; @endphp
  @foreach($statusTabs as $key => $label)
    <a href="{{ route('dosen.dashboard', array_merge(request()->only('search', 'jurusan', 'tahun'), ['status' => $key])) }}"
       class="dz-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] }})</a>
  @endforeach
</div>

{{-- Student List --}}
@forelse($students as $student)
@php
  $internship = $student->internships->first();
  $isFinished = $internship?->is_finished ?? false;
  $graded     = ($internship?->scores_count ?? 0) > 0;
  $colors = [
    ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
    ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#eeedfe','text'=>'#3c3489'],
    ['bg'=>'#fcebeb','text'=>'#791f1f'],
  ];
  $color    = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<a href="{{ route('dosen.mahasiswa.detail', $student) }}" class="student-card">
  <div class="student-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">
    {{ $initials }}
  </div>
  <div class="student-info">
    <div class="student-name">{{ $student->user->name ?? '-' }}</div>
    <div class="student-nim">{{ $student->user->username ?? '-' }}</div>
    <div class="student-meta">
      <span>{{ $student->major }}</span>
      <span>{{ $student->the_class }}</span>
      @if($internship?->company)
        <span>{{ $internship->company->name }}</span>
      @endif
    </div>
  </div>
  <div class="student-stats">
    <div class="student-stat-item">
      <strong>{{ $student->guidances->count() }}</strong>
      Bimbingan
    </div>
    <div class="student-stat-item">
      <strong>{{ $student->logBooks->count() }}</strong>
      Log
    </div>
  </div>
  @if($graded)
    <span class="badge-dinilai">✓ Dinilai</span>
  @elseif($isFinished)
    <span class="badge-selesai">Selesai</span>
  @else
    <span class="badge-aktif">Aktif</span>
  @endif
</a>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="font-size:40px;margin-bottom:12px;">👨‍🎓</div>
  <p>Belum ada mahasiswa bimbingan.</p>
</div>
@endforelse

@endsection
