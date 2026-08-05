@extends('layouts.kaprodi')
@section('title', 'Periode Magang')
@php $title = 'Periode Magang'; @endphp

@push('styles')
<style>
.per-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:16px 18px;margin-bottom:10px;
  display:flex;align-items:center;gap:18px;flex-wrap:wrap;
}
.per-card.is-active { border-color:var(--primary);border-left:4px solid var(--primary); }
.per-main { flex:1;min-width:220px; }
.per-label { font-size:15px;font-weight:700;color:var(--text); }
.per-window { font-size:12px;color:var(--text-muted);margin-top:2px; }
.per-prodi { display:flex;gap:14px;flex-wrap:wrap;align-items:center; }
.per-prodi label { font-size:13px;color:var(--text);display:flex;align-items:center;gap:6px;cursor:pointer; }
.per-actions { display:flex;gap:8px;align-items:center;flex-shrink:0; }
.per-badge {
  font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;
  background:var(--blue-tint);color:var(--primary);
}
</style>
@endpush

@section('content')

<x-form-errors/>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

<div class="info-note" style="background:var(--blue-tint);border:1px solid #C7DCFF;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12.5px;color:var(--primary);">
  Periode mengikuti kalender akademik dan muncul sendiri — Gasal berjalan Agustus–Januari,
  Genap Februari–Juli. Yang perlu Anda tentukan hanya <strong>prodi mana yang magang</strong>
  pada tiap periode, lalu <strong>aktifkan</strong> yang sedang berjalan. Mahasiswa yang mendaftar
  akan masuk ke periode aktif itu.
</div>

@foreach($periods as $p)
<div class="per-card {{ $p->is_active ? 'is-active' : '' }}">
  <div class="per-main">
    <div class="per-label">{{ $p->label }}</div>
    <div class="per-window">
      {{ $p->start_date?->format('d M Y') ?? '-' }} – {{ $p->end_date?->format('d M Y') ?? '-' }}
      · {{ $p->duration_months }} bulan
      · {{ $p->students_count }} mahasiswa
    </div>
  </div>

  <form method="POST" action="{{ route('kaprodi.periode.prodi', $p) }}" class="per-prodi">
    @csrf
    @foreach($prodi as $nama)
      <label>
        <input type="checkbox" name="study_programs[]" value="{{ $nama }}"
               {{ in_array($nama, $p->study_programs ?? [], true) ? 'checked' : '' }}>
        {{ $nama }}
      </label>
    @endforeach
    <button type="submit" class="btn btn-outline btn-sm">Simpan</button>
  </form>

  <div class="per-actions">
    @if($p->is_active)
      <span class="per-badge">Berjalan</span>
    @else
      <form method="POST" action="{{ route('kaprodi.periode.aktifkan', $p) }}"
        data-confirm="Jadikan {{ $p->label }} periode yang berjalan? Mahasiswa yang mendaftar setelah ini akan masuk ke periode tersebut.">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">Aktifkan</button>
      </form>
    @endif
  </div>
</div>
@endforeach

@endsection
