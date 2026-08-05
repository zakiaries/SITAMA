@extends('layouts.mahasiswa')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif
@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ $errors->first() }}</div>
@endif

<div class="page-header">
  <div>
    <div class="page-title">Seminar Hasil Magang</div>
    <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
      Dosen pembimbing membuat sesi seminar dan menetapkanmu sebagai penyaji. Isi ketersediaan tanggalmu, lalu pantau jadwal finalnya di sini.
    </div>
  </div>
</div>

@forelse($presenterRows as $row)
@php
  $s = $row->seminar;
  $stMap = [
    'draft'     => ['Menunggu Jadwal (isi ketersediaan)', 'var(--warn-bg)', 'var(--warn-text)'],
    'scheduled' => ['Terjadwal', 'var(--blue-tint)', 'var(--primary)'],
    'completed' => ['Selesai (Disahkan Dosen)', 'var(--success-bg)', 'var(--success-text)'],
    'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
  ];
  $st = $stMap[$s->status] ?? [$s->status, 'var(--warm-2)', 'var(--text-secondary)'];
  $guests = $s->attendances->count();
@endphp
<div class="card" style="margin-bottom:16px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $s->title }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Dosen: {{ $s->lecturer->user->name ?? '-' }}</div>
    </div>
    <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
  </div>

  @if($s->status === 'draft')
    <form method="POST" action="{{ route('mahasiswa.seminar.availability', $s->id) }}" style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      @csrf
      <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Tanggal yang kamu bisa</label>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" name="available_dates" value="{{ $row->available_dates }}" required
          placeholder="Contoh: 12, 15, atau 18 Agustus 2026"
          style="flex:1;min-width:220px;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        <button type="submit" class="btn btn-primary btn-sm">{{ $row->responded_at ? 'Perbarui' : 'Kirim' }}</button>
      </div>
      @if($row->responded_at)
        <div style="font-size:11.5px;color:var(--success-text);margin-top:6px;"><x-icon name="check" :size="11"/> Terkirim {{ $row->responded_at->format('d M Y H:i') }} — dosen akan menetapkan tanggal final.</div>
      @else
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:6px;">Isi tanggal yang kamu bisa; dosen akan memilih tanggal final dari ketersediaan semua penyaji.</div>
      @endif
    </form>

  @elseif($s->status === 'scheduled')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      <div class="sem-meta" style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:10px;">
        <div><div style="font-size:11px;color:var(--text-muted);">Tanggal</div><div style="font-size:13px;font-weight:600;">{{ $s->date?->format('d M Y') ?? '-' }}</div></div>
        <div><div style="font-size:11px;color:var(--text-muted);">Waktu</div><div style="font-size:13px;font-weight:600;">{{ $s->time ?? '-' }}</div></div>
        <div><div style="font-size:11px;color:var(--text-muted);">Ruang</div><div style="font-size:13px;font-weight:600;">{{ $s->location ?? '-' }}</div></div>
        <div><div style="font-size:11px;color:var(--text-muted);">Audiens</div><div style="font-size:13px;font-weight:600;color:{{ $guests >= $s->minGuests() ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ $s->minGuests() }}</div></div>
      </div>
      <a href="{{ route('mahasiswa.seminar.detail', $s->id) }}" class="btn btn-primary btn-sm"><x-icon name="qr" :size="14"/> QR & Daftar Hadir</a>
    </div>

  @elseif($s->status === 'completed')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;font-size:12.5px;color:var(--success-text);">
      <x-icon name="check" :size="13"/> Seminar telah disahkan dosen pada {{ $s->witnessed_at?->format('d M Y H:i') }}. Audiens hadir: {{ $guests }}.
      <a href="{{ route('mahasiswa.seminar.detail', $s->id) }}" style="color:var(--primary);margin-left:8px;">Lihat detail</a>
    </div>
  @endif
</div>
@empty
<div class="card" style="text-align:center;padding:40px 20px;color:var(--text-muted);">
  <p style="margin:0;">Belum ada sesi seminar untukmu. Dosen pembimbing akan membuat sesi setelah magangmu ditandai selesai — kamu akan dapat notifikasi untuk mengisi ketersediaan tanggal.</p>
</div>
@endforelse

@endsection
