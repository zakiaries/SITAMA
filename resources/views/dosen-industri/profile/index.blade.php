@extends('layouts.dosen-industri')
@section('title', 'Profil')
@php $title = 'Profil'; @endphp

@push('styles')
<style>
.stat-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px; }
.stat-mini { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;text-align:center; }
.stat-mini .val { font-size:24px;font-weight:800;color:var(--primary); }
.stat-mini .lbl { font-size:11px;color:var(--text-muted);margin-top:2px; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

@php $initials = collect(explode(' ', $user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp

<div class="profile-hero">
  <div class="profile-av">
    @if($user->photo_profile)
      <img src="{{ $user->photoUrl() }}" alt="Foto profil" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
    @else
      {{ $initials }}
    @endif
  </div>
  <div>
    <div class="profile-name">{{ $user->name }}</div>
    <div class="profile-role">Pembimbing Industri</div>
    <div class="profile-email">{{ $user->email }}</div>
  </div>
  <button class="btn btn-ghost" onclick="document.getElementById('modal-edit-profile').classList.add('open')"
    style="margin-left:auto;">
    <x-icon name="pencil" :size="15"/> Edit Profil
  </button>
</div>

{{-- Statistik --}}
<div class="stat-grid-2">
  <div class="stat-mini"><div class="val">{{ $totalMahasiswa }}</div><div class="lbl">Total Mahasiswa</div></div>
  <div class="stat-mini"><div class="val">{{ $aktif }}</div><div class="lbl">Sedang Aktif</div></div>
  <div class="stat-mini"><div class="val">{{ $selesai }}</div><div class="lbl">Telah Selesai</div></div>
  <div class="stat-mini"><div class="val">{{ $totalMahasiswa - $aktif }}</div><div class="lbl">Telah Dinilai</div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title" style="margin-bottom:14px;">Informasi Akun</div>
    <div class="info-row"><div class="info-key">Nama</div><div class="info-val">{{ $user->name }}</div></div>
    <div class="info-row"><div class="info-key">Username</div><div class="info-val">{{ $user->username }}</div></div>
    <div class="info-row"><div class="info-key">Email</div><div class="info-val">{{ $user->email }}</div></div>
    <div class="info-row"><div class="info-key">Role</div><div class="info-val">
      <span style="background:var(--primary-light);color:var(--primary-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Pembimbing Industri</span>
    </div></div>
  </div>

  <div class="card">
    <div class="card-title" style="margin-bottom:14px;">Pengaturan Akun</div>
    <div class="settings">
      <div class="setting-row" onclick="document.getElementById('modal-edit-profile').classList.add('open')">
        <div class="setting-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></div>
        <div class="setting-main"><div class="setting-label">Edit Profil</div><div class="setting-desc">Ubah nama, email, atau password</div></div>
        <svg class="setting-arr" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <a href="{{ route('bantuan') }}" class="setting-row" style="text-decoration:none;color:inherit;">
        <div class="setting-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="setting-main"><div class="setting-label">Help &amp; Support</div><div class="setting-desc">Bantuan dan pusat dukungan</div></div>
        <svg class="setting-arr" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
      <a href="{{ route('about') }}" class="setting-row" style="text-decoration:none;color:inherit;">
        <div class="setting-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>
        <div class="setting-main"><div class="setting-label">About App</div><div class="setting-desc">Versi &amp; informasi aplikasi</div></div>
        <svg class="setting-arr" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="setting-row danger">
          <div class="setting-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></div>
          <div class="setting-main"><div class="setting-label">Log Out</div><div class="setting-desc">Keluar dari akun ini</div></div>
          <svg class="setting-arr" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Modal Edit --}}
<div class="modal-overlay" id="modal-edit-profile" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header"><div class="modal-title">Edit Profil</div></div>
    <form method="POST" action="{{ route('dosen-industri.profile.update') }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Foto Profil <span style="font-weight:400;color:var(--text-muted);">(JPG/PNG, maks 4 MB)</span></label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png">
      </div>
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
          Password Baru <span style="font-weight:400;color:var(--text-muted);">(kosongkan jika tidak ubah)</span>
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
