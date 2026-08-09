@extends('layouts.mahasiswa')
@section('title', 'Ajukan Magang')
@php $title = 'Ajukan Magang'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Info / blocker --}}
@if($hasActiveInternship)
  <div class="card" style="border-left:4px solid var(--success-text);">
    <div style="font-weight:700;color:var(--success-text);margin-bottom:6px;">Kamu sudah memiliki magang aktif</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">Pengajuan magang baru tidak diperlukan. Pantau status magangmu di <a href="{{ route('mahasiswa.magang-saya') }}" style="color:var(--primary);">Magang Saya</a>.</p>
  </div>

@elseif($hasPending)
  <div class="card" style="border-left:4px solid var(--warning);">
    <div style="font-weight:700;color:var(--warn-text);margin-bottom:6px;">Pengajuan sedang diproses</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">Kaprodi sedang mereview pengajuanmu. Kamu tidak bisa mengajukan lagi sampai pengajuan sebelumnya selesai diproses.</p>
  </div>

@elseif(! $hasLecturer)
  <div class="card" style="border-left:4px solid var(--warning);">
    <div style="font-weight:700;color:var(--warn-text);margin-bottom:6px;">Dosen pembimbing belum ditetapkan</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">
      Kaprodi belum menugaskan dosen pembimbing untukmu. Pengajuan magang bisa dikirim setelah dosen
      pembimbingmu ditetapkan — dosen itulah yang akan membimbing proposal dan mendampingi kendala
      magangmu. Silakan hubungi Kaprodi bila magangmu sudah dekat.
    </p>
  </div>

@else
  {{-- Form Ajukan Magang --}}
  {{-- Tanpa judul kartu: kartu ini SATU-SATUNYA isi halaman, dan judulnya sudah
       tercetak di header atas. --}}
  <div class="card" style="margin-bottom:20px;">
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px;">
      Isi data magang dan upload bukti penerimaan. Kaprodi akan mereview dan menghubungkan ke sistem.
    </p>

    {{-- autocomplete="off": tanpa ini peramban memulihkan sendiri isi form saat
         halaman dimuat ulang atau dibuka lewat tombol Kembali, sehingga
         pembimbing yang tadi dipilih tetap terpasang di dropdown padahal
         pengajuannya sudah terkirim. Sisi server sudah bersih — form ini hanya
         dirender saat mahasiswa memang boleh mengajukan, dan old() cuma terisi
         bila validasi gagal. --}}
    <form method="POST" action="{{ route('mahasiswa.ajukan-magang.store') }}" enctype="multipart/form-data" autocomplete="off">
      @csrf

      {{-- BUKTI PENERIMAAN --}}
      <div class="form-group" style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">
          Bukti Penerimaan Magang <span style="color:var(--danger);">*</span>
        </label>
        <input type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required
          style="width:100%;padding:8px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">Surat penerimaan, screenshot konfirmasi, atau dokumen sejenis (PDF/JPG/PNG, maks 10 MB).</div>
        @error('proof_file')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      {{-- PERUSAHAAN --}}
      @php
        $prefCompanyId   = old('company_id', request('company_id'));
        $prefCompanyName = old('company_name', request('company_name'));
        $companyMode     = old('company_mode', $prefCompanyId ? 'existing' : ($prefCompanyName ? 'new' : 'existing'));
      @endphp
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">
        Perusahaan <span style="color:var(--danger);">*</span>
      </div>
      <div style="display:flex;gap:16px;margin-bottom:6px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="existing" {{ $companyMode === 'existing' ? 'checked' : '' }} onchange="toggleCompany(this.value)"> Sudah terdaftar di SIMAMA
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="new" {{ $companyMode === 'new' ? 'checked' : '' }} onchange="toggleCompany(this.value)"> Belum terdaftar
        </label>
      </div>
      <div id="co-existing" style="margin-bottom:14px;{{ $companyMode === 'existing' ? '' : 'display:none;' }}">
        <select name="company_id" id="company_id_sel" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih perusahaan —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $prefCompanyId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
        @error('company_id')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>
      <div id="co-new" style="margin-bottom:14px;{{ $companyMode === 'new' ? '' : 'display:none;' }}">
        <input type="text" name="company_name" value="{{ $prefCompanyName }}" placeholder="Nama perusahaan"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        @error('company_name')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      {{-- PEMBIMBING INDUSTRI --}}
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">
        Data Pembimbing Industri <span style="color:var(--danger);">*</span>
      </div>
      @if($existingPics->isNotEmpty())
      <select name="lecturer_industry_id" id="pic_sel" onchange="togglePic(this.value)"
        style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;margin-bottom:6px;">
        <option value="">— Pembimbing baru (isi data di bawah) —</option>
        @foreach($existingPics as $p)
          <option value="{{ $p['id'] }}" {{ old('lecturer_industry_id') == $p['id'] ? 'selected' : '' }}>
            {{ $p['name'] }}@if($p['company']) — {{ $p['company'] }}@endif
          </option>
        @endforeach
      </select>
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;">
        Kalau pembimbingmu sudah pernah terdaftar (mis. dari mahasiswa lain di perusahaan yang sama), pilih dari sini — tak perlu daftar ulang.
      </div>
      @endif
      <div id="pic-manual" style="background:var(--warm);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:14px;display:flex;flex-direction:column;gap:8px;">
        <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Nama lengkap pembimbing *"
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <input type="tel" name="pic_phone" value="{{ old('pic_phone') }}" placeholder="No. HP / WhatsApp (opsional)"
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <input type="email" name="pic_email" value="{{ old('pic_email') }}" placeholder="Email (opsional)"
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        @error('pic_name')<div style="color:var(--danger);font-size:12px;">{{ $message }}</div>@enderror
      </div>

      {{-- BIDANG, POSISI & TANGGAL --}}
      <div class="form-group" style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Bidang</label>
        <select name="bidang" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih bidang —</option>
          @foreach(\App\Models\JobListing::BIDANG_OPTIONS as $b)
            <option value="{{ $b }}" {{ old('bidang') === $b ? 'selected' : '' }}>{{ $b }}</option>
          @endforeach
        </select>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">Membantu perusahaanmu muncul di pencarian & rekomendasi untuk adik tingkat.</div>
      </div>
      <div class="form-group" style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Posisi (spesifik)</label>
        <input type="text" name="position" value="{{ old('position') }}" placeholder="Contoh: Backend Developer (Laravel)"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>
      <div class="grid-duo" style="gap:14px;margin-bottom:18px;">
        <div class="form-group" style="margin:0;">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">
            Tanggal Mulai <span style="color:var(--danger);">*</span>
          </label>
          <input type="date" name="start_date" value="{{ old('start_date') }}" required
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          @error('start_date')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin:0;">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">
            Tanggal Selesai <span style="color:var(--danger);">*</span>
          </label>
          <input type="date" name="end_date" value="{{ old('end_date') }}" required
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          @error('end_date')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div style="grid-column:1 / -1;font-size:11.5px;color:var(--text-muted);margin-top:-6px;">
          Sesuai periode di surat penerimaan magangmu. Tanggal ini yang tampil sebagai akhir magang di profil dan portal dosen.
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Kirim Pengajuan</button>
    </form>
  </div>
