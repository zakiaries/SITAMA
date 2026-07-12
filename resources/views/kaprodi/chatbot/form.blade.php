@extends('layouts.kaprodi')
@section('title', $item ? 'Edit FAQ' : 'Tambah FAQ')
@php $title = $item ? 'Edit FAQ Chatbot' : 'Tambah FAQ Chatbot'; @endphp
@section('content')

<div class="page-header">
  <div class="page-title">{{ $item ? 'Edit FAQ Chatbot' : 'Tambah FAQ Chatbot' }}</div>
</div>

@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;">
      @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="card" style="max-width:720px;">
  <form method="POST" action="{{ $item ? route('kaprodi.chatbot.update', $item) : route('kaprodi.chatbot.store') }}">
    @csrf
    @if($item) @method('PUT') @endif

    @php
      $labelStyle = 'display:block;font-size:12px;font-weight:700;color:var(--text);margin-bottom:6px;';
      $inputStyle = 'width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;';
      $hintStyle  = 'font-size:11px;color:var(--text-muted);margin-top:4px;';
    @endphp

    <div style="margin-bottom:16px;">
      <label style="{{ $labelStyle }}">Pertanyaan (representatif)</label>
      <input type="text" name="pertanyaan" maxlength="500" required style="{{ $inputStyle }}"
             value="{{ old('pertanyaan', $item->pertanyaan ?? '') }}"
             placeholder="Contoh: Bagaimana cara mengajukan magang?">
      <div style="{{ $hintStyle }}">Kalimat pertanyaan utama yang ditampilkan ke mahasiswa sebagai saran.</div>
    </div>

    <div style="margin-bottom:16px;">
      <label style="{{ $labelStyle }}">Kata Kunci / Variasi</label>
      <textarea name="kata_kunci" rows="3" required style="{{ $inputStyle }}resize:vertical;"
                placeholder="ajukan mengajukan magang daftar lapor perusahaan bukti penerimaan">{{ old('kata_kunci', $item->kata_kunci ?? '') }}</textarea>
      <div style="{{ $hintStyle }}">Kumpulan kata/frasa (dipisah spasi) yang memperkaya pencocokan TF-IDF. Makin variatif, makin tahan terhadap gaya bahasa mahasiswa.</div>
    </div>

    <div style="margin-bottom:16px;">
      <label style="{{ $labelStyle }}">Jawaban</label>
      <textarea name="jawaban" rows="4" required style="{{ $inputStyle }}resize:vertical;"
                placeholder="Jawaban/panduan yang akan direkomendasikan chatbot.">{{ old('jawaban', $item->jawaban ?? '') }}</textarea>
    </div>

    <div style="margin-bottom:16px;">
      <label style="{{ $labelStyle }}">Kategori</label>
      <input type="text" name="kategori" maxlength="100" required style="{{ $inputStyle }}"
             value="{{ old('kategori', $item->kategori ?? 'Umum') }}"
             placeholder="Umum / Magang / Seminar / Bimbingan / Nilai / Akun / Laporan / Log Book"
             list="kategori-list">
      <datalist id="kategori-list">
        @foreach(['Umum','Akun','Magang','Bimbingan','Log Book','Laporan','Nilai','Seminar'] as $k)
          <option value="{{ $k }}">
        @endforeach
      </datalist>
    </div>

    <div style="margin-bottom:20px;display:flex;align-items:center;gap:8px;">
      <input type="checkbox" name="is_active" value="1" id="is_active"
             {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
      <label for="is_active" style="font-size:13px;color:var(--text);cursor:pointer;">Aktif (dipakai chatbot)</label>
    </div>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-primary btn-sm"><x-icon name="save" :size="14"/> {{ $item ? 'Simpan Perubahan' : 'Simpan' }}</button>
      <a href="{{ route('kaprodi.chatbot.index') }}" class="btn btn-outline btn-sm">Batal</a>
    </div>
  </form>
</div>

@endsection
