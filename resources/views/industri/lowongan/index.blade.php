@extends('layouts.industri')
@section('title', 'Kelola Lowongan')
@php $title = 'Kelola Lowongan'; @endphp

@push('styles')
<style>
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab { padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;cursor:pointer;text-decoration:none;transition:all .15s; }
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

.low-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px; }
.low-head { display:flex;justify-content:space-between;align-items:flex-start;gap:10px; }
.low-title { font-size:15px;font-weight:700;color:var(--text); }
.low-meta { font-size:12px;color:var(--text-muted);margin-top:2px; }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-active { background:#dcfce7;color:#16a34a; }
.st-closed { background:#f1f5f9;color:#64748b; }
.low-tags { display:flex;gap:6px;flex-wrap:wrap;margin-top:10px; }
.low-tag { background:#f1f5f9;color:var(--text);font-size:11px;padding:2px 9px;border-radius:6px; }
.low-foot { display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:1px solid var(--border); }
.low-applicants { font-size:12px;color:var(--text-muted); }
.low-applicants strong { color:var(--primary); }
.low-actions { display:flex;gap:8px; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

<div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
  <a href="{{ route('industri.lowongan.create') }}" class="btn btn-primary btn-sm">+ Buat Lowongan</a>
</div>

<form method="GET" action="{{ route('industri.lowongan.index') }}">
  <input type="hidden" name="status" value="{{ $status }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari judul lowongan..." value="{{ request('search') }}">
  </div>
</form>

<div class="filter-tabs">
  @php $tabs = ['all'=>'Semua','active'=>'Aktif','closed'=>'Ditutup']; @endphp
  @foreach($tabs as $key => $label)
  <a href="{{ route('industri.lowongan.index', ['status' => $key, 'search' => request('search')]) }}"
     class="filter-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] }})</a>
  @endforeach
</div>

@forelse($lowongans as $job)
<div class="low-card">
  <div class="low-head">
    <div>
      <div class="low-title">{{ $job->title }}</div>
      <div class="low-meta">{{ $job->division ?? '-' }} · {{ $job->job_type }} · {{ $job->duration_months }} bulan · Kuota {{ $job->quota }}</div>
    </div>
    <span class="st-badge {{ $job->status === 'active' ? 'st-active' : 'st-closed' }}">
      {{ $job->status === 'active' ? 'Aktif' : 'Ditutup' }}
    </span>
  </div>

  @if(!empty($job->skills))
  <div class="low-tags">
    @foreach($job->skills as $skill)<span class="low-tag">{{ $skill }}</span>@endforeach
  </div>
  @endif

  <div class="low-foot">
    <div class="low-applicants">
      👥 <strong>{{ $job->applications_count }}</strong> pelamar · Diposting {{ $job->created_at->format('d M Y') }}
    </div>
    <div class="low-actions">
      <a href="{{ route('industri.lowongan.edit', $job) }}" class="btn btn-outline btn-sm">✏ Edit</a>
      <form method="POST" action="{{ route('industri.lowongan.toggle', $job) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-outline btn-sm">{{ $job->status === 'active' ? '🔒 Tutup' : '🔓 Buka' }}</button>
      </form>
      <form method="POST" action="{{ route('industri.lowongan.destroy', $job) }}" onsubmit="return confirm('Hapus lowongan ini?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;">🗑</button>
      </form>
    </div>
  </div>
</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="font-size:40px;margin-bottom:12px;">📋</div>
  <p style="margin-bottom:16px;">Belum ada lowongan pada kategori ini.</p>
  <a href="{{ route('industri.lowongan.create') }}" class="btn btn-primary">+ Buat Lowongan Pertama</a>
</div>
@endforelse

@endsection
