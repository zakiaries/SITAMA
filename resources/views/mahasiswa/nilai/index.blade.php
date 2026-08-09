@extends('layouts.mahasiswa')
@section('title', 'Nilai')
@php $title = 'Nilai'; @endphp

@push('styles')
<style>
.nilai-overall {
  background: var(--primary);
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
  background: var(--warm); color: var(--primary); font-weight: 700;
  font-size: 13px; padding: 4px 14px; border-radius: 20px; min-width: 48px; text-align: center;
}
.nilai-badge.empty { background: var(--warm); color: var(--text-muted); font-weight: 600; }
</style>
@endpush

@section('content')

@if(!$internship)
  <div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
    <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="chart" :size="40"/></div>
    <p style="font-size:14px;margin-bottom:4px;font-weight:600;color:var(--text);">Belum ada data magang.</p>
    <p style="font-size:13px;">Nilai akan tampil di sini setelah Anda memiliki magang aktif.</p>
  </div>
@else

  {{-- Nilai Akhir = rata dosen + rata industri (dijumlah) --}}
  <div class="nilai-overall">
    <div class="nilai-overall-lbl">Nilai Akhir (Dosen + Industri)</div>
    <div class="nilai-overall-val">{{ $nilai['final'] ?? '-' }}</div>
    <div class="nilai-overall-sub">
      @if($nilai['final'] === null)
        Menunggu penilaian lengkap dari dosen pembimbing & pembimbing industri.
      @else
        Rata dosen {{ $nilai['lecturer']['average'] }} + Rata industri {{ $nilai['industry']['average'] }} &nbsp;(skala 1–10)
      @endif
    </div>
  </div>

  {{-- Dua lembar terpisah, mengikuti form resminya: masing-masing ditandatangani
       orang yang berbeda. Tiap tombol muncul begitu penilainya sendiri selesai,
       jadi tanda tangan dosen tak perlu menunggu pihak perusahaan. --}}
  @if($nilai['lecturer']['average'] !== null || $nilai['industry']['average'] !== null)
    <div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;">
      @if($nilai['lecturer']['average'] !== null)
        <a href="{{ route('mahasiswa.nilai.pdf', 'dosen') }}" class="btn btn-outline btn-sm">
          <x-icon name="download" :size="13"/> Lembar Nilai Dosen Pembimbing
        </a>
      @endif
      @if($nilai['industry']['average'] !== null)
        <a href="{{ route('mahasiswa.nilai.pdf', 'industri') }}" class="btn btn-outline btn-sm">
          <x-icon name="download" :size="13"/> Lembar Nilai Pembimbing Industri
        </a>
      @endif
    </div>
    <div style="font-size:11.5px;color:var(--text-muted);margin-top:-8px;margin-bottom:16px;">
      Cetak lalu mintakan tanda tangan ke masing-masing penilai.
    </div>
  @endif

  {{-- Info Magang singkat --}}
  <div class="card" style="margin-bottom:16px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Info Magang</div>
      @if($internship->is_finished)
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Selesai</span>
      @else
        <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
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

  {{-- Penilaian Dosen Pembimbing (Proposal 20% + Laporan 80%) --}}
  <div class="page-header">
    <div class="page-title">Penilaian Dosen Pembimbing</div>
    <span class="nilai-badge {{ $nilai['lecturer']['average'] === null ? 'empty' : '' }}">{{ $nilai['lecturer']['average'] ?? 'Belum dinilai' }}</span>
  </div>
  <div class="nilai-card" style="margin-bottom:16px;">
    @foreach($nilai['lecturer']['components'] as $item)
    <div class="nilai-row">
      <div style="font-size:13px;color:var(--text);">
        {{ $item['name'] }}@if($item['weight']) <span style="color:var(--text-muted);font-size:11px;">(bobot {{ intval($item['weight']) }}%)</span>@endif
      </div>
      <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? 'Belum dinilai' }}</div>
    </div>
    <x-nilai-rincian :komponen="$item" />
    @endforeach
  </div>

  {{-- Penilaian Pembimbing Industri (rata 8 komponen) --}}
  <div class="page-header">
    <div class="page-title">Penilaian Pembimbing Industri</div>
    <span class="nilai-badge {{ $nilai['industry']['average'] === null ? 'empty' : '' }}">{{ $nilai['industry']['average'] ?? 'Belum dinilai' }}</span>
  </div>
  <div class="nilai-card">
    @foreach($nilai['industry']['components'] as $item)
    <div class="nilai-row">
      <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}</div>
      <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? 'Belum dinilai' }}</div>
    </div>
    @endforeach
  </div>

@endif

@endsection
