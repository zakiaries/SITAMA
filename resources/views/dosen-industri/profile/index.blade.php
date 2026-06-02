@extends('layouts.dosen-industri')
@section('title', 'Profil')
@php $title = 'Profil'; @endphp

@push('styles')
<style>
.profile-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
}
.stat-grid-2 { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px; }
.stat-mini { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;text-align:center; }
.stat-mini .val { font-size:24px;font-weight:800;color:var(--primary); }
.stat-mini .lbl { font-size:11px;color:var(--text-muted);margin-top:2px; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

@php $initials = collect(explode(' ', $user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp

<div class="profile-hero">
  <div class="profile-av">{{ $initials }}</div>
  <div>
    <div class="profile-name">{{ $user->name }}</div>
    <div class="profile-role">Pembimbing Industri</div>
    <div class="profile-email">{{ $user->email }}</div>
  </div>
  <button class="btn" onclick="document.getElementById('modal-edit-profile').classList.add('open')"
    style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.25);">
    ✏ Edit Profil
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
    <div class="setting-row" onclick="document.getElementById('modal-edit-profile').classList.add('open')" style="cursor:pointer;">
      <span>✏ Edit Profil</span><span class="setting-arr">›</span>
    </div>
    <div class="setting-row"><span>❓ Help &amp; Support</span><span class="setting-arr">›</span></div>
    <div class="setting-row"><span>ℹ About App</span><span class="setting-arr">›</span></div>
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

{{-- Modal Edit --}}
<div class="modal-overlay" id="modal-edit-profile" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header"><div class="modal-title">Edit Profil</div></div>
    <form method="POST" action="{{ route('dosen-industri.profile.update') }}">
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
