@extends('layouts.dosen')
@section('title', 'Seminar Bimbingan')
@php $title = 'Seminar Bimbingan'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
  <div>
    <div class="page-title">Seminar Bimbingan</div>
    <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
      Buat sesi seminar untuk mahasiswa bimbingan yang sudah selesai magang. Mereka maju bergantian dalam satu sesi.
    </div>
  </div>
  @if($eligibleStudents->isNotEmpty())
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-buat').classList.add('open')">+ Buat Sesi Seminar</button>
  @endif
</div>

@if($eligibleStudents->isEmpty() && $seminars->isEmpty())
  <div class="card" style="text-align:center;padding:40px 20px;color:var(--text-muted);">
    <p style="margin:0;">Belum ada mahasiswa bimbingan yang selesai magang. Sesi seminar bisa dibuat setelah ada mahasiswa yang magangnya ditandai selesai.</p>
  </div>
@endif

@foreach($seminars as $s)
@php
  $stMap = [
    'draft'     => ['Menunggu Ketersediaan', 'var(--warn-bg)', 'var(--warn-text)'],
    'scheduled' => ['Terjadwal', 'var(--blue-tint)', 'var(--primary)'],
    'completed' => ['Selesai (Disahkan)', 'var(--success-bg)', 'var(--success-text)'],
    'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
  ];
  $st = $stMap[$s->status] ?? [$s->status, 'var(--warm-2)', 'var(--text-secondary)'];
  $guests = $s->attendances->count();
@endphp
<div class="card" style="margin-bottom:16px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $s->title }}</div>
      @if($s->date)
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
          {{ $s->date->format('d M Y') }}{{ $s->time ? ' · '.$s->time : '' }}{{ $s->location ? ' · '.$s->location : '' }}
        </div>
      @endif
    </div>
    <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
  </div>

  {{-- Penyaji + ketersediaan --}}
  <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Penyaji ({{ $s->presenters->count() }})</div>
  <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
    @foreach($s->presenters as $p)
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:8px 10px;background:var(--warm);border-radius:8px;">
      <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $p->student->user->name ?? '-' }}</div>
      <div style="font-size:12px;color:{{ $p->responded_at ? 'var(--text-secondary)' : 'var(--text-muted)' }};">
        @if($p->responded_at)
          Bisa: {{ $p->available_dates }}
        @else
          <em>belum mengisi ketersediaan</em>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  {{-- Aksi per status --}}
  @if($s->status === 'draft')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">Tetapkan Jadwal Final</div>
      <form method="POST" action="{{ route('dosen.seminar.finalize', $s) }}">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:10px;">
          <input type="date" name="date" min="{{ now()->toDateString() }}" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <input type="text" name="time" placeholder="Waktu (mis. 09:00-11:00)" style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <input type="text" name="location" placeholder="Ruang/tempat" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Tetapkan &amp; Jadwalkan</button>
      </form>
      <form method="POST" action="{{ route('dosen.seminar.destroy', $s) }}" style="margin-top:8px;" onsubmit="return confirm('Batalkan sesi ini?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;">Batalkan Sesi</button>
      </form>
    </div>

  @elseif($s->status === 'scheduled')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <div style="font-size:12px;color:var(--text-secondary);">
          Daftar hadir audiens:
          <strong style="color:{{ $guests >= \App\Models\Seminar::MIN_GUESTS ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ \App\Models\Seminar::MIN_GUESTS }}</strong>
        </div>
        @if($s->access_token)
          <a href="{{ url('/seminar/hadir/'.$s->access_token) }}" target="_blank" style="font-size:12px;color:var(--primary);font-weight:600;">Buka halaman daftar hadir (QR) →</a>
        @endif
      </div>
      <form method="POST" action="{{ route('dosen.seminar.sahkan', $s) }}"
            data-confirm="Sahkan bahwa seminar ini telah berlangsung dan selesai?">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm" {{ $guests >= \App\Models\Seminar::MIN_GUESTS ? '' : 'disabled title=\'Audiens belum mencapai minimal\' style=opacity:.55;cursor:not-allowed;' }}>
          <x-icon name="check" :size="14"/> Sahkan Seminar (Saksi)
        </button>
      </form>
    </div>

  @elseif($s->status === 'completed')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;font-size:12px;color:var(--success-text);">
      <x-icon name="check" :size="13"/> Disahkan {{ $s->witnessed_at?->format('d M Y H:i') }} · Audiens hadir: {{ $guests }}
    </div>
  @endif
</div>
@endforeach

{{-- Modal Buat Sesi --}}
<div class="modal-overlay" id="modal-buat" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Buat Sesi Seminar</div>
      <button class="modal-close" onclick="document.getElementById('modal-buat').classList.remove('open')"><x-icon name="x" :size="14"/></button>
    </div>
    <form method="POST" action="{{ route('dosen.seminar.store') }}">
      @csrf
      <div class="form-group">
        <label>Judul Sesi</label>
        <input type="text" name="title" value="Seminar Hasil Magang" required>
      </div>
      <div class="form-group">
        <label>Pilih Mahasiswa Penyaji (sudah selesai magang)</label>
        <div style="display:flex;flex-direction:column;gap:6px;max-height:240px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:10px;">
          @foreach($eligibleStudents as $st)
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
            <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" checked>
            {{ $st->user->name ?? '-' }} <span style="color:var(--text-muted);">· {{ $st->user->username ?? '' }}</span>
          </label>
          @endforeach
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-buat').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Buat Sesi</button>
      </div>
    </form>
  </div>
</div>

@endsection
