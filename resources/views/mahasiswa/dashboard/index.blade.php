@extends('layouts.mahasiswa')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp
@section('content')

  {{-- Greeting --}}
  <div class="greeting-sub">Selamat datang kembali</div>
  <div class="greeting-name">Halo, {{ $user->name }}</div>

  {{-- Notifikasi pindah ke panel melayang di lonceng (x-notif-bell): kartunya
       dulu menumpuk di sini, mendorong turun statistik, tak bisa diklik maupun
       ditutup, dan tak terlihat dari halaman lain. --}}

  {{-- Stats --}}
  <div class="grid-4">
    <div class="stat-card">
      <div class="stat-label">Log Book</div>
      <div class="stat-value">{{ $logBooksCount }}</div>
      <div class="stat-sub">Total entri</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Bimbingan</div>
      <div class="stat-value">{{ $guidancesDone }}</div>
      <div class="stat-sub">Sesi disetujui</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Seminar</div>
      <div class="stat-value">{{ $seminarsCount }}</div>
      <div class="stat-sub">Terjadwal</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Hari Magang</div>
      <div class="stat-value">{{ $daysInternship }}</div>
      <div class="stat-sub">Sejak mulai</div>
    </div>
  </div>

  <div class="grid-2">
    {{-- Bimbingan Terbaru --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Bimbingan Terbaru</div>
        <a href="{{ route('mahasiswa.bimbingan') }}" class="see-all">Lihat semua &rsaquo;</a>
      </div>
      @forelse($latestGuidances as $g)
      <div class="exp-item {{ $loop->first ? 'open' : '' }}">
        <div class="exp-header" onclick="toggle(this)">
          <div class="status-dot {{ $g->status === 'approved' ? 'done' : 'pending' }}">
            @if($g->status === 'approved')<x-icon name="check" :size="13"/>@else—@endif
          </div>
          <div>
            <div class="exp-title">{{ $g->title }}</div>
            <div class="exp-date">{{ $g->date->format('d/m/Y') }}</div>
          </div>
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
        </div>
      </div>
      @empty
      <p style="color:var(--text-muted);font-size:13px;padding:12px 0;">Belum ada data bimbingan.</p>
      @endforelse
    </div>

    {{-- Log Book Terbaru --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Log Book Terbaru</div>
        <a href="{{ route('mahasiswa.logbook') }}" class="see-all">Lihat semua &rsaquo;</a>
      </div>
      @forelse($latestLogBooks as $lb)
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
        </div>
      </div>
      @empty
      <p style="color:var(--text-muted);font-size:13px;padding:12px 0;">Belum ada data log book.</p>
      @endforelse
    </div>
  </div>

  {{-- Info Magang --}}
  @if($internship)
  <div class="card" style="margin-top:0;">
    <div class="card-title" style="margin-bottom:14px;">Info Magang Aktif</div>
    <div class="info-row">
      <div class="info-key">Perusahaan</div>
      <div class="info-val" style="color:var(--primary);">{{ $internship->company->name ?? '-' }} — {{ $internship->position }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Dosen Pembimbing</div>
      <div class="info-val">{{ optional(optional($student->lecturer)->user)->name ?? 'Belum ditugaskan' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Mulai</div>
      <div class="info-val">{{ $internship->start_date->format('d M Y') }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Selesai</div>
      <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
    </div>
  </div>
  @endif

@endsection
