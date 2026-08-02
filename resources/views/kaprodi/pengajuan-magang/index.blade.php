@extends('layouts.kaprodi')
@section('title', 'Pengajuan Magang')
@php $title = 'Pengajuan Magang'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Info aktivasi setelah approve --}}
@if(session('activation_info'))
  @php $act = session('activation_info'); @endphp
  <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:16px;margin-bottom:16px;">
    <div style="font-weight:700;color:#15803d;margin-bottom:6px;">Pengajuan Disetujui — Link Aktivasi Dibuat</div>
    <div style="font-size:13px;color:#166534;margin-bottom:10px;">{{ $act['mail_status'] }}</div>
    <div style="font-size:12px;color:#14532d;margin-bottom:8px;">
      <strong>Pembimbing:</strong> {{ $act['pic_name'] }} &middot; {{ $act['pic_email'] }}
    </div>
    <div style="font-size:11.5px;color:#166534;margin-bottom:6px;">Link aktivasi (bagikan jika email gagal):</div>
    <div style="background:#dcfce7;border-radius:6px;padding:8px 12px;font-size:12px;word-break:break-all;display:flex;align-items:center;gap:10px;">
      <code style="flex:1;">{{ $act['activation_url'] }}</code>
      <button type="button" onclick="navigator.clipboard.writeText('{{ $act['activation_url'] }}')"
        style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;flex-shrink:0;">
        Salin
      </button>
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
      <span style="background:{{ $status === $key ? 'var(--primary-light)' : 'var(--warm)' }};color:{{ $status === $key ? 'var(--primary-text)' : 'var(--text-muted)' }};font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">
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
          <div style="font-size:11.5px;color:var(--success-text);"><x-icon name="check" :size="12"/> Sudah terdaftar di sistem</div>
        @else
          <div style="font-size:11.5px;color:var(--warning);">Perusahaan baru — akan didaftarkan saat disetujui</div>
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
        <x-icon name="doc" :size="14"/> Lihat Bukti Penerimaan Magang
      </a>
    </div>
  @endif

  {{-- Aksi --}}
  @if($req->status === 'pending')
  <div style="display:flex;gap:10px;padding-top:14px;border-top:1px solid var(--border);">
    <form method="POST" action="{{ route('kaprodi.pengajuan-magang.approve', $req) }}"
          data-confirm="Setujui pengajuan magang {{ $student->user->name }}? Link aktivasi akan dikirim ke email pembimbing industri.">
      @csrf
      <button type="submit" class="btn btn-primary btn-sm">
        <x-icon name="check" :size="14"/> Setujui & Kirim Link Aktivasi
      </button>
    </form>
    <button type="button" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"
      onclick="openReject({{ $req->id }})">
      <x-icon name="x" :size="14"/> Tolak
    </button>
  </div>
  @elseif($req->status === 'rejected')
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;border-radius:8px;padding:10px 12px;font-size:12px;color:var(--danger);">
      <strong>Ditolak:</strong> {{ $req->rejection_reason }}
    </div>
  @elseif($req->status === 'approved')
    @php $activated = $req->createdLecturer?->user?->is_activated ?? false; @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:10px;border-top:1px solid var(--border);flex-wrap:wrap;">
      <div style="font-size:12px;color:var(--success-text);display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <x-icon name="check" :size="12"/> Disetujui &middot;
        <strong>{{ $req->createdLecturer?->user?->name ?? '-' }}</strong>
        @if($activated)
          <span style="background:#dcfce7;color:#15803d;font-size:11px;padding:2px 8px;border-radius:12px;font-weight:600;">Akun Aktif</span>
        @else
          <span style="background:#fef9c3;color:#92400e;font-size:11px;padding:2px 8px;border-radius:12px;font-weight:600;">Menunggu Aktivasi</span>
        @endif
      </div>
      <div style="display:flex;gap:8px;flex-shrink:0;">
        @if(!$activated && $req->createdLecturer)
          <form method="POST" action="{{ route('kaprodi.pengajuan-magang.resend', $req) }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">Kirim Ulang Link</button>
          </form>
        @endif
        @if($req->createdLecturer)
          <a href="{{ route('kaprodi.dosen.detail', $req->createdLecturer) }}" class="btn btn-outline btn-sm">
            Kelola Akun <x-icon name="arrow-right" :size="14"/>
          </a>
        @endif
      </div>
    </div>
  @endif

</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="clipboard" :size="40"/></div>
  <p style="font-weight:600;color:var(--text);">Tidak ada pengajuan {{ $status === 'pending' ? 'yang menunggu' : ($status === 'approved' ? 'disetujui' : 'ditolak') }}</p>
</div>
@endforelse

{{-- Modal Tolak --}}
<div id="modal-reject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:420px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:12px;">Tolak Pengajuan</div>
    <form id="form-reject" method="POST">
      @csrf
      <textarea name="rejection_reason" rows="4" required placeholder="Tulis alasan penolakan..."
        style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      <div style="display:flex;gap:10px;margin-top:14px;">
        <button type="submit" class="btn btn-sm" style="background:var(--danger);color:#fff;border:none;">Tolak</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeReject()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReject(id) {
  document.getElementById('form-reject').action = '/kaprodi/pengajuan-magang/' + id + '/reject';
  document.getElementById('modal-reject').style.display = 'flex';
}
function closeReject() {
  document.getElementById('modal-reject').style.display = 'none';
}
</script>
@endsection
