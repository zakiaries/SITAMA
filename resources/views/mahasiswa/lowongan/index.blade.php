@extends('layouts.mahasiswa')
@section('title', 'Lowongan Magang')
@php $title = 'Lowongan Magang'; @endphp
@section('content')

<div class="card" style="margin-bottom:16px;">
  <div class="card-title" style="margin-bottom:4px;">Lowongan / Tempat Magang</div>
  <p style="font-size:12.5px;color:var(--text-muted);margin:0 0 14px;">
    Daftar lowongan dari perusahaan yang <strong>berafiliasi dengan Polines</strong>. Untuk mendaftar,
    gunakan menu <a href="{{ route('mahasiswa.ajukan-magang') }}" style="color:var(--primary);">Ajukan Magang</a>.
    Perusahaan di luar daftar ini tetap bisa kamu ajukan sendiri lewat menu tersebut.
  </p>

  <form method="GET" action="{{ route('mahasiswa.lowongan') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari posisi, perusahaan…"
      style="flex:1;min-width:180px;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
    <select name="bidang" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      <option value="">Semua bidang</option>
      @foreach($bidangOptions as $b)
        <option value="{{ $b }}" {{ $bidang === $b ? 'selected' : '' }}>{{ $b }}</option>
      @endforeach
    </select>
    <select name="location" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      <option value="">Semua wilayah</option>
      @foreach($locationOptions as $loc)
        <option value="{{ $loc }}" {{ $location === $loc ? 'selected' : '' }}>{{ $loc }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-primary" style="padding:9px 16px;">Cari</button>
    @if($q !== '' || $bidang !== '' || $location !== '')
      <a href="{{ route('mahasiswa.lowongan') }}" class="btn" style="padding:9px 16px;border:1.5px solid var(--border);">Reset</a>
    @endif
  </form>
</div>

@if($listings->isEmpty())
  <div class="card" style="text-align:center;padding:32px 16px;">
    <div style="font-weight:700;margin-bottom:4px;">Belum ada lowongan</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">
      {{ $q !== '' ? 'Tidak ada lowongan yang cocok dengan pencarianmu.' : 'Belum ada lowongan dari perusahaan afiliasi saat ini.' }}
    </p>
  </div>
@else
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;align-items:stretch;grid-auto-rows:1fr;">
    @foreach($listings as $l)
      <a href="{{ route('mahasiswa.lowongan.detail', $l) }}" class="card"
         style="text-decoration:none;color:inherit;display:flex;flex-direction:column;height:100%;margin:0;padding:16px;">
        {{-- Header: judul (2 baris tetap) + tipe --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
          <div style="font-weight:700;font-size:14.5px;line-height:1.35;min-height:2.7em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $l->title }}</div>
          @if($l->job_type)
            <span style="flex:none;background:var(--primary-tint);color:var(--primary);font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap;">{{ $l->job_type }}</span>
          @endif
        </div>
        {{-- Perusahaan (1 baris) --}}
        <div style="font-size:12.5px;color:var(--text);font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $l->company_display_name }}</div>
        {{-- Meta chips --}}
        @if($l->bidang)
          <div style="margin-top:8px;"><span style="display:inline-block;background:var(--primary-tint);color:var(--primary);font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:999px;">{{ $l->bidang }}</span></div>
        @endif
        <div style="display:flex;flex-wrap:wrap;gap:6px 12px;font-size:11.5px;color:var(--text-muted);margin-top:10px;">
          @if($l->location)<span>📍 {{ $l->location }}</span>@endif
          @if($l->division)<span>🏷️ {{ $l->division }}</span>@endif
        </div>
        {{-- Aksi: selalu menempel di bawah --}}
        <div style="margin-top:auto;padding-top:12px;font-size:12px;color:var(--primary);font-weight:600;">Lihat detail →</div>
      </a>
    @endforeach
  </div>
@endif

@endsection
