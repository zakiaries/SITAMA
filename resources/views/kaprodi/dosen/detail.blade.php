@extends('layouts.kaprodi')
@section('title', 'Detail Dosen')
@php $title = 'Detail Dosen'; @endphp

@push('styles')
<style>
.mhs-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
}
.mhs-av   { width:44px;height:44px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px; }
.mhs-info { flex:1;min-width:0; }
.mhs-name { font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px; }
.mhs-meta { font-size:12px;color:var(--text-muted); }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-aktif   { background:#eff6ff;color:#2563eb; }
.st-selesai { background:#dcfce7;color:#16a34a; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="{{ route('kaprodi.dosen.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
  <div>
    <div class="page-title" style="margin-bottom:2px;">{{ $lecturer->user->name ?? '-' }}</div>
    <div style="font-size:12px;color:var(--text-muted);">Bimbingan {{ $students->count() }} Mahasiswa</div>
  </div>
</div>

@forelse($students as $student)
@php
  $internship = $student->internships->first();
  $colors = [
    ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
    ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#eeedfe','text'=>'#3c3489'],
  ];
  $color    = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="mhs-card">
  <div class="mhs-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
  <div class="mhs-info">
    <div class="mhs-name">{{ $student->user->name }}</div>
    <div class="mhs-meta">{{ $student->user->username }} · {{ $student->the_class }}@if($internship?->company) · {{ $internship->company->name }}@endif</div>
  </div>
  @if($internship?->is_finished)
    <span class="st-badge st-selesai">Selesai</span>
  @else
    <span class="st-badge st-aktif">Aktif</span>
  @endif
</div>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Dosen ini belum membimbing mahasiswa.</p></div>
@endforelse

@endsection
