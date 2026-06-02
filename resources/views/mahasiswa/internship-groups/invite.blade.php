@extends('layouts.mahasiswa')
@section('title', 'Undang Anggota Kelompok')
@php $title = 'Undang Anggota'; @endphp
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

  {{-- Header --}}
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="{{ route('mahasiswa.magang-saya') }}" class="btn btn-outline btn-sm">← Lewati</a>
    <div class="page-title">Undang Anggota Kelompok</div>
  </div>

  {{-- Info Kelompok --}}
  <div class="card" style="margin-bottom:16px;border-left:4px solid #7c3aed;">
    <div class="card-title" style="margin-bottom:12px;color:#7c3aed;">👥 Kelompok Anda</div>
    <div class="info-row">
      <div class="info-key">Posisi</div>
      <div class="info-val" style="font-weight:600;">{{ $internshipGroup->leaderApplication->jobListing->title }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Perusahaan</div>
      <div class="info-val">{{ $internshipGroup->leaderApplication->jobListing->company->name }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Role Anda</div>
      <div class="info-val">
        <span style="background:#f3e8ff;color:#7c3aed;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">👑 Ketua Kelompok</span>
      </div>
    </div>
  </div>

  <div class="grid-2">

    {{-- Form Undang --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">Undang Anggota via Username</div>
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;">
        Masukkan <strong>username</strong> mahasiswa yang ingin diundang ke kelompok Anda.
      </p>
      <form method="POST" action="{{ route('mahasiswa.internship-groups.invite.store', $internshipGroup) }}">
        @csrf
        <div class="form-group">
          <label>Username / NIM Mahasiswa</label>
          <input type="text" name="username" placeholder="Contoh: mahasiswa2" required value="{{ old('username') }}"
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;outline:none;">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          + Undang
        </button>
      </form>
    </div>

    {{-- Daftar Anggota --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">
        Anggota Diundang
        <span style="background:#f3e8ff;color:#7c3aed;padding:2px 8px;border-radius:20px;font-size:11px;margin-left:6px;">
          {{ $internshipGroup->members->count() }} orang
        </span>
      </div>

      @forelse($internshipGroup->members as $member)
      @php
        $mName    = $member->student->user->name ?? '-';
        $initials = collect(explode(' ', $mName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
        $statusColor = match($member->status) {
          'accepted' => ['bg' => '#dcfce7', 'text' => '#16a34a', 'label' => '✓ Diterima'],
          'rejected' => ['bg' => '#fee2e2', 'text' => '#dc2626', 'label' => '✕ Ditolak'],
          default    => ['bg' => '#fef9c3', 'text' => '#854f0b', 'label' => '⏳ Menunggu'],
        };
      @endphp
      <div class="audience-row" style="margin-bottom:10px;">
        <div class="avatar" style="width:34px;height:34px;font-size:12px;background:var(--primary-light);color:var(--primary-text);flex-shrink:0;">
          {{ $initials }}
        </div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;">{{ $mName }}</div>
          <span style="font-size:11px;padding:1px 8px;border-radius:20px;background:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};">
            {{ $statusColor['label'] }}
          </span>
        </div>
      </div>
      @empty
      <div style="text-align:center;padding:24px 0;color:var(--text-muted);">
        <div style="font-size:28px;margin-bottom:6px;">👤</div>
        <p style="font-size:13px;">Belum ada anggota. Undang teman Anda!</p>
      </div>
      @endforelse
    </div>

  </div>

  {{-- Tombol Selesai --}}
  <div style="margin-top:20px;display:flex;justify-content:flex-end;">
    <a href="{{ route('mahasiswa.magang-saya') }}" class="btn btn-primary" style="padding:11px 28px;">
      Selesai →
    </a>
  </div>

@endsection
