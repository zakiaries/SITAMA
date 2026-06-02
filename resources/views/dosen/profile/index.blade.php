@extends('layouts.dosen')
@section('title', 'Profil')
@php $title = 'Profil'; @endphp

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

@php
  $initials = collect(explode(' ', $user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp

{{-- Profile Hero --}}
<div class="profile-hero">
  <div class="profile-av">{{ $initials }}</div>
  <div>
    <div class="profile-name">{{ $user->name }}</div>
    <div class="profile-role">Dosen Pembimbing</div>
    <div class="profile-email">{{ $user->email }}</div>
  </div>
  <button class="btn" onclick="document.getElementById('modal-edit-profile').classList.add('open')"
    style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.25);">
    ✏ Edit Profil
  </button>
</div>

<div class="grid-2">

  {{-- Info Dosen --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:14px;">Informasi Akun</div>
    <div class="info-row"><div class="info-key">Nama</div><div class="info-val">{{ $user->name }}</div></div>
    <div class="info-row"><div class="info-key">Username</div><div class="info-val">{{ $user->username }}</div></div>
    <div class="info-row"><div class="info-key">Email</div><div class="info-val">{{ $user->email }}</div></div>
    <div class="info-row"><div class="info-key">Role</div><div class="info-val">
      <span style="background:var(--primary-light);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">
        Dosen
      </span>
    </div></div>
  </div>

  {{-- Settings --}}
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
        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger);font-size:14px;width:100%;text-align:left;display:flex;justify-content:space-between;font-family:inherit;">
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
    <form method="POST" action="{{ route('dosen.profile.update') }}">
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
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">
          Password Baru <span style="font-weight:400;color:var(--text-muted);">(kosongkan jika tidak ingin ubah)</span>
        </label>
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
