@extends('layouts.mahasiswa')
@section('title', 'Log Book')
@php $title = 'Log Book'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="page-header">
    <div class="page-title">Log Book</div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-logbook').classList.add('open')">+ Tambah Log Book</button>
  </div>

  <form method="GET" action="{{ route('mahasiswa.logbook') }}">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Pencarian..." value="{{ request('search') }}">
    </div>
  </form>

  @forelse($logBooks as $lb)
  <div class="exp-item {{ $loop->first ? 'open' : '' }}">
    <div class="exp-header" onclick="toggle(this)">
      <div>
        <div class="exp-title">{{ $lb->title }}</div>
        <div class="exp-date">{{ $lb->date->format('d/m/Y') }}</div>
      </div>
      <div class="chevron">▾</div>
    </div>
    <div class="exp-body">
      <div class="field-label">Aktivitas</div>
      <div class="field-value">{{ $lb->activity }}</div>
      @if($lb->lecturer_note)
      <div class="field-group" style="border-left:3px solid #2563eb;padding-left:10px;margin-top:12px;">
        <div class="field-label" style="color:#2563eb;">Catatan Dosen Pembimbing (Kampus)</div>
        <div class="field-value">{{ $lb->lecturer_note }}</div>
      </div>
      @endif
      @if($lb->industry_note)
      <div class="field-group" style="border-left:3px solid #16a34a;padding-left:10px;margin-top:12px;">
        <div class="field-label" style="color:#16a34a;">Catatan Pembimbing Industri</div>
        <div class="field-value">{{ $lb->industry_note }}</div>
      </div>
      @endif
      <div style="display:flex;gap:8px;margin-top:12px;">
        <form method="POST" action="{{ route('mahasiswa.logbook.destroy', $lb->id) }}" data-confirm="Hapus log book ini?" data-confirm-danger>
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
        </form>
      </div>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada data log book.</p>
  </div>
  @endforelse

@endsection
