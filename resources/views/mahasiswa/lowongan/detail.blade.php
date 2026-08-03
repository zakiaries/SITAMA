@extends('layouts.mahasiswa')
@section('title', $jobListing->title)
@php $title = 'Detail Lowongan'; @endphp
@section('content')

<a href="{{ route('mahasiswa.lowongan') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:14px;">← Kembali ke daftar lowongan</a>

<div class="card" style="margin-bottom:16px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
    <div>
      <div class="card-title" style="margin-bottom:2px;">{{ $jobListing->title }}</div>
      <div style="font-size:13px;color:var(--text);font-weight:600;">{{ $jobListing->company_display_name }}</div>
    </div>
    <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;white-space:nowrap;">Afiliasi Polines</span>
  </div>

  <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;">
    @php
      $chips = array_filter([
        $jobListing->division ? 'Divisi: ' . $jobListing->division : null,
        $jobListing->location ? 'Lokasi: ' . $jobListing->location : null,
        $jobListing->job_type ? 'Tipe: ' . $jobListing->job_type : null,
      ]);
    @endphp
    @foreach($chips as $chip)
      <span style="background:var(--bg);border:1px solid var(--border);font-size:12px;padding:5px 11px;border-radius:8px;color:var(--text);">{{ $chip }}</span>
    @endforeach
    {{-- Kuota penanda saja — pengajuan tetap dibuka meski penuh. --}}
    @if($jobListing->punyaKuota())
      <span style="background:{{ $jobListing->penuh() ? 'var(--warn-bg)' : 'var(--bg)' }};border:1px solid {{ $jobListing->penuh() ? '#F0DCA4' : 'var(--border)' }};font-size:12px;padding:5px 11px;border-radius:8px;color:{{ $jobListing->penuh() ? 'var(--warn-text)' : 'var(--text)' }};">
        Kuota: {{ $jobListing->ringkasanKuota() }}{{ $jobListing->penuh() ? ' · Penuh' : '' }}
      </span>
    @endif
  </div>

  @if($jobListing->punyaKuota())
    <p style="font-size:12px;color:var(--text-muted);margin:12px 0 0;">
      {{ $jobListing->penjelasanKuota() }}
      @if($jobListing->penuh())
        Karena sudah terisi, sebaiknya pastikan dulu ke perusahaannya bahwa masih ada tempat.
      @endif
    </p>
  @endif
</div>

@if($jobListing->description)
<div class="card" style="margin-bottom:16px;">
  <div style="font-size:12px;font-weight:700;color:var(--text-muted);letter-spacing:.3px;margin-bottom:8px;">DESKRIPSI</div>
  <div style="font-size:13.5px;line-height:1.6;white-space:pre-line;">{{ $jobListing->description }}</div>
</div>
@endif

@if(!empty($jobListing->skills))
<div class="card" style="margin-bottom:16px;">
  <div style="font-size:12px;font-weight:700;color:var(--text-muted);letter-spacing:.3px;margin-bottom:8px;">KEAHLIAN / SKILL</div>
  <div style="display:flex;flex-wrap:wrap;gap:6px;">
    @foreach($jobListing->skills as $skill)
      <span style="background:var(--primary-tint);color:var(--primary);font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px;">{{ $skill }}</span>
    @endforeach
  </div>
</div>
@endif

<div class="card" style="border-left:4px solid var(--primary);">
  <div style="font-weight:700;margin-bottom:4px;">Tertarik magang di sini?</div>
  <p style="font-size:13px;color:var(--text-muted);margin:0 0 12px;">
    Klik tombol di bawah untuk mengajukan — <strong>perusahaan &amp; posisi otomatis terisi</strong>. Kamu tinggal melengkapi
    data pembimbing industri &amp; bukti penerimaan, lalu tunggu review Kaprodi.
  </p>
  <a href="{{ route('mahasiswa.ajukan-magang', ['company_id' => $jobListing->company_id, 'position' => $jobListing->title]) }}"
     style="display:inline-block;background:var(--primary);color:#fff;font-size:13px;font-weight:700;padding:10px 18px;border-radius:8px;text-decoration:none;">
    Ajukan Magang di Perusahaan Ini
  </a>
</div>

@endsection
