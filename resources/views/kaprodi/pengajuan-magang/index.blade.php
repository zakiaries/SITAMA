@extends('layouts.kaprodi')
@section('title', 'Pengajuan Magang')
@php $title = 'Pengajuan Magang'; @endphp
@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Kredensial akun baru --}}
@if(session('new_pic_credentials'))
  @php $cred = session('new_pic_credentials'); @endphp
  <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:16px;margin-bottom:16px;">
    <div style="font-weight:700;color:#15803d;margin-bottom:4px;">Akun Pembimbing Industri Berhasil Dibuat</div>
    <div style="font-size:12px;color:#166534;margin-bottom:10px;">Sampaikan kredensial berikut ke pembimbing industri. Ditampilkan hanya sekali.</div>
    <div style="font-size:13px;color:#14532d;display:flex;flex-direction:column;gap:4px;">
      <div><strong>Nama:</strong> {{ $cred['name'] }}</div>
      <div><strong>Username:</strong> <code style="background:#dcfce7;padding:1px 6px;border-radius:4px;">{{ $cred['username'] }}</code></div>
      <div><strong>Password:</strong> <code style="background:#dcfce7;padding:1px 6px;border-radius:4px;">{{ $cred['password'] }}</code></div>
    </div>
  </div>
@endif

{{-- Tab filter --}}
<div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border);">
  @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
  <a href="{{ route('kaprodi.pengajuan-magang.index', ['status' => $key]) }}"
     style="padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid {{ $status === $key ? 'var(--primary)' : 'transparent' }};
            font-size:13px;font-weight:700;color:{{ $status === $key ? 'var(--primary)' : 'var(--text-muted)' }};
            text-decoration:none;display:flex;align-items:center;gap:8px;">
    {{ $label }}
    @if($counts[$key] > 0)
      <span style="background:{{ $status === $key ? 'var(--primary-light)' : '#f1f5f9' }};color:{{ $status === $key ? 'var(--primary-text)' : 'var(--text-muted)' }};font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">
        {{ $counts[$key] }}
      </span>
    @endif
  </a>
  @endforeach
</div>

