@extends('layouts.kaprodi')
@section('title', 'Pengajuan Seminar')
@php $title = 'Pengajuan Seminar'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Tab filter --}}
<div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border);">
  @foreach(['pending' => 'Menunggu', 'scheduled' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
  <a href="{{ route('kaprodi.seminar.index', ['status' => $key]) }}"
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

@forelse($seminars as $seminar)
@php $student = $seminar->student; @endphp
<div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;margin-bottom:14px;">

  {{-- Header --}}
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $seminar->title }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        {{ $student->user->name ?? '-' }} &middot; {{ $student->user->username ?? '-' }} &middot; {{ $student->major ?? '-' }}
      </div>
    </div>
    <div style="font-size:11px;color:var(--text-muted);">Diajukan {{ $seminar->created_at->format('d M Y H:i') }}</div>
  </div>

  {{-- Detail jadwal --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:14px;">
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Tanggal</div>
      <div style="font-size:13px;color:var(--text);">{{ $seminar->date?->format('d M Y') ?? '-' }}</div>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Waktu</div>
      <div style="font-size:13px;color:var(--text);">{{ $seminar->time ?? '-' }}</div>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Ruang</div>
      <div style="font-size:13px;color:var(--text);">{{ $seminar->location ?? '-' }}</div>
    </div>
  </div>

  @if($seminar->description)
  <div style="margin-bottom:14px;font-size:13px;color:var(--text);">
    <span style="color:var(--text-muted);">Deskripsi:</span> {{ $seminar->description }}
  </div>
  @endif

  {{-- Aksi --}}
  @if($seminar->status === 'pending')
  <div style="display:flex;gap:10px;padding-top:14px;border-top:1px solid var(--border);">
    <form method="POST" action="{{ route('kaprodi.seminar.approve', $seminar) }}"
          data-confirm="Setujui jadwal seminar {{ $student->user->name ?? '' }} pada {{ $seminar->date?->format('d M Y') }}?">
      @csrf
      <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="14"/> Setujui Jadwal</button>
    </form>
    <button type="button" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"
      onclick="openReject({{ $seminar->id }})"><x-icon name="x" :size="14"/> Tolak</button>
  </div>
  @elseif($seminar->status === 'rejected')
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;border-radius:8px;padding:10px 12px;font-size:12px;color:var(--danger);">
      <strong>Ditolak:</strong> {{ $seminar->rejection_reason }}
    </div>
  @elseif($seminar->status === 'scheduled')
    @php $guests = $seminar->attendances->count(); $min = \App\Models\Seminar::MIN_GUESTS; @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:10px;border-top:1px solid var(--border);flex-wrap:wrap;">
      <div style="font-size:12px;color:var(--success-text);display:flex;align-items:center;gap:8px;">
        <x-icon name="check" :size="12"/> Disetujui &middot; berita acara aktif
      </div>
      <div style="font-size:12px;font-weight:700;color:{{ $guests >= $min ? 'var(--success-text)' : 'var(--warn-text)' }};">
        Tamu terisi: {{ $guests }}/{{ $min }}
      </div>
    </div>
  @endif

</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;display:flex;justify-content:center;"><x-icon name="cap" :size="40"/></div>
  <p style="font-weight:600;color:var(--text);">Tidak ada pengajuan seminar {{ $status === 'pending' ? 'yang menunggu' : ($status === 'scheduled' ? 'yang disetujui' : 'yang ditolak') }}</p>
</div>
@endforelse

{{-- Modal Tolak --}}
<div id="modal-reject" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:420px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:6px;">Tolak Jadwal Seminar</div>
    <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Mahasiswa akan diminta mengajukan jadwal baru.</div>
    <form id="form-reject" method="POST">
      @csrf
      <textarea name="rejection_reason" rows="4" required placeholder="Contoh: ruang bentrok pada tanggal tsb, silakan ajukan tanggal lain."
        style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      <div style="display:flex;gap:10px;margin-top:14px;">
        <button type="submit" class="btn btn-sm" style="background:var(--danger);color:#fff;border:none;">Tolak Jadwal</button>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeReject()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReject(id) {
  document.getElementById('form-reject').action = '/kaprodi/seminar/' + id + '/reject';
  document.getElementById('modal-reject').style.display = 'flex';
}
function closeReject() {
  document.getElementById('modal-reject').style.display = 'none';
}
</script>
@endsection
