@extends('layouts.kaprodi')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px;
  padding: 28px 24px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 20px;
}
.student-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(45,62,110,0.80);
}
.student-hero > * { position: relative; z-index: 1; }
.hero-avatar {
  width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
  background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 800; color: #fff;
}
.hero-detail { flex: 1; }
.hero-sname  { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px; }
.hero-nim    { display: inline-block; background: rgba(255,255,255,0.18); color: rgba(255,255,255,0.9); font-size: 12px; padding: 2px 12px; border-radius: 20px; margin-bottom: 6px; }
.hero-email  { font-size: 12px; color: rgba(255,255,255,0.7); }

.nilai-card { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; padding: 18px; }
.nilai-row  { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
.nilai-row:last-child { border-bottom: none; }
.nilai-row.total { border-top: 2px solid var(--border); margin-top: 4px; padding-top: 12px; font-weight: 700; }
.nilai-badge {
  background: #f1f5f9; color: var(--primary); font-weight: 700;
  font-size: 13px; padding: 4px 14px; border-radius: 20px; min-width: 48px; text-align: center;
}
.nilai-badge.empty { background: #f8fafc; color: var(--text-muted); font-weight: 600; }

.btn-finish { background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; width: 100%; }
.btn-finish:hover { background: #15803d; }
.btn-reopen { background: #fff; color: #475569; border: 1.5px solid var(--border); border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; width: 100%; }
.btn-reopen:hover { background: #f8fafc; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('error') }}
  </div>
@endif

{{-- Back Button --}}
<div style="margin-bottom:16px;">
  <a href="{{ route('kaprodi.mahasiswa.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
</div>

{{-- Hero --}}
@php
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="student-hero">
  <div class="hero-avatar">{{ $initials }}</div>
  <div class="hero-detail">
    <div class="hero-sname">{{ $student->user->name }}</div>
    <div class="hero-nim">{{ $student->user->username }}</div>
    <div class="hero-email">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      {{ $student->user->email }}
    </div>
  </div>
</div>

@if(!$internship)

  <div class="card">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Info Mahasiswa</div>
      <span style="background:#fef9c3;color:#92400e;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum Magang</span>
    </div>
    <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
    <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
    <div class="info-row"><div class="info-key">Prodi</div><div class="info-val">{{ $student->study_program }}</div></div>
    <div class="info-row"><div class="info-key">T. Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
  </div>
  <p style="color:var(--text-muted);font-size:13px;margin-top:16px;text-align:center;">
    Mahasiswa ini belum memiliki data magang, sehingga belum ada nilai untuk ditampilkan.
  </p>

  {{-- Catat Magang (item 27): kaprodi mencatat magang di perusahaan yang sudah terdaftar --}}
  <div class="card" style="margin-top:16px;">
    <div class="card-title" style="margin-bottom:6px;">Catat Magang</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;">
      Catat magang mahasiswa di perusahaan yang sudah terdaftar (mahasiswa diterima di luar aplikasi).
      Untuk perusahaan baru yang belum punya akun, gunakan alur
      <strong>Akun Industri</strong> (dari permintaan mahasiswa).
    </p>

    @if($companies->isEmpty())
      <div style="background:#fef9c3;border:1px solid #fde047;color:#854f0b;padding:10px 14px;border-radius:8px;font-size:12.5px;">
        Belum ada perusahaan terdaftar. Buat akun perusahaan terlebih dahulu lewat menu
        <strong>Akun Industri</strong>.
      </div>
    @else
    <form method="POST" action="{{ route('kaprodi.mahasiswa.internship.store', $student) }}">
      @csrf
      <div class="form-group" style="margin-bottom:12px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Perusahaan <span style="color:#dc2626;">*</span></label>
        <select name="company_id" required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih perusahaan —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Pembimbing Industri</label>
        <select name="lecturer_industry_id" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Belum ditentukan —</option>
          @foreach($industriLecturers as $l)
            <option value="{{ $l->id }}">{{ $l->user->name ?? '-' }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group" style="margin-bottom:12px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Posisi / Bidang</label>
        <input type="text" name="position" value="{{ old('position') }}" placeholder="Contoh: Frontend Developer"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Tanggal Mulai <span style="color:#dc2626;">*</span></label>
        <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">+ Catat Magang</button>
    </form>
    @endif
  </div>

@else

<div class="grid-2" style="gap:16px;align-items:start;">

  {{-- LEFT: Info Magang + Aksi --}}
  <div>
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
      <div class="info-row"><div class="info-key">Mulai</div><div class="info-val">{{ $internship->start_date->format('d M Y') }}</div></div>
      <div class="info-row">
        <div class="info-key">Selesai</div>
        <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
      </div>
      <div class="info-row"><div class="info-key">Dosen Pembimbing</div><div class="info-val">{{ $internship->lecturer?->user?->name ?? 'Belum ditugaskan' }}</div></div>
      <div class="info-row"><div class="info-key">Pembimbing Industri</div><div class="info-val">{{ $internship->lecturerIndustry?->user?->name ?? 'Belum ditugaskan' }}</div></div>
      <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px;">
        <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
        <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
        <div class="info-row"><div class="info-key">Prodi</div><div class="info-val">{{ $student->study_program }}</div></div>
        <div class="info-row"><div class="info-key">T. Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
      </div>
    </div>

    {{-- Aksi: Tandai Selesai / Aktif Kembali --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Status Magang</div>
      @if($internship->is_finished)
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
          Magang ini sudah ditandai <strong style="color:#16a34a;">selesai</strong>.
          Jika ditandai aktif kembali, status akan muncul sebagai "Aktif" di seluruh portal.
        </p>
        <form method="POST" action="{{ route('kaprodi.mahasiswa.toggle-finished', $student) }}"
              data-confirm="Tandai magang {{ $student->user->name }} sebagai aktif kembali?">
          @csrf
          <button type="submit" class="btn-reopen">↺ Tandai Aktif Kembali</button>
        </form>
      @else
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
          Tandai magang ini sebagai <strong style="color:var(--text);">selesai</strong> setelah seluruh
          proses bimbingan dan penilaian rampung. Status "Selesai" akan tampil di portal
          mahasiswa, dosen, dan pembimbing industri.
        </p>
        <form method="POST" action="{{ route('kaprodi.mahasiswa.toggle-finished', $student) }}"
              data-confirm="Tandai magang {{ $student->user->name }} sebagai selesai?">
          @csrf
          <button type="submit" class="btn-finish">✓ Tandai Selesai Magang</button>
        </form>
      @endif
    </div>
  </div>

  {{-- RIGHT: Nilai --}}
  <div>
    <div class="nilai-card">
      <div class="card-header" style="margin-bottom:14px;">
        <div class="card-title">Nilai</div>
      </div>
      @foreach($nilai['items'] as $item)
      <div class="nilai-row">
        <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}</div>
        <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? '-' }}</div>
      </div>
      @endforeach
      <div class="nilai-row total">
        <div style="font-size:13px;">Rata - rata</div>
        <div class="nilai-badge" style="background:var(--primary);color:#fff;">
          {{ $nilai['overall'] ?? '-' }}
        </div>
      </div>
    </div>
  </div>

</div>

@endif

@endsection
