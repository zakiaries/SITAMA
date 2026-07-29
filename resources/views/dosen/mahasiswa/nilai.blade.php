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
.accordion-header:hover { background: var(--warm); }
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
.score-input:invalid { border-color: var(--danger); }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="{{ route('dosen.mahasiswa.detail', $student) }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
  <div>
    <div class="page-title" style="margin-bottom:2px;">Input Nilai</div>
    <div style="font-size:12px;color:var(--text-muted);">{{ $student->user->name }} — {{ $student->user->username }}</div>
  </div>
</div>

{{-- Info Card --}}
<div class="card" style="margin-bottom:20px;display:flex;align-items:center;gap:14px;">
  @php $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
  <div style="width:42px;height:42px;border-radius:50%;background:var(--blue-tint);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
    {{ $initials }}
  </div>
  <div style="flex:1;">
    <div style="font-weight:700;font-size:14px;">{{ $student->user->name }}</div>
    <div style="font-size:12px;color:var(--text-muted);">{{ $internship->company->name ?? '-' }} · {{ $internship->position }}</div>
  </div>
  @if($internship->is_finished)
    <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Magang Selesai</span>
  @else
    <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="alert" :size="12"/> Magang Aktif</span>
  @endif
</div>

{{-- Form Nilai --}}
<form method="POST" action="{{ route('dosen.mahasiswa.nilai.update', $student) }}">
  @csrf

  @php
    $componentIcons = ['clipboard', 'code', 'handshake', 'bulb'];
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
        <span style="color:var(--primary);display:inline-flex;"><x-icon :name="$icon" :size="18"/></span>
      </div>
      <div class="accordion-title">{{ $component->name }}@if($component->weight) <span style="font-weight:500;color:var(--text-muted);font-size:12px;">(bobot {{ intval($component->weight) }}%)</span>@endif</div>
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
          placeholder="1 - 10"
          min="1" max="10" step="0.5"
          oninput="clampScore(this)">
      </div>
      @endforeach
    </div>
  </div>
  @endforeach

  @php
    $hasAnyScore = collect($components)->flatMap->detailedComponents
      ->flatMap(fn($d) => $d->scores)->isNotEmpty();
  @endphp
  <div style="margin-top:8px;">
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">
      {{ $hasAnyScore ? 'Update Nilai' : 'Simpan Nilai' }}
    </button>
  </div>
</form>

@endsection

@push('scripts')
<script>
function clampScore(el) {
  if (el.value === '') return;
  var v = parseFloat(el.value);
  if (isNaN(v)) { el.value = ''; return; }
  if (v > 10) el.value = 10;
  if (v < 1)  el.value = 1;
}
</script>
@endpush
