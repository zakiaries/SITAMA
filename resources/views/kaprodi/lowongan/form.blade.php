@extends('layouts.kaprodi')
@section('title', $lowongan ? 'Edit Pengumuman' : 'Tambah Pengumuman')
@php
  $title    = $lowongan ? 'Edit Pengumuman' : 'Tambah Pengumuman';
  $isEdit   = (bool) $lowongan;
  $action   = $isEdit ? route('kaprodi.lowongan.update', $lowongan) : route('kaprodi.lowongan.store');
  $skillStr = $isEdit ? implode(', ', $lowongan->skills ?? []) : old('skills');
@endphp

@push('styles')
<style>
.form-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:24px;max-width:680px; }
.fg { margin-bottom:18px; }
.fg label { display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px; }
.fg .hint { font-size:11px;color:var(--text-muted);font-weight:400; }
.fg input, .fg select, .fg textarea {
  width:100%;border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;
  font-size:13px;font-family:inherit;color:var(--text);outline:none;transition:border-color .15s;
}
.fg input:focus, .fg select:focus, .fg textarea:focus { border-color:var(--primary); }
.fg textarea { resize:vertical;min-height:100px; }
.fg-row { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.err { color:var(--danger);font-size:12px;margin-top:4px; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="{{ route('kaprodi.lowongan.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
  <div class="page-title">{{ $isEdit ? 'Edit Pengumuman Lowongan' : 'Tambah Pengumuman Lowongan' }}</div>
</div>

<div class="form-card">
  <form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="fg-row">
      <div class="fg">
        <label>Judul Posisi <span style="color:var(--danger);">*</span></label>
        <input type="text" name="title" value="{{ old('title', $lowongan->title ?? '') }}" placeholder="Contoh: Frontend Developer Intern" required>
        @error('title')<div class="err">{{ $message }}</div>@enderror
      </div>
      <div class="fg">
        <label>Nama Perusahaan <span style="color:var(--danger);">*</span></label>
        <input type="text" name="company_name" value="{{ old('company_name', $lowongan->company_name ?? '') }}" placeholder="Contoh: PT Telkom Indonesia" required>
        @error('company_name')<div class="err">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="fg-row">
      <div class="fg">
        <label>Divisi</label>
        <input type="text" name="division" value="{{ old('division', $lowongan->division ?? '') }}" placeholder="Contoh: Teknik Informatika">
      </div>
      <div class="fg">
        <label>Lokasi</label>
        <input type="text" name="location" value="{{ old('location', $lowongan->location ?? '') }}" placeholder="Contoh: Semarang">
      </div>
    </div>

    <div class="fg-row">
      <div class="fg">
        <label>Tipe Pekerjaan <span style="color:var(--danger);">*</span></label>
        @php $jt = old('job_type', $lowongan->job_type ?? 'On-site'); @endphp
        <select name="job_type" required>
          <option value="On-site" {{ $jt==='On-site'?'selected':'' }}>On-site</option>
          <option value="Work From Home" {{ $jt==='Work From Home'?'selected':'' }}>Work From Home</option>
          <option value="Hybrid" {{ $jt==='Hybrid'?'selected':'' }}>Hybrid</option>
        </select>
      </div>
      <div class="fg">
        <label>Status <span style="color:var(--danger);">*</span></label>
        @php $stt = old('status', $lowongan->status ?? 'active'); @endphp
        <select name="status" required>
          <option value="active" {{ $stt==='active'?'selected':'' }}>Aktif (tampil ke mahasiswa)</option>
          <option value="closed" {{ $stt==='closed'?'selected':'' }}>Ditutup</option>
        </select>
      </div>
    </div>

    <div class="fg">
      <label>Kategori Skill <span class="hint">(pisahkan dengan koma)</span></label>
      <input type="text" name="skills" value="{{ $skillStr }}" placeholder="Laravel, PHP, MySQL">
    </div>

    <div style="font-size:12px;font-weight:700;color:var(--primary);margin:4px 0 12px;">Kontak (PIC)</div>

    <div class="fg-row">
      <div class="fg">
        <label>Nama PIC</label>
        <input type="text" name="pic_name" value="{{ old('pic_name', $lowongan->pic_name ?? '') }}" placeholder="Contoh: Bpk. Andi">
      </div>
      <div class="fg">
        <label>No. Telp / WA PIC</label>
        <input type="text" name="pic_phone" value="{{ old('pic_phone', $lowongan->pic_phone ?? '') }}" placeholder="Contoh: 0812xxxxxxx">
      </div>
    </div>

    <div class="fg">
      <label>Email PIC</label>
      <input type="email" name="pic_email" value="{{ old('pic_email', $lowongan->pic_email ?? '') }}" placeholder="rekrutmen@perusahaan.com">
      @error('pic_email')<div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="fg">
      <label>Deskripsi &amp; Persyaratan</label>
      <textarea name="description" placeholder="Jelaskan posisi, persyaratan, dan cara melamar...">{{ old('description', $lowongan->description ?? '') }}</textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
      <a href="{{ route('kaprodi.lowongan.index') }}" class="btn btn-outline">Batal</a>
      <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Terbitkan Pengumuman' }}</button>
    </div>
  </form>
</div>

@endsection
