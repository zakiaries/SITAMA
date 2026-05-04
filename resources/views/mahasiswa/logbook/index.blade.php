@extends('layouts.mahasiswa')
@section('title', 'Log Book')
@php $title = 'Log Book'; @endphp
@section('content')
        <div class="page-header">
          <div class="page-title">Log Book</div>
          <button class="btn btn-primary" onclick="document.getElementById('modal-logbook').classList.add('open')">+ Tambah Log Book</button>
        </div>
        <div class="search-bar">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input placeholder="Pencarian...">
        </div>

        <div class="exp-item open">
          <div class="exp-header" onclick="toggle(this)">
            <div><div class="exp-title">Dokumentasi Code</div><div class="exp-date">01/05/2026</div></div>
            <div class="chevron">▾</div>
          </div>
          <div class="exp-body">
            <div class="field-label">Aktivitas</div><div class="field-value">Dokumentasi lengkap untuk source code</div>
            <div class="field-group"><div class="field-label">Catatan Dosen</div><div class="field-value">Dokumentasi bagus dan informatif</div></div>
            <div style="display:flex;gap:8px;margin-top:12px;">
              <button class="btn btn-outline btn-sm">✏ Edit</button>
              <button class="btn btn-danger btn-sm">🗑 Delete</button>
            </div>
          </div>
        </div>
        <div class="exp-item">
          <div class="exp-header" onclick="toggle(this)"><div><div class="exp-title">Bug Fixing dan Testing</div><div class="exp-date">29/04/2026</div></div><div class="chevron">▾</div></div>
          <div class="exp-body"><div class="field-label">Aktivitas</div><div class="field-value">Fix bug login dan unit testing</div></div>
        </div>
        <div class="exp-item">
          <div class="exp-header" onclick="toggle(this)"><div><div class="exp-title">Implementasi Feature Login</div><div class="exp-date">25/04/2026</div></div><div class="chevron">▾</div></div>
          <div class="exp-body"><div class="field-label">Aktivitas</div><div class="field-value">Implementasi fitur login dengan JWT</div></div>
        </div>
        <div class="exp-item">
          <div class="exp-header" onclick="toggle(this)"><div><div class="exp-title">Hari Pertama Magang</div><div class="exp-date">22/04/2026</div></div><div class="chevron">▾</div></div>
          <div class="exp-body"><div class="field-label">Aktivitas</div><div class="field-value">Setup development environment</div></div>
        </div>
@endsection