@endif

{{-- Riwayat Pengajuan --}}
@if($requests->isNotEmpty())
<div class="page-header" style="margin-top:8px;"><div class="page-title">Riwayat Pengajuan</div></div>
@foreach($requests as $req)
@php
  $badge = match($req->status) {
    'approved' => ['bg' => 'var(--success-bg)', 'color' => 'var(--success-text)', 'label' => 'Disetujui'],
    'rejected' => ['bg' => 'var(--danger-bg)', 'color' => 'var(--danger)', 'label' => 'Ditolak'],
    default    => ['bg' => 'var(--warn-bg)', 'color' => 'var(--warn-text)', 'label' => 'Menunggu'],
  };
@endphp
<div class="card" style="margin-bottom:12px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
    <div>
      <div style="font-weight:700;font-size:14px;color:var(--text);">{{ $req->company_name }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        {{ $req->position ?? '-' }} &middot;
        {{ $req->start_date?->format('d M Y') ?? '-' }} &ndash; {{ $req->end_date?->format('d M Y') ?? '-' }}
      </div>
    </div>
    <span style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;white-space:nowrap;">
      {{ $badge['label'] }}
    </span>
  </div>
  <div style="font-size:12.5px;color:var(--text-muted);">
    Pembimbing: <strong style="color:var(--text);">{{ $req->pic_name }}</strong>
    @if($req->pic_phone) &middot; {{ $req->pic_phone }} @endif
  </div>
  @if($req->proof_file)
    <div style="margin-top:8px;">
      <a href="{{ route('berkas.bukti', $req) }}" target="_blank"
        style="font-size:12px;color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
        <x-icon name="doc" :size="13"/> Lihat Bukti Penerimaan
      </a>
    </div>
  @endif
  @if($req->status === 'rejected' && $req->rejection_reason)
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:12px;color:var(--danger);">
      <strong>Alasan ditolak:</strong> {{ $req->rejection_reason }}
    </div>
  @endif
  @if($req->status === 'approved' && $req->createdLecturer)
    <div style="font-size:12px;color:var(--success-text);margin-top:8px;">
      <x-icon name="check" :size="12"/> Magang berhasil dicatat &middot; Pembimbing industri: {{ $req->createdLecturer->user->name ?? '-' }}
    </div>
  @endif
  <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">Diajukan {{ $req->created_at->format('d M Y H:i') }}</div>
  @if($req->status === 'pending')
    <form method="POST" action="{{ route('mahasiswa.ajukan-magang.cancel', $req->id) }}" style="margin-top:10px;"
      data-confirm="Batalkan pengajuan magang ini? Kamu bisa mengajukan lagi setelahnya." data-confirm-danger>
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger);border-color:#F0C4BE;">
        <x-icon name="x" :size="13"/> Batalkan Pengajuan
      </button>
    </form>
  @endif
</div>
@endforeach
@endif

<script>
function toggleCompany(mode) {
  document.getElementById('co-existing').style.display = mode === 'existing' ? 'block' : 'none';
  document.getElementById('co-new').style.display      = mode === 'new'      ? 'block' : 'none';
  document.getElementById('company_id_sel').required   = mode === 'existing';
}

// Pilih PIC terdaftar → sembunyikan isian manual & tak wajib; "baru" → tampilkan.
function togglePic(v) {
  var manual = document.getElementById('pic-manual');
  var name   = document.querySelector('input[name="pic_name"]');
  if (v) { manual.style.display = 'none'; name.required = false; }
  else   { manual.style.display = 'flex'; name.required = true; }
}
(function () {
  var sel = document.getElementById('pic_sel');
  togglePic(sel ? sel.value : '');
})();
</script>
@endsection
