@extends('layouts.mahasiswa')
@section('title', 'Ajukan Akun Industri')
@php $title = 'Ajukan Akun Industri'; @endphp

@push('styles')
<style>
.req-status-card {
  border-radius: 12px; padding: 18px; margin-bottom: 16px;
  border: 1.5px solid var(--border); background: #fff;
}
.req-status-card.pending  { background:#fffbeb; border-color:#fde68a; }
.req-status-card.rejected { background:#fef2f2; border-color:#fca5a5; }
.req-status-card.approved { background:#f0fdf4; border-color:#86efac; }
.req-status-title { font-size:14px; font-weight:700; margin-bottom:4px; }
.req-status-title.pending  { color:#92400e; }
.req-status-title.rejected { color:#b91c1c; }
.req-status-title.approved { color:#15803d; }
.req-status-desc { font-size:12.5px; color:var(--text-muted); line-height:1.6; }

.history-item {
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  padding:12px 0; border-bottom:1px solid var(--border);
}
.history-item:last-child { border-bottom:none; }
.history-name { font-size:13px; font-weight:600; color:var(--text); }
.history-date { font-size:11px; color:var(--text-muted); margin-top:2px; }
.st-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; flex-shrink:0; }
.st-pending  { background:#fef9c3; color:#92400e; }
.st-approved { background:#dcfce7; color:#16a34a; }
.st-rejected { background:#fee2e2; color:#dc2626; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

<div class="info-note" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12.5px;color:#1e40af;display:flex;gap:10px;align-items:flex-start;">
  <span style="font-size:16px;">ℹ️</span>
  <span>
    Gunakan formulir ini jika Anda magang di perusahaan yang <strong>belum terdaftar</strong> di SITAMA.
    Kaprodi akan membuatkan akun perusahaan dan akun pembimbing industri berdasarkan data yang Anda
    berikan di sini.
  </span>
</div>

@php $latest = $requests->first(); @endphp

@if($hasPending)
  @php $p = $requests->firstWhere('status', 'pending'); @endphp
  <div class="req-status-card pending">
    <div class="req-status-title pending">⏳ Menunggu Peninjauan Kaprodi</div>
    <div class="req-status-desc">
      Pengajuan untuk <strong>{{ $p->company_name }}</strong> (PIC: {{ $p->pic_name }}) sedang ditinjau.
      Anda akan bisa mengajukan permintaan baru lagi setelah pengajuan ini diproses.
    </div>
  </div>
@else
  @if($latest && $latest->status === 'rejected')
    <div class="req-status-card rejected">
      <div class="req-status-title rejected">✕ Pengajuan Sebelumnya Ditolak</div>
      <div class="req-status-desc">
        Pengajuan untuk <strong>{{ $latest->company_name }}</strong> ditolak Kaprodi.
        @if($latest->rejection_reason)
          <br>Alasan: {{ $latest->rejection_reason }}
        @endif
        <br>Anda dapat mengajukan kembali dengan data yang sudah diperbaiki di bawah ini.
      </div>
    </div>
  @elseif($latest && $latest->status === 'approved')
    <div class="req-status-card approved">
      <div class="req-status-title approved">✓ Pengajuan Sebelumnya Disetujui</div>
      <div class="req-status-desc">
        Akun untuk <strong>{{ $latest->company_name }}</strong> sudah dibuatkan oleh Kaprodi.
        Jika Anda perlu mengajukan perusahaan lain, silakan isi formulir di bawah ini.
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('mahasiswa.industri-request.store') }}">
    @csrf

    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">Data Perusahaan</div>
      <div class="grid-2" style="margin-bottom:0;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Nama Perusahaan</label>
          <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Contoh: PT Maju Bersama" required>
          @error('company_name') <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Bidang Usaha</label>
          <input type="text" name="company_field" value="{{ old('company_field') }}" placeholder="Contoh: Teknologi Informasi">
        </div>
        <div class="form-group">
          <label>Telepon Perusahaan</label>
          <input type="text" name="company_phone" value="{{ old('company_phone') }}" placeholder="08xxxxxxxxxx">
        </div>
        <div class="form-group">
          <label>Email Perusahaan</label>
          <input type="email" name="company_email" value="{{ old('company_email') }}" placeholder="info@perusahaan.com">
          @error('company_email') <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Alamat Perusahaan</label>
          <input type="text" name="company_address" value="{{ old('company_address') }}" placeholder="Alamat lengkap perusahaan">
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-title" style="margin-bottom:14px;">Data Pembimbing Industri</div>
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;margin-top:-6px;">
        Pembimbing industri adalah karyawan perusahaan yang membimbing & menilai Anda selama magang.
      </p>
      <div class="grid-2" style="margin-bottom:0;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Nama Pembimbing Industri</label>
          <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Nama lengkap pembimbing" required>
          @error('pic_name') <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>Email Pembimbing</label>
          <input type="email" name="pic_email" value="{{ old('pic_email') }}" placeholder="nama@perusahaan.com">
          @error('pic_email') <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
          <label>No. HP Pembimbing</label>
          <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" placeholder="08xxxxxxxxxx">
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
      <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
    </div>
  </form>
@endif

@if($requests->isNotEmpty())
<div class="card" style="margin-top:16px;">
  <div class="card-title" style="margin-bottom:8px;">Riwayat Pengajuan</div>
  @foreach($requests as $req)
  <div class="history-item">
    <div>
      <div class="history-name">{{ $req->company_name }} <span style="color:var(--text-muted);font-weight:500;">· PIC: {{ $req->pic_name }}</span></div>
      <div class="history-date">{{ $req->created_at->format('d M Y, H:i') }}</div>
    </div>
    @php
      $stClass = match($req->status) {
        'approved' => 'st-approved', 'rejected' => 'st-rejected', default => 'st-pending',
      };
      $stLabel = match($req->status) {
        'approved' => '✓ Disetujui', 'rejected' => '✕ Ditolak', default => '⏳ Menunggu',
      };
    @endphp
    <span class="st-badge {{ $stClass }}">{{ $stLabel }}</span>
  </div>
  @endforeach
</div>
@endif

@endsection
