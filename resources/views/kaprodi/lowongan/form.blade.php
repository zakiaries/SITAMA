@extends('layouts.kaprodi')
@php $isEdit = $listing->exists; @endphp
@section('title', $isEdit ? 'Edit Lowongan' : 'Tambah Lowongan')
@php $title = $isEdit ? 'Edit Lowongan' : 'Tambah Lowongan'; @endphp
@section('content')

<a href="{{ route('kaprodi.lowongan.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:14px;">← Kembali</a>

@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;">
      @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $isEdit ? route('kaprodi.lowongan.update', $listing) : route('kaprodi.lowongan.store') }}">
  @csrf
  @if($isEdit) @method('PUT') @endif

  @php
    $skillsText = is_array($listing->skills) ? implode(', ', $listing->skills) : '';
    $fld = fn($n, $def = '') => old($n, $listing->{$n} ?? $def);
    $lbl = 'display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;';
    $inp = 'width:100%;padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;';
  @endphp

  <div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:12px;">Perusahaan (Afiliasi)</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <div style="grid-column:1 / -1;">
        <label style="{{ $lbl }}">Nama Perusahaan <span style="color:var(--danger);">*</span></label>
        <input type="text" name="company_name" value="{{ $fld('company_name') }}" required style="{{ $inp }}" placeholder="mis. PT Teknologi Nusantara">
      </div>
      <div>
        <label style="{{ $lbl }}">Bidang / Industri</label>
        <input type="text" name="field" value="{{ $fld('field') }}" style="{{ $inp }}" placeholder="mis. Teknologi Informasi">
      </div>
      <div>
        <label style="{{ $lbl }}">Lokasi</label>
        <input type="text" name="location" value="{{ $fld('location') }}" style="{{ $inp }}" placeholder="mis. Semarang">
      </div>
    </div>
    <p style="font-size:11.5px;color:var(--text-muted);margin:12px 0 0;">Perusahaan akan otomatis ditandai <strong>berafiliasi Polines</strong> dan tampil di daftar lowongan mahasiswa.</p>
  </div>

  <div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:12px;">Detail Lowongan</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <div style="grid-column:1 / -1;">
        <label style="{{ $lbl }}">Judul / Posisi <span style="color:var(--danger);">*</span></label>
        <input type="text" name="title" value="{{ $fld('title') }}" required style="{{ $inp }}" placeholder="mis. Web Developer Intern">
      </div>
      <div>
        <label style="{{ $lbl }}">Bidang</label>
        <select name="bidang" style="{{ $inp }}">
          <option value="">— Pilih bidang —</option>
          @foreach(\App\Models\JobListing::BIDANG_OPTIONS as $b)
            <option value="{{ $b }}" {{ $fld('bidang') === $b ? 'selected' : '' }}>{{ $b }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label style="{{ $lbl }}">Divisi</label>
        <input type="text" name="division" value="{{ $fld('division') }}" style="{{ $inp }}" placeholder="mis. Engineering">
      </div>
      <div>
        <label style="{{ $lbl }}">Tipe</label>
        <input type="text" name="job_type" value="{{ $fld('job_type') }}" style="{{ $inp }}" placeholder="mis. WFO / Hybrid / Remote">
      </div>
      <div>
        <label style="{{ $lbl }}">Kuota <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
        <input type="number" name="quota" value="{{ $fld('quota') }}" min="1" max="999" style="{{ $inp }}" placeholder="mis. 3">
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">
          Kosongkan bila tak dibatasi. Angka ini <strong>penanda saja</strong> — sistem menampilkan berapa yang terisi
          dan menandai lowongan penuh, tapi tidak menghalangi mahasiswa mengajukan.
        </div>
      </div>
      <div style="grid-column:1 / -1;">
        <label style="{{ $lbl }}">Keahlian / Skill <span style="color:var(--text-muted);font-weight:400;">(pisahkan dengan koma)</span></label>
        <input type="text" name="skills" value="{{ old('skills', $skillsText) }}" style="{{ $inp }}" placeholder="mis. Laravel, MySQL, Git">
      </div>
      <div style="grid-column:1 / -1;">
        <label style="{{ $lbl }}">Deskripsi</label>
        <textarea name="description" rows="4" style="{{ $inp }}" placeholder="Tugas, syarat, dan info lain…">{{ $fld('description') }}</textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:10px;">
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Lowongan' }}</button>
    <a href="{{ route('kaprodi.lowongan.index') }}" class="btn btn-outline">Batal</a>
  </div>
</form>

@endsection
