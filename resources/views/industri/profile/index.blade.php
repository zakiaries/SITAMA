@extends('layouts.industri')
@section('title', 'Profil Perusahaan')
@php $title = 'Profil Perusahaan'; @endphp

@push('styles')
<style>
.stat-grid-3 { display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px; }
.stat-mini { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;text-align:center; }
.stat-mini .val { font-size:24px;font-weight:800;color:var(--primary); }
.stat-mini .lbl { font-size:11px;color:var(--text-muted);margin-top:2px; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

@php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $company->name)))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp

<div class="profile-hero">
  <div class="profile-av">{{ $initials }}</div>
  <div>
    <div class="profile-name">{{ $company->name }}</div>
    <div class="profile-role">{{ $company->field ?? 'Perusahaan' }}</div>
    <div class="profile-email">{{ $company->email }}</div>
  </div>
  <button class="btn" onclick="document.getElementById('modal-edit-profile').classList.add('open')"
    style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.25);">
    ✏ Edit Profil
  </button>
</div>

<div class="stat-grid-3">
  <div class="stat-mini"><div class="val">{{ $lowonganAktif }}</div><div class="lbl">Lowongan Aktif</div></div>
  <div class="stat-mini"><div class="val">{{ $magangAktif }}</div><div class="lbl">Magang Aktif</div></div>
  <div class="stat-mini"><div class="val">{{ $pelamarDiterima }}</div><div class="lbl">Pelamar Diterima</div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header" style="margin-bottom:14px;">
      <div class="card-title">Informasi Perusahaan</div>
      @if($company->verification_status === 'verified')
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Terverifikasi</span>
      @elseif($company->verification_status === 'rejected')
        <span style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✕ Ditolak</span>
      @else
        <span style="background:#fef9c3;color:#92400e;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">⏳ Menunggu</span>
      @endif
    </div>
    <div class="info-row"><div class="info-key">Nama</div><div class="info-val">{{ $company->name }}</div></div>
    <div class="info-row"><div class="info-key">Bidang</div><div class="info-val">{{ $company->field ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Alamat</div><div class="info-val">{{ $company->address ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Telepon</div><div class="info-val">{{ $company->phone ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Email</div><div class="info-val">{{ $company->email }}</div></div>
    @if($company->verification_status === 'rejected' && $company->rejection_reason)
      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 12px;margin-top:12px;font-size:12px;color:#991b1b;">
        <strong>Alasan ditolak:</strong> {{ $company->rejection_reason }}
      </div>
    @endif
  </div>

  <div class="card">
    <div class="card-title" style="margin-bottom:14px;">Pengaturan Akun</div>
    <div class="setting-row" onclick="document.getElementById('modal-edit-profile').classList.add('open')" style="cursor:pointer;">
      <span>✏ Edit Profil Perusahaan</span><span class="setting-arr">›</span>
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
    <div class="modal-header"><div class="modal-title">Edit Profil Perusahaan</div></div>
    <form method="POST" action="{{ route('industri.profile.update') }}">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Nama Perusahaan</label>
        <input type="text" name="name" value="{{ $company->name }}" required>
      </div>
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Bidang</label>
        <input type="text" name="field" value="{{ $company->field }}">
      </div>
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Alamat</label>
        <input type="text" name="address" value="{{ $company->address }}">
      </div>
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Telepon</label>
        <input type="text" name="phone" value="{{ $company->phone }}">
      </div>
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Email</label>
        <input type="email" name="email" value="{{ $company->email }}" required>
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
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@endsection
