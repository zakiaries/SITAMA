@extends('layouts.mahasiswa')
@section('title', 'Detail Seminar')
@php $title = 'Detail Seminar'; @endphp
@section('content')

<a href="{{ route('mahasiswa.seminar') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);text-decoration:none;margin-bottom:14px;">← Kembali</a>

@php $guests = $seminar->attendances->count(); $min = \App\Models\Seminar::MIN_GUESTS; @endphp

<div class="card" style="margin-bottom:16px;">
  <div style="font-size:16px;font-weight:700;color:var(--text);">{{ $seminar->title }}</div>
  <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Dosen: {{ $seminar->lecturer->user->name ?? '-' }}</div>
  @if($seminar->date)
    <div style="font-size:13px;color:var(--text);margin-top:8px;">
      {{ $seminar->date->format('d M Y') }}{{ $seminar->time ? ' · '.$seminar->time : '' }}{{ $seminar->location ? ' · '.$seminar->location : '' }}
    </div>
  @endif

  <div style="margin-top:12px;">
    <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Penyaji</div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;">
      @foreach($seminar->presenters as $p)
        <span style="background:var(--warm-2);color:var(--text);font-size:12px;padding:3px 10px;border-radius:20px;">{{ $p->student->user->name ?? '-' }}</span>
      @endforeach
    </div>
  </div>
</div>

@if($seminar->status === 'scheduled')
<div class="card" style="margin-bottom:16px;">
  <div style="font-size:14px;font-weight:700;margin-bottom:4px;">Daftar Hadir Audiens</div>
  <div style="font-size:12px;color:var(--text-muted);line-height:1.6;">
    QR daftar hadir <strong>ditampilkan dosen di layar</strong> saat seminar berlangsung dan
    <strong>berganti otomatis</strong> untuk mencegah titip absen. Audiens memindai langsung dari layar,
    login, lalu menekan "Hadir". Minimal {{ $min }} audiens.
  </div>
</div>
@endif

<div class="card">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
    <div style="font-size:14px;font-weight:700;">Daftar Hadir Audiens ({{ $guests }}/{{ $min }})</div>
    @if($seminar->status === 'completed')
      <a href="{{ route('mahasiswa.seminar.berita-acara', $seminar->id) }}" class="btn btn-outline btn-sm"><x-icon name="download" :size="13"/> Unduh Berita Acara (PDF)</a>
    @endif
  </div>
  @forelse($seminar->attendances as $a)
    <div style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
      <div style="font-size:13px;color:var(--text);">{{ $a->name ?? ($a->student->user->name ?? '-') }} <span style="color:var(--text-muted);">· {{ $a->nim ?? '' }}</span></div>
      <div style="font-size:11.5px;color:var(--text-muted);">{{ $a->created_at->format('d M H:i') }}</div>
    </div>
  @empty
    <p style="color:var(--text-muted);font-size:13px;padding:6px 0;">Belum ada audiens yang mengisi daftar hadir.</p>
  @endforelse
</div>

@endsection
