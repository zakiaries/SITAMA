@extends('layouts.kaprodi')
@section('title', 'Data Dosen')
@php $title = 'Data Dosen'; @endphp

@push('styles')
<style>
.dosen-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
  text-decoration:none;transition:all .15s;
}
.dosen-card:hover { border-color:var(--primary);box-shadow:0 2px 12px rgba(45,62,110,0.08);transform:translateY(-1px); }
.dosen-av   { width:48px;height:48px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px; }
.dosen-info { flex:1;min-width:0; }
.dosen-name { font-size:14px;font-weight:700;color:var(--text); }
.dosen-user { font-size:12px;color:var(--text-muted);margin-bottom:8px; }
.cap-bar-label { display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.cap-bar-track { height:6px;background:var(--border);border-radius:4px;overflow:hidden; }
.cap-bar-fill  { height:100%;background:var(--primary);border-radius:4px; }
.dosen-count { width:50px;height:50px;border-radius:12px;background:var(--primary-light);color:var(--primary-text);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0; }
.dosen-count .n { font-size:20px;font-weight:800;line-height:1; }
.dosen-count .l { font-size:9px;text-transform:uppercase; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ $errors->first() }}</div>
@endif

{{-- Tab --}}
<div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border);">
  @foreach(['dosen' => 'Dosen Kampus', 'industri' => 'Pembimbing Industri'] as $key => $label)
  <a href="{{ route('kaprodi.dosen.index', ['tab' => $key, 'search' => request('search')]) }}"
     style="padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid {{ $tab === $key ? 'var(--primary)' : 'transparent' }};
            font-size:13px;font-weight:700;color:{{ $tab === $key ? 'var(--primary)' : 'var(--text-muted)' }};
            text-decoration:none;display:flex;align-items:center;gap:8px;">
    {{ $label }}
    <span style="background:{{ $tab === $key ? 'var(--primary-light)' : 'var(--warm)' }};color:{{ $tab === $key ? 'var(--primary-text)' : 'var(--text-muted)' }};font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">
      {{ $counts[$key] }}
    </span>
  </a>
  @endforeach
</div>

<div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
  <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('modal-add-dosen').style.display='flex'">
    + Tambah {{ $tab === 'industri' ? 'Pembimbing Industri' : 'Dosen' }}
  </button>
</div>

<form method="GET" action="{{ route('kaprodi.dosen.index') }}">
  <input type="hidden" name="tab" value="{{ $tab }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari nama {{ $tab === 'industri' ? 'pembimbing industri' : 'dosen' }}..." value="{{ request('search') }}">
  </div>
</form>

<div style="font-size:12px;color:var(--text-muted);margin-bottom:13px;">
  Total <strong style="color:var(--text);">{{ $lecturers->count() }} {{ $tab === 'industri' ? 'pembimbing industri' : 'dosen' }}</strong>
</div>

@forelse($lecturers as $lec)
@php
  $count = $lec->students_count ?? 0;
  $max   = 20;
  $pct   = min(round($count / $max * 100), 100);
  $colors = [
    ['bg'=>'var(--blue-tint)','text'=>'var(--primary)'],['bg'=>'var(--success-bg)','text'=>'var(--success-text)'],
    ['bg'=>'var(--warn-bg)','text'=>'var(--warn-text)'],['bg'=>'var(--purple-bg)','text'=>'var(--purple-text)'],
  ];
  $color    = $colors[$lec->id % count($colors)];
@endphp
<a href="{{ route('kaprodi.dosen.detail', $lec) }}" class="dosen-card">
  <x-avatar :user="$lec->user" class="dosen-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};" />
  <div class="dosen-info">
    <div class="dosen-name">{{ $lec->user->name ?? '-' }}</div>
    <div class="dosen-user">{{ $lec->user->username ?? '-' }}</div>
    <div class="cap-bar-label"><span>{{ $tab === 'industri' ? 'Mahasiswa dibimbing' : 'Mahasiswa bimbingan' }}</span><span>{{ $count }}/{{ $max }}</span></div>
    <div class="cap-bar-track"><div class="cap-bar-fill" style="width:{{ $pct }}%;"></div></div>
  </div>
  <div class="dosen-count">
    <div class="n">{{ $count }}</div>
    <div class="l">Mhs</div>
  </div>
</a>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Tidak ada dosen ditemukan.</p></div>
@endforelse

{{-- Modal Tambah Dosen / Pembimbing Industri --}}
<div id="modal-add-dosen" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:430px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:4px;">Tambah {{ $tab === 'industri' ? 'Pembimbing Industri' : 'Dosen Kampus' }}</div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">Akun dibuat oleh Kaprodi dan langsung aktif (bisa langsung login).</p>
    <form method="POST" action="{{ route('kaprodi.dosen.store') }}">
      @csrf
      <input type="hidden" name="tab" value="{{ $tab }}">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
      </div>
      <div class="form-group">
        <label>{{ $tab === 'industri' ? 'Username' : 'NIP / Username' }}</label>
        <input type="text" name="username" value="{{ old('username') }}" required>
      </div>
      <div class="form-group">
        <label>Email <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
        <input type="email" name="email" value="{{ old('email') }}">
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="pw-wrap">
          <input type="password" name="password" placeholder="Min. 6 karakter" required>
          <x-password-toggle />
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-add-dosen').style.display='none'">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@if($errors->any())
<script>document.getElementById('modal-add-dosen').style.display='flex';</script>
@endif

@endsection
