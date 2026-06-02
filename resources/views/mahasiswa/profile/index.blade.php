@extends('layouts.mahasiswa')
@section('title', 'Profile')
@php $title = 'Profile'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  @php
    $initials = collect(explode(' ', $user->name))->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
  @endphp

  <div class="profile-hero">
    <div class="profile-av">{{ $initials }}</div>
    <div>
      <div class="profile-name">{{ $user->name }}</div>
      <div class="profile-role">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
      <div class="profile-email">{{ $user->email }}</div>
    </div>
    <button class="btn" onclick="document.getElementById('modal-edit-profile').classList.add('open')"
      style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.25);">
      ✏ Edit Profil
    </button>
  </div>

  <div class="grid-2">
    {{-- Info Magang --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">Informasi Magang</div>
      @if($internship)
        <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);">{{ $internship->company->name ?? '-' }}</div></div>
        <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
        <div class="info-row"><div class="info-key">Tanggal Mulai</div><div class="info-val">{{ $internship->start_date->format('d M Y') }}</div></div>
        <div class="info-row">
          <div class="info-key">Tanggal Selesai</div>
          <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
        </div>
      @else
        <p style="color:var(--text-muted);font-size:13px;">Belum ada data magang aktif.</p>
      @endif

      @if($student)
      <div style="border-top:1px solid var(--border);margin-top:14px;padding-top:14px;">
        <div class="card-title" style="margin-bottom:10px;">Data Mahasiswa</div>
        <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
        <div class="info-row"><div class="info-key">Program Studi</div><div class="info-val">{{ $student->study_program }}</div></div>
        <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
        <div class="info-row"><div class="info-key">Tahun Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
      </div>
      @endif
    </div>

    {{-- Pengaturan --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">Pengaturan Akun</div>
      <div class="setting-row" onclick="document.getElementById('modal-edit-profile').classList.add('open')" style="cursor:pointer;">
        <span>✏ Edit Profil</span><span class="setting-arr">›</span>
      </div>
      <div class="setting-row">
        <span>❓ Help &amp; Support</span><span class="setting-arr">›</span>
      </div>
      <div class="setting-row">
        <span>ℹ About App</span><span class="setting-arr">›</span>
      </div>
      <div class="setting-row" style="color:var(--danger);">
        <form method="POST" action="{{ route('logout') }}" style="width:100%;">
          @csrf
          <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger);font-size:14px;width:100%;text-align:left;display:flex;justify-content:space-between;">
            <span>⎋ Log Out</span><span>›</span>
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Edit Profil --}}
  <div class="modal-overlay" id="modal-edit-profile" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Edit Profil</div>
      </div>
      <form method="POST" action="{{ route('mahasiswa.profile.update') }}">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Nama Lengkap</label>
          <input type="text" name="name" value="{{ $user->name }}" required>
        </div>
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Email</label>
          <input type="email" name="email" value="{{ $user->email }}" required>
        </div>
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Password Baru <span style="font-weight:400;color:var(--text-muted);">(kosongkan jika tidak ingin ubah)</span></label>
          <input type="password" name="password" placeholder="Min. 8 karakter">
        </div>
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Konfirmasi Password</label>
          <input type="password" name="password_confirmation">
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-edit-profile').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

@endsection
