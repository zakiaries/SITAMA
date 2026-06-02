@extends('layouts.dosen')
@section('title', 'Input Nilai')
@php $title = 'Input Nilai'; @endphp

@push('styles')
<style>
.accordion-section {
  background: #fff; border: 1.5px solid var(--border); border-radius: 12px;
  margin-bottom: 12px; overflow: hidden;
}
.accordion-header {
  display: flex; align-items: center; gap: 14px; padding: 16px 18px;
  cursor: pointer; user-select: none; transition: background .15s;
}
.accordion-header:hover { background: #f8fafc; }
.accordion-icon-wrap {
  width: 38px; height: 38px; border-radius: 10px; background: var(--primary-light);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.accordion-title { flex: 1; font-size: 14px; font-weight: 700; color: var(--text); }
.accordion-chevron { color: var(--text-muted); transition: transform .2s; }
.accordion-section.open .accordion-chevron { transform: rotate(180deg); }
.accordion-body {
  display: none; padding: 0 18px 18px; border-top: 1px solid var(--border);
}
.accordion-section.open .accordion-body { display: block; }
.score-row {
  display: flex; align-items: center; justify-content: space-between;
  gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border);
}
.score-row:last-child { border-bottom: none; }
.score-label { font-size: 13px; color: var(--text); flex: 1; }
.score-input {
  width: 90px; padding: 8px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center;
  font-family: inherit; outline: none; color: var(--primary);
  transition: border-color .15s;
}
.score-input:focus  { border-color: var(--primary); }
.score-input:invalid { border-color: #dc2626; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="{{ route('dosen.mahasiswa.detail', $student) }}" class="btn btn-outline btn-sm">← Kembali</a>
  <div>
    <div class="page-title" style="margin-bottom:2px;">Input Nilai</div>
    <div style="font-size:12px;color:var(--text-muted);">{{ $student->user->name }} — {{ $student->user->username }}</div>
  </div>
</div>

{{-- Info Card --}}
<div class="card" style="margin-bottom:20px;display:flex;align-items:center;gap:14px;">
  @php $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
  <div style="width:42px;height:42px;border-radius:50%;background:#e8eef8;color:#0c2a5c;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
    {{ $initials }}
  </div>
  <div style="flex:1;">
    <div style="font-weight:700;font-size:14px;">{{ $student->user->name }}</div>
    <div style="font-size:12px;color:var(--text-muted);">{{ $internship->company->name ?? '-' }} · {{ $internship->position }}</div>
  </div>
  @if($internship->is_finished)
    <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">✓ Magang Selesai</span>
  @else
    <span style="background:#fef9c3;color:#854f0b;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">⚠ Magang Aktif</span>
  @endif
</div>

{{-- Form Nilai --}}
<form method="POST" action="{{ route('dosen.mahasiswa.nilai.update', $student) }}">
  @csrf

  @php
    $componentIcons = ['📋', '💻', '🤝', '💡'];
    $i = 0;
  @endphp

  @foreach($components as $component)
  @php
    $icon = $componentIcons[$i % count($componentIcons)];
    $i++;
  @endphp
  <div class="accordion-section open">
    <div class="accordion-header" onclick="this.parentElement.classList.toggle('open')">
      <div class="accordion-icon-wrap">
        <span style="font-size:18px;">{{ $icon }}</span>
      </div>
      <div class="accordion-title">{{ $component->name }}</div>
      <svg class="accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </div>
    <div class="accordion-body">
      @foreach($component->detailedComponents as $detail)
      @php $currentScore = $detail->scores->first()?->score; @endphp
      <div class="score-row">
        <div class="score-label">{{ $detail->name }}</div>
        <input
          type="number"
          name="scores[{{ $detail->id }}]"
          class="score-input"
          value="{{ $currentScore !== null ? number_format($currentScore, 1) : '' }}"
          placeholder="0 - 100"
          min="0" max="100" step="0.5">
      </div>
      @endforeach
    </div>
  </div>
  @endforeach

  <div style="margin-top:8px;">
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">
      Update Nilai
    </button>
  </div>
</form>

@endsection
