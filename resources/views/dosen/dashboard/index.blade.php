@extends('layouts.dosen')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.dosen-hero {
  background: var(--warm);
  border-radius: 14px;
  padding: 24px 26px;
  margin-bottom: 20px;
  display: flex; align-items: center; gap: 18px;
}
.hero-av { width:64px;height:64px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;flex-shrink:0; }
.hero-role { font-size:11px;font-weight:700;letter-spacing:.06em;color:var(--primary);text-transform:uppercase; }
.hero-name  { font-size: 26px; font-weight: 800; color: var(--text); letter-spacing: -0.02em; line-height: 1.1; margin-top: 3px; }
.hero-search {
  margin-top: 14px;
  display: flex; align-items: center; gap: 9px;
  background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 11px 14px;
  color: var(--text-muted); max-width: 420px;
}
.hero-search svg { flex-shrink: 0; }
.hero-search input {
  border: none; outline: none; background: transparent;
  font-size: 14px; color: var(--text); width: 100%; font-family: inherit;
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
/* Di layar sempit kartu ini memuat empat hal yang TIGA di antaranya menolak
   menyusut: avatar 46px, dua angka statistik (±110px), dan lencana status
   (±80px). Bersama jarak antar-item dan padding kartu, yang tersisa untuk
   nama, NIM, tiga keterangan, dan penanda tugas tinggal ±65px — semuanya
   terpecah satu kata per baris.

   flex-wrap saja tidak cukup: .student-info memakai flex:1, yang berarti
   flex-basis:0, sehingga ia tak pernah MENUNTUT ruang — ia hanya mengalah
   sampai nol sementara ketiga saudaranya bertahan. Lebar minimumnyalah yang
   mendorong statistik dan lencana turun ke baris kedua. */
@media (max-width: 760px) {
  .student-card { flex-wrap: wrap; }
  .student-info { min-width: 150px; }
}
.student-av {
  width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px;
}
.student-info { flex: 1; min-width: 0; }
.student-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
.student-nim  { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.student-meta { font-size: 11px; color: var(--text-muted); display: flex; gap: 8px; flex-wrap: wrap; }
.student-meta span { background: var(--warm); padding: 2px 8px; border-radius: 20px; }
.student-stats { display: flex; gap: 12px; flex-shrink: 0; text-align: center; }
.student-stat-item { font-size: 11px; color: var(--text-muted); }
.student-stat-item strong { display: block; font-size: 15px; font-weight: 700; color: var(--primary); }
.badge-selesai { background: var(--success-bg); color: var(--success-text); border: 1px solid #A7E8CF; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.badge-aktif   { background: var(--blue-tint); color: var(--primary); border: 1px solid #C7DCFF; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
.badge-dinilai { background: var(--success-bg); color: var(--success-text); border: 1px solid #A7E8CF; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 20px; flex-shrink: 0; }
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
  <x-avatar :user="$user" class="hero-av" />
  <div style="flex:1;">
    <div class="hero-role">Dosen Pembimbing</div>
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
  <form method="GET" action="{{ route('dosen.dashboard') }}" class="filter-form">
    @if(request('search'))
      <input type="hidden" name="search" value="{{ request('search') }}">
    @endif
    <select name="jurusan" class="filter-select" onchange="this.form.submit()">
      <option value="">Semua Jurusan</option>
      @foreach($majors as $major)
        <option value="{{ $major }}" {{ request('jurusan') === $major ? 'selected' : '' }}>{{ $major }}</option>
      @endforeach
    </select>
    <x-periode-select :periode="$periode" :list="$periodeList"/>
  </form>
</div>

{{-- Tab status penilaian --}}
<div class="dz-tabs">
  @php $statusTabs = ['semua' => 'Semua', 'belum' => 'Belum Dinilai', 'dinilai' => 'Sudah Dinilai']; @endphp
  @foreach($statusTabs as $key => $label)
    <a href="{{ route('dosen.dashboard', array_merge(request()->only('search', 'jurusan'), ['status' => $key, 'periode' => $periode])) }}"
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
    ['bg'=>'var(--blue-tint)','text'=>'var(--primary)'],['bg'=>'var(--success-bg)','text'=>'var(--success-text)'],
    ['bg'=>'var(--warn-bg)','text'=>'var(--warn-text)'],['bg'=>'var(--purple-bg)','text'=>'var(--purple-text)'],
    ['bg'=>'var(--danger-bg)','text'=>'var(--danger)'],
  ];
  $color    = $colors[$student->id % count($colors)];

  // Yang menunggu tanggapan dosen dari mahasiswa ini (dihitung dari relasi yang
  // sudah ter-eager-load, jadi tanpa query tambahan per kartu).
  $lbBaru   = $student->logBooks->whereNull('lecturer_note')->count();
  $bimBaru  = $student->guidances->where('status', 'pending')->count();
  $lapBaru  = ($student->report && $student->report->status === 'pending') ? 1 : 0;
  $perluAksi = $lbBaru + $bimBaru + $lapBaru;
@endphp
<a href="{{ route('dosen.mahasiswa.detail', $student) }}" class="student-card">
  <x-avatar :user="$student->user" class="student-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};" />
  <div class="student-info">
    <div class="student-name">
      {{ $student->user->name ?? '-' }}
      @if($perluAksi > 0)
        <span class="dot-baru" title="Ada {{ $perluAksi }} hal yang menunggu tanggapanmu"></span>
      @endif
    </div>
    <div class="student-nim">{{ $student->user->username ?? '-' }}</div>
    <div class="student-meta">
      <span>{{ $student->major }}</span>
      <span>{{ $student->the_class }}</span>
      @if($internship?->company)
        <span>{{ $internship->company->name }}</span>
      @endif
    </div>
    @if($perluAksi > 0)
      <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">
        @if($lbBaru > 0)<span class="tag-baru">{{ $lbBaru }} logbook belum dikomentari</span>@endif
        @if($bimBaru > 0)<span class="tag-baru">{{ $bimBaru }} bimbingan belum di-ACC</span>@endif
        @if($lapBaru > 0)<span class="tag-baru">Laporan menunggu review</span>@endif
      </div>
    @endif
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
    <span class="badge-dinilai" style="display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Dinilai</span>
  @elseif($isFinished)
    <span class="badge-selesai">Selesai</span>
  @else
    <span class="badge-aktif">Aktif</span>
  @endif
</a>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="cap" :size="40"/></div>
  <p>Belum ada mahasiswa bimbingan.</p>
</div>
@endforelse

@endsection
