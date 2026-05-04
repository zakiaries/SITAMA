@extends('layouts.mahasiswa')
@section('title', 'Profile')
@php $title = 'Profile'; @endphp
@section('content')
        <div class="profile-hero">
          <div class="profile-av">BS</div>
          <div>
            <div class="profile-name">Budi Santoso</div>
            <div class="profile-role">student</div>
            <div class="profile-email">budi.santoso@student.edu</div>
          </div>
          <button class="btn" style="margin-left:auto;background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.25);">✏ Edit Profil</button>
        </div>

        <div class="grid-2">
          <div class="card">
            <div class="card-title" style="margin-bottom:14px;">Informasi Industri</div>
            <div class="tabs">
              <div class="tab active" onclick="switchTab(this)">Industri 1</div>
              <div class="tab" onclick="switchTab(this)">Industri 2</div>
            </div>
            <div class="info-row"><div class="info-key">Nama Perusahaan</div><div class="info-val" style="color:var(--primary);">PT. Maju Jaya Indonesia – Flutter Developer</div></div>
            <div class="info-row"><div class="info-key">Tanggal Mulai</div><div class="info-val">03 March 2026</div></div>
            <div class="info-row"><div class="info-key">Tanggal Selesai</div><div><div style="background:repeating-linear-gradient(-45deg,#f5c200,#f5c200 5px,#333 5px,#333 10px);height:14px;border-radius:4px;opacity:0.4;width:120px;"></div></div></div>
          </div>

          <div class="card">
            <div class="card-title" style="margin-bottom:14px;">Pengaturan Akun</div>
            <div class="setting-row"><span>🔑 Ubah Kata Sandi</span><span class="setting-arr">›</span></div>
            <div class="setting-row"><span>🌙 Dark Mode</span><span class="setting-arr">›</span></div>
            <div class="setting-row"><span>❓ Help &amp; Support</span><span class="setting-arr">›</span></div>
            <div class="setting-row"><span>ℹ About App</span><span class="setting-arr">›</span></div>
            <div class="setting-row" style="color:var(--danger);"><span>⎋ Log Out</span><span class="setting-arr">›</span></div>
          </div>
        </div>
@endsection
