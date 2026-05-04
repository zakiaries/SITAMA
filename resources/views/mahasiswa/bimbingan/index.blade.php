@extends('layouts.mahasiswa')
@section('title', 'Bimbingan')
@php $title = 'Bimbingan'; @endphp
@section('content')
        <div class="page-header">
          <div class="page-title">Daftar Bimbingan</div>
          <button class="btn btn-primary" onclick="document.getElementById('modal-bimb').classList.add('open')">+ Tambah Bimbingan</button>
        </div>
        <div class="search-bar">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input placeholder="Pencarian...">
        </div>

        <div class="exp-item open">
          <div class="exp-header" onclick="toggle(this)">
            <div class="status-dot done">✓</div>
            <div><div class="exp-title">Konsultasi Magang</div><div class="exp-date">28/01/2024</div></div>
            <div class="chevron">▾</div>
          </div>
          <div class="exp-body">
            <div class="field-label">Aktivitas</div><div class="field-value">Diskusi tentang rencana kerja magang</div>
            <div class="field-group"><div class="field-label">Catatan Dosen</div><div class="field-value">Bagus, lanjutkan dengan implementasi</div></div>
            <div class="file-badge"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>File Bimbingan</div>
          </div>
        </div>
        <div class="exp-item">
          <div class="exp-header" onclick="toggle(this)">
            <div class="status-dot pending">—</div>
            <div><div class="exp-title">Progress Update</div><div class="exp-date">27/01/2024</div></div>
            <div class="chevron">▾</div>
          </div>
          <div class="exp-body"><div class="field-label">Aktivitas</div><div class="field-value">Update progress sprint 2</div></div>
        </div>
        <div class="exp-item">
          <div class="exp-header" onclick="toggle(this)">
            <div class="status-dot done">✓</div>
            <div><div class="exp-title">Presentasi Hasil</div><div class="exp-date">26/01/2024</div></div>
            <div class="chevron">▾</div>
          </div>
          <div class="exp-body"><div class="field-label">Aktivitas</div><div class="field-value">Presentasi hasil magang akhir semester</div></div>
        </div>
@endsection
