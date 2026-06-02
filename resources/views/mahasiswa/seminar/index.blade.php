@extends('layouts.mahasiswa')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="page-header"><div class="page-title">Seminar Magang</div></div>
  <div class="header-banner">
    <h2>🎓 Seminar Wajib Magang</h2>
    <p>Daftarkan diri Anda untuk mengikuti seminar magang.</p>
  </div>

  <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:12px;">
    Daftar Seminar ({{ $seminars->count() }})
  </div>

  @forelse($seminars as $seminar)
  <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', $seminar->id) }}'">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">{{ $seminar->program }}</div>
      </div>
      @if(in_array($seminar->id, $registeredIds))
        <span class="badge" style="background:#dcfce7;color:#16a34a;">Terdaftar</span>
      @else
        <span class="badge jadwal">Terjadwal</span>
      @endif
    </div>
    <div class="sem-meta">
      @if($seminar->date)
      <div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>
      @endif
      @if($seminar->time)
      <div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>
      @endif
      @if($seminar->location)
      <div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>
      @endif
    </div>
    <div class="sem-tap">Tap untuk detail »</div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada seminar tersedia.</p>
  </div>
  @endforelse

@endsection
