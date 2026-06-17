@extends('layouts.mahasiswa')
@section('title', 'Ajukan Magang')
@php $title = 'Ajukan Magang'; @endphp
@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Info / blocker --}}
@if($hasActiveInternship)
  <div class="card" style="border-left:4px solid #16a34a;">
    <div style="font-weight:700;color:#16a34a;margin-bottom:6px;">Kamu sudah memiliki magang aktif</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">Pengajuan magang baru tidak diperlukan. Pantau status magangmu di <a href="{{ route('mahasiswa.magang-saya') }}" style="color:var(--primary);">Magang Saya</a>.</p>
  </div>

@elseif($hasPending)
  <div class="card" style="border-left:4px solid #f59e0b;">
    <div style="font-weight:700;color:#854f0b;margin-bottom:6px;">Pengajuan sedang diproses</div>
    <p style="font-size:13px;color:var(--text-muted);margin:0;">Kaprodi sedang mereview pengajuanmu. Kamu tidak bisa mengajukan lagi sampai pengajuan sebelumnya selesai diproses.</p>
  </div>

@else
  {{-- Form Ajukan Magang --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-title" style="margin-bottom:4px;">Ajukan Magang</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px;">
      Isi data magang dan upload bukti penerimaan. Kaprodi akan mereview dan menghubungkan ke sistem.
    </p>

    <form method="POST" action="{{ route('mahasiswa.ajukan-magang.store') }}" enctype="multipart/form-data">
      @csrf

      {{-- BUKTI PENERIMAAN --}}
      <div class="form-group" style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">
          Bukti Penerimaan Magang <span style="color:#dc2626;">*</span>
        </label>
        <input type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required
          style="width:100%;padding:8px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;">Surat penerimaan, screenshot konfirmasi, atau dokumen sejenis (PDF/JPG/PNG, maks 10 MB).</div>
        @error('proof_file')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      {{-- PERUSAHAAN --}}
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">
        Perusahaan <span style="color:#dc2626;">*</span>
      </div>
      <div style="display:flex;gap:16px;margin-bottom:6px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="existing" checked onchange="toggleCompany(this.value)"> Sudah terdaftar di SITAMA
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="new" onchange="toggleCompany(this.value)"> Belum terdaftar
        </label>
      </div>
      <div id="co-existing" style="margin-bottom:14px;">
        <select name="company_id" id="company_id_sel" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih perusahaan —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
        @error('company_id')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>
      <div id="co-new" style="display:none;margin-bottom:14px;">
        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Nama perusahaan"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        @error('company_name')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      {{-- PEMBIMBING INDUSTRI --}}
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">
        Data Pembimbing Industri <span style="color:#dc2626;">*</span>
      </div>
      <div style="background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:14px;display:flex;flex-direction:column;gap:8px;">
        <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Nama lengkap pembimbing *" required
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" placeholder="No. HP / WhatsApp (opsional)"
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <input type="email" name="pic_email" value="{{ old('pic_email') }}" placeholder="Email (opsional)"
          style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        @error('pic_name')<div style="color:#dc2626;font-size:12px;">{{ $message }}</div>@enderror
      </div>

      {{-- POSISI & TANGGAL --}}
      <div class="form-group" style="margin-bottom:14px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Posisi / Bidang</label>
        <input type="text" name="position" value="{{ old('position') }}" placeholder="Contoh: Backend Developer"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>
      <div class="form-group" style="margin-bottom:18px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">
          Tanggal Mulai <span style="color:#dc2626;">*</span>
        </label>
        <input type="date" name="start_date" value="{{ old('start_date') }}" required
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        @error('start_date')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
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
    'approved' => ['bg' => '#dcfce7', 'color' => '#16a34a', 'label' => 'Disetujui'],
    'rejected' => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Ditolak'],
    default    => ['bg' => '#fef9c3', 'color' => '#854f0b', 'label' => 'Menunggu'],
  };
@endphp
<div class="card" style="margin-bottom:12px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
    <div>
      <div style="font-weight:700;font-size:14px;color:var(--text);">{{ $req->company_name }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        {{ $req->position ?? '-' }} &middot; Mulai {{ $req->start_date?->format('d M Y') ?? '-' }}
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
      <a href="{{ Storage::url($req->proof_file) }}" target="_blank"
        style="font-size:12px;color:var(--primary);text-decoration:none;">
        📄 Lihat Bukti Penerimaan
      </a>
    </div>
  @endif
  @if($req->status === 'rejected' && $req->rejection_reason)
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:12px;color:#991b1b;">
      <strong>Alasan ditolak:</strong> {{ $req->rejection_reason }}
    </div>
  @endif
  @if($req->status === 'approved' && $req->createdLecturer)
    <div style="font-size:12px;color:#16a34a;margin-top:8px;">
      ✓ Magang berhasil dicatat &middot; Pembimbing industri: {{ $req->createdLecturer->user->name ?? '-' }}
    </div>
  @endif
  <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">Diajukan {{ $req->created_at->format('d M Y H:i') }}</div>
</div>
@endforeach
@endif

<script>
function toggleCompany(mode) {
  document.getElementById('co-existing').style.display = mode === 'existing' ? 'block' : 'none';
  document.getElementById('co-new').style.display      = mode === 'new'      ? 'block' : 'none';
  document.getElementById('company_id_sel').required   = mode === 'existing';
}
</script>
@endsection