@forelse($requests as $req)
@php $student = $req->student; @endphp
<div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;margin-bottom:14px;">

  {{-- Header --}}
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $student->user->name ?? '-' }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        {{ $student->nim ?? $student->username ?? '-' }} &middot; {{ $student->major ?? '-' }}
      </div>
    </div>
    <div style="font-size:11px;color:var(--text-muted);">{{ $req->created_at->format('d M Y H:i') }}</div>
  </div>

  {{-- Detail grid --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Data Perusahaan</div>
      <div style="font-size:13px;color:var(--text);display:flex;flex-direction:column;gap:4px;">
        <div><strong>Nama:</strong> {{ $req->company_name }}</div>
        @if($req->company_id)
          <div style="font-size:11.5px;color:#16a34a;">✓ Sudah terdaftar di sistem</div>
        @else
          <div style="font-size:11.5px;color:#f59e0b;">Perusahaan baru — akan didaftarkan saat disetujui</div>
        @endif
        <div><strong>Posisi:</strong> {{ $req->position ?? '-' }}</div>
        <div><strong>Mulai:</strong> {{ $req->start_date?->format('d M Y') ?? '-' }}</div>
      </div>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Data Pembimbing Industri</div>
      <div style="font-size:13px;color:var(--text);display:flex;flex-direction:column;gap:4px;">
        <div><strong>Nama:</strong> {{ $req->pic_name }}</div>
        @if($req->pic_phone)<div><strong>HP:</strong> {{ $req->pic_phone }}</div>@endif
        @if($req->pic_email)<div><strong>Email:</strong> {{ $req->pic_email }}</div>@endif
      </div>
    </div>
  </div>

  {{-- Bukti --}}
  @if($req->proof_file)
    <div style="margin-bottom:14px;">
      <a href="{{ Storage::url($req->proof_file) }}" target="_blank"
        class="btn btn-outline btn-sm">
        📄 Lihat Bukti Penerimaan Magang
      </a>
    </div>
  @endif

  {{-- Aksi --}}
  @if($req->status === 'pending')
  <div style="display:flex;gap:10px;padding-top:14px;border-top:1px solid var(--border);">
    <button type="button" class="btn btn-primary btn-sm"
      onclick="openApprove({{ $req->id }}, '{{ addslashes($req->pic_name) }}')">
      ✓ Setujui
    </button>
    <button type="button" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;"
      onclick="openReject({{ $req->id }})">
      ✕ Tolak
    </button>
  </div>
  @elseif($req->status === 'rejected')
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 12px;font-size:12px;color:#991b1b;">
      <strong>Ditolak:</strong> {{ $req->rejection_reason }}
    </div>
  @elseif($req->status === 'approved')
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:10px;border-top:1px solid var(--border);flex-wrap:wrap;">
      <div style="font-size:12px;color:#16a34a;">
        ✓ Disetujui &middot; Pembimbing industri:
        <strong>{{ $req->createdLecturer?->user?->name ?? '-' }}</strong>
        @if($req->createdLecturer?->user?->username)
          <code style="background:#dcfce7;color:#15803d;font-size:11px;padding:1px 7px;border-radius:6px;margin-left:4px;">
            {{ $req->createdLecturer->user->username }}
          </code>
        @endif
      </div>
      @if($req->createdLecturer)
        <a href="{{ route('kaprodi.dosen.detail', $req->createdLecturer) }}"
           class="btn btn-outline btn-sm" style="flex-shrink:0;">
          Kelola Akun →
        </a>
      @endif
    </div>
  @endif

</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="font-size:40px;margin-bottom:12px;">📋</div>
  <p style="font-weight:600;color:var(--text);">Tidak ada pengajuan {{ $status === 'pending' ? 'yang menunggu' : ($status === 'approved' ? 'disetujui' : 'ditolak') }}</p>
</div>
@endforelse

{{-- Modal Setujui --}}
<div id="modal-approve" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:440px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:4px;">Setujui Pengajuan</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;">Buat akun pembimbing industri. Catat username & password sebelum menyimpan.</p>
    <form id="form-approve" method="POST">
      @csrf
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Username <span style="color:#dc2626;">*</span></label>
          <input id="approve-username" type="text" name="username" required maxlength="50"
            style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">Hanya huruf, angka, dan underscore. Digunakan untuk login.</div>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Password <span style="color:#dc2626;">*</span></label>
          <input type="password" name="password" required minlength="6"
            style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;"
            placeholder="Minimal 6 karakter">
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Konfirmasi Password <span style="color:#dc2626;">*</span></label>
          <input type="password" name="password_confirmation" required minlength="6"
            style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:16px;">
        <button type="submit" class="btn btn-primary btn-sm">✓ Setujui & Buat Akun</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeApprove()">Batal</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Tolak --}}
<div id="modal-reject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:420px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:12px;">Tolak Pengajuan</div>
    <form id="form-reject" method="POST">
      @csrf
      <textarea name="rejection_reason" rows="4" required placeholder="Tulis alasan penolakan..."
        style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      <div style="display:flex;gap:10px;margin-top:14px;">
        <button type="submit" class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;">Tolak</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeReject()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 20);
}
function openApprove(id, picName) {
  document.getElementById('form-approve').action = '/kaprodi/pengajuan-magang/' + id + '/approve';
  document.getElementById('approve-username').value = slugify(picName);
  document.getElementById('modal-approve').style.display = 'flex';
}
function closeApprove() {
  document.getElementById('modal-approve').style.display = 'none';
}
function openReject(id) {
  document.getElementById('form-reject').action = '/kaprodi/pengajuan-magang/' + id + '/reject';
  document.getElementById('modal-reject').style.display = 'flex';
}
function closeReject() {
  document.getElementById('modal-reject').style.display = 'none';
}
</script>
@endsection
