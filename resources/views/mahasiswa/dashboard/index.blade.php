@extends('layouts.mahasiswa')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp
@section('content')
        <div class="greeting-sub">Selamat datang kembali 👋</div>
        <div class="greeting-name">Halo, Budi Santoso</div>

        <div class="alert-box">
          <div class="alert-icon">
            <svg width="16" height="16" fill="none" stroke="#854f0b" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91"/></svg>
          </div>
          <div>
            <div class="alert-title">Pembimbing Anda telah memberikan feedback</div>
            <div class="alert-body">Silakan cek feedback di bagian bimbingan</div>
          </div>
          <div class="alert-time">2026-05-01 &nbsp;02:53</div>
        </div>

        <div class="grid-4">
          <div class="stat-card"><div class="stat-label">Log Book</div><div class="stat-value">4</div><div class="stat-sub">Total entri</div></div>
          <div class="stat-card"><div class="stat-label">Bimbingan</div><div class="stat-value">3</div><div class="stat-sub">Sesi selesai</div></div>
          <div class="stat-card"><div class="stat-label">Seminar</div><div class="stat-value">3</div><div class="stat-sub">Terjadwal</div></div>
          <div class="stat-card"><div class="stat-label">Hari Magang</div><div class="stat-value">41</div><div class="stat-sub">Sejak mulai</div></div>
        </div>

        <div class="grid-2">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Bimbingan Terbaru</div>
              <a href="{{ route('mahasiswa.bimbingan') }}" class="see-all">Lihat semua &rsaquo;</a>
            </div>
            <div class="exp-item open">
              <div class="exp-header" onclick="toggle(this)">
                <div class="status-dot done">✓</div>
                <div><div class="exp-title">Konsultasi Magang</div><div class="exp-date">27/04/2026</div></div>
                <div class="chevron">▾</div>
              </div>
              <div class="exp-body">
                <div class="field-label">Aktivitas</div><div class="field-value">Diskusi progress project</div>
                <div class="field-group"><div class="field-label">Catatan Dosen</div><div class="field-value">Lanjutkan dengan sprint berikutnya</div></div>
                <div class="file-badge">
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  File Bimbingan
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-title">Log Book Terbaru</div>
              <a href="{{ route('mahasiswa.logbook') }}" class="see-all">Lihat semua &rsaquo;</a>
            </div>
            <div class="exp-item open">
              <div class="exp-header" onclick="toggle(this)">
                <div><div class="exp-title">Hari Pertama Magang</div><div class="exp-date">22/04/2026</div></div>
                <div class="chevron">▾</div>
              </div>
              <div class="exp-body">
                <div class="field-label">Aktivitas</div><div class="field-value">Setup development environment</div>
                <div class="field-group"><div class="field-label">Catatan Dosen</div><div class="field-value">Perkenalan dengan tim</div></div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                  <button class="btn btn-outline btn-sm">✏ Edit</button>
                  <button class="btn btn-danger btn-sm">🗑 Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
@endsection
