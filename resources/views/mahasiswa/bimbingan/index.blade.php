@extends('layouts.mahasiswa')
@section('title', 'Bimbingan')
@php $title = 'Bimbingan'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="page-header">
    <div class="page-title">Daftar Bimbingan</div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-bimb').classList.add('open')">+ Tambah Bimbingan</button>
  </div>

  <form method="GET" action="{{ route('mahasiswa.bimbingan') }}">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Pencarian..." value="{{ request('search') }}">
    </div>
  </form>

  @forelse($guidances as $g)
  <div class="exp-item {{ $loop->first ? 'open' : '' }}">
    <div class="exp-header" onclick="toggle(this)">
      <div class="status-dot {{ $g->status === 'approved' ? 'done' : ($g->status === 'rejected' ? 'rejected' : 'pending') }}">
        @if($g->status === 'approved') ✓
        @elseif($g->status === 'rejected') ✕
        @else —
        @endif
      </div>
      <div>
        <div class="exp-title">{{ $g->title }}</div>
        <div class="exp-date">{{ $g->date->format('d/m/Y') }}</div>
      </div>
      <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:{{ $g->status==='approved'?'#dcfce7':($g->status==='rejected'?'#fee2e2':'#fef9c3') }};color:{{ $g->status==='approved'?'#16a34a':($g->status==='rejected'?'#dc2626':'#854f0b') }};">
        {{ ucfirst($g->status) }}
      </span>
      <div class="chevron">▾</div>
    </div>
    <div class="exp-body">
      <div class="field-label">Aktivitas</div>
      <div class="field-value">{{ $g->activity }}</div>
      @if($g->lecturer_note)
      <div class="field-group">
        <div class="field-label">Catatan Dosen</div>
        <div class="field-value">{{ $g->lecturer_note }}</div>
      </div>
      @endif
      @if($g->name_file)
      <div class="file-badge">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <a href="{{ Storage::url($g->name_file) }}" target="_blank">File Bimbingan</a>
      </div>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada data bimbingan.</p>
  </div>
  @endforelse

@endsection
