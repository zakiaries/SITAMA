@extends('layouts.kaprodi')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
@section('content')

<div class="page-header">
  <div>
    <div class="page-title">Seminar Magang</div>
    <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
      Pemantauan sesi seminar. Penjadwalan &amp; pengesahan dilakukan dosen pembimbing.
    </div>
  </div>
</div>

{{-- Tab filter --}}
<div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border);">
  @foreach(['all' => 'Semua', 'draft' => 'Menunggu Jadwal', 'scheduled' => 'Terjadwal', 'completed' => 'Selesai'] as $key => $label)
  <a href="{{ route('kaprodi.seminar.index', ['status' => $key]) }}"
     style="padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid {{ $status === $key ? 'var(--primary)' : 'transparent' }};
            font-size:13px;font-weight:700;color:{{ $status === $key ? 'var(--primary)' : 'var(--text-muted)' }};
            text-decoration:none;display:flex;align-items:center;gap:8px;">
    {{ $label }}
    <span style="background:{{ $status === $key ? 'var(--primary-light)' : 'var(--warm)' }};color:{{ $status === $key ? 'var(--primary-text)' : 'var(--text-muted)' }};font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">
      {{ $counts[$key] }}
    </span>
  </a>
  @endforeach
</div>

@forelse($seminars as $seminar)
@php
  $stMap = [
    'draft'     => ['Menunggu Jadwal', 'var(--warn-bg)', 'var(--warn-text)'],
    'scheduled' => ['Terjadwal', 'var(--blue-tint)', 'var(--primary)'],
    'completed' => ['Selesai (Disahkan)', 'var(--success-bg)', 'var(--success-text)'],
    'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
  ];
  $st = $stMap[$seminar->status] ?? [$seminar->status, 'var(--warm-2)', 'var(--text-secondary)'];
  $guests = $seminar->attendances->count();
@endphp
<div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;margin-bottom:14px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $seminar->title }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
        Dosen: {{ $seminar->lecturer->user->name ?? '-' }}
        @if($seminar->date) &middot; {{ $seminar->date->format('d M Y') }}{{ $seminar->time ? ', '.$seminar->time : '' }}@endif
        @if($seminar->location) &middot; {{ $seminar->location }}@endif
      </div>
    </div>
    <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
  </div>

  <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
    @foreach($seminar->presenters as $p)
      <span style="background:var(--warm-2);color:var(--text);font-size:12px;padding:3px 10px;border-radius:20px;">{{ $p->student->user->name ?? '-' }}</span>
    @endforeach
  </div>

  <div style="font-size:12px;color:var(--text-secondary);">
    Audiens hadir: <strong style="color:{{ $guests >= $seminar->minGuests() ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ $seminar->minGuests() }}</strong>
    @if($seminar->status === 'completed') &middot; disahkan {{ $seminar->witnessed_at?->format('d M Y H:i') }}@endif
  </div>
</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;display:flex;justify-content:center;"><x-icon name="cap" :size="40"/></div>
  <p style="font-weight:600;color:var(--text);">Belum ada sesi seminar.</p>
</div>
@endforelse

@endsection
