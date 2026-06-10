@extends('layouts.mahasiswa')
@section('title', 'Nilai')
@php $title = 'Nilai'; @endphp

@push('styles')
<style>
.nilai-overall {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px;
  padding: 28px 24px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
  text-align: center;
}
.nilai-overall::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(45,62,110,0.80);
}
.nilai-overall > * { position: relative; z-index: 1; }
.nilai-overall-lbl { font-size: 12px; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
.nilai-overall-val { font-size: 40px; font-weight: 800; color: #fff; line-height: 1.1; }
.nilai-overall-sub { font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 6px; }

.nilai-card { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; padding: 18px; }
.nilai-row  { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border); }
.nilai-row:last-child { border-bottom: none; }
.nilai-badge {
  background: #f1f5f9; color: var(--primary); font-weight: 700;
  font-size: 13px; padding: 4px 14px; border-radius: 20px; min-width: 48px; text-align: center;
}
.nilai-badge.empty { background: #f8fafc; color: var(--text-muted); font-weight: 600; }
</style>
@endpush

@section('content')

@if(!$internship)
  <div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
    <div style="font-size:40px;margin-bottom:12px;">📊</div>
    <p style="font-size:14px;margin-bottom:4px;font-weight:600;color:var(--text);">Belum ada data magang.</p>
    <p style="font-size:13px;">Nilai akan tampil di sini setelah Anda memiliki magang aktif.</p>
  </div>
@else

  {{-- Rata-rata keseluruhan --}}
  <div class="nilai-overall">
    <div class="nilai-overall-lbl">Rata-rata Nilai Akhir</div>
    <div class="nilai-overall-val">{{ $nilai['overall'] ?? '-' }}</div>
    <div class="nilai-overall-sub">
      @if($nilai['overall'] === null)
        Belum ada nilai yang diberikan oleh pembimbing.
      @else
        Gabungan penilaian dosen pembimbing kampus & industri
      @endif
    </div>
  </div>

  {{-- Info Magang singkat --}}
  <div class="card" style="margin-bottom:16px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Info Magang</div>
      @if($internship->is_finished)
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Selesai</span>
      @else
        <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
      @endif
    </div>
    <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);font-weight:600;">{{ $internship->company->name ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
    <div class="info-row">
      <div class="info-key">Dosen Pembimbing</div>
      <div class="info-val">{{ $internship->lecturer?->user?->name ?? 'Belum ditugaskan' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Pembimbing Industri</div>
      <div class="info-val">{{ $internship->lecturerIndustry?->user?->name ?? 'Belum ditugaskan' }}</div>
    </div>
  </div>

  {{-- Rincian per komponen --}}
  <div class="page-header">
    <div class="page-title">Rincian Nilai per Komponen</div>
  </div>
  <div class="nilai-card">
    @foreach($nilai['items'] as $item)
    <div class="nilai-row">
      <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}</div>
      <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? 'Belum dinilai' }}</div>
    </div>
    @endforeach
  </div>

@endif

@endsection
