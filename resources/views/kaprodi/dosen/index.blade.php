@extends('layouts.kaprodi')
@section('title', 'Data Dosen')
@php $title = 'Data Dosen'; @endphp

@push('styles')
<style>
.dosen-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
  text-decoration:none;transition:all .15s;
}
.dosen-card:hover { border-color:var(--primary);box-shadow:0 2px 12px rgba(45,62,110,0.08);transform:translateY(-1px); }
.dosen-av   { width:48px;height:48px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px; }
.dosen-info { flex:1;min-width:0; }
.dosen-name { font-size:14px;font-weight:700;color:var(--text); }
.dosen-user { font-size:12px;color:var(--text-muted);margin-bottom:8px; }
.cap-bar-label { display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.cap-bar-track { height:6px;background:#e5e7eb;border-radius:4px;overflow:hidden; }
.cap-bar-fill  { height:100%;background:var(--primary);border-radius:4px; }
.dosen-count { width:50px;height:50px;border-radius:12px;background:var(--primary-light);color:var(--primary-text);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0; }
.dosen-count .n { font-size:20px;font-weight:800;line-height:1; }
.dosen-count .l { font-size:9px;text-transform:uppercase; }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('kaprodi.dosen.index') }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari nama dosen..." value="{{ request('search') }}">
  </div>
</form>

<div style="font-size:12px;color:var(--text-muted);margin-bottom:13px;">
  Total <strong style="color:var(--text);">{{ $lecturers->count() }} dosen</strong>
</div>

@forelse($lecturers as $lec)
@php
  $count = $lec->students_count ?? 0;
  $max   = 20;
  $pct   = min(round($count / $max * 100), 100);
  $colors = [
    ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
    ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#eeedfe','text'=>'#3c3489'],
  ];
  $color    = $colors[$lec->id % count($colors)];
  $initials = collect(explode(' ', $lec->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<a href="{{ route('kaprodi.dosen.detail', $lec) }}" class="dosen-card">
  <div class="dosen-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
  <div class="dosen-info">
    <div class="dosen-name">{{ $lec->user->name ?? '-' }}</div>
    <div class="dosen-user">{{ $lec->user->username ?? '-' }}</div>
    <div class="cap-bar-label"><span>Mahasiswa bimbingan</span><span>{{ $count }}/{{ $max }}</span></div>
    <div class="cap-bar-track"><div class="cap-bar-fill" style="width:{{ $pct }}%;"></div></div>
  </div>
  <div class="dosen-count">
    <div class="n">{{ $count }}</div>
    <div class="l">Mhs</div>
  </div>
</a>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Tidak ada dosen ditemukan.</p></div>
@endforelse

@endsection
