@extends('layouts.industri')
@section('title', 'Review Pelamar')
@php $title = 'Review Pelamar'; @endphp

@push('styles')
<style>
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab { padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;cursor:pointer;text-decoration:none;transition:all .15s; }
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

.warn-banner { background:#fff8ed;border:1px solid #fcd34d;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#92400e; }

.pel-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px; }
.pel-head { display:flex;align-items:center;gap:14px; }
.pel-av { width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px; }
.pel-info { flex:1;min-width:0; }
.pel-name { font-size:14px;font-weight:700;color:var(--text); }
.pel-meta { font-size:12px;color:var(--text-muted);margin-top:2px; }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-pending  { background:#fef9c3;color:#92400e; }
.st-accepted { background:#dcfce7;color:#16a34a; }
.st-rejected { background:#fee2e2;color:#dc2626; }
.pel-job { margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted); }
.pel-job strong { color:var(--text); }
.pel-actions { display:flex;gap:8px;margin-top:14px; }
.btn-accept { background:#16a34a;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-reject { background:#fff;color:#dc2626;border:1.5px solid #dc2626;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-accept:hover { background:#15803d; }
.btn-reject:hover { background:#fef2f2; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('industri.pelamar.index') }}">
  <input type="hidden" name="status" value="{{ $status }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari nama atau NIM pelamar..." value="{{ request('search') }}">
  </div>
</form>

<div class="filter-tabs">
  @php $tabs = ['pending'=>'Menunggu','accepted'=>'Diterima','rejected'=>'Ditolak']; @endphp
  @foreach($tabs as $key => $label)
  <a href="{{ route('industri.pelamar.index', ['status' => $key, 'search' => request('search')]) }}"
     class="filter-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] }})</a>
  @endforeach
</div>

@if($status === 'pending' && $counts['pending'] > 0)
  <div class="warn-banner">⏳ Ada <strong>{{ $counts['pending'] }} pelamar</strong> menunggu keputusan Anda.</div>
@endif

@forelse($pelamars as $pelamar)
@php
  $student = $pelamar->student;
  $colors = [
    ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
    ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#eeedfe','text'=>'#3c3489'],
  ];
  $color    = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="pel-card">
  <div class="pel-head">
    <div class="pel-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
    <div class="pel-info">
      <div class="pel-name">{{ $student->user->name ?? '-' }}</div>
      <div class="pel-meta">{{ $student->user->username ?? '-' }} · {{ $student->the_class }} · {{ $student->major }}</div>
    </div>
    @php
      $stClass = match($pelamar->status) { 'accepted'=>'st-accepted','rejected'=>'st-rejected',default=>'st-pending' };
      $stLabel = match($pelamar->status) { 'accepted'=>'✓ Diterima','rejected'=>'✕ Ditolak',default=>'⏳ Menunggu' };
    @endphp
    <span class="st-badge {{ $stClass }}">{{ $stLabel }}</span>
  </div>

  <div class="pel-job">
    Melamar: <strong>{{ $pelamar->jobListing->title ?? '-' }}</strong> · {{ $pelamar->created_at->format('d M Y') }}
  </div>

  @if($pelamar->status === 'pending')
  <div class="pel-actions">
    <form method="POST" action="{{ route('industri.pelamar.accept', $pelamar) }}" onsubmit="return confirm('Terima pelamar ini?')">
      @csrf
      <button type="submit" class="btn-accept">✓ Terima</button>
    </form>
    <form method="POST" action="{{ route('industri.pelamar.reject', $pelamar) }}" onsubmit="return confirm('Tolak pelamar ini?')">
      @csrf
      <button type="submit" class="btn-reject">✕ Tolak</button>
    </form>
  </div>
  @endif
</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="font-size:40px;margin-bottom:12px;">👥</div>
  <p>Tidak ada pelamar pada kategori ini.</p>
</div>
@endforelse

@endsection
