@extends('layouts.dosen-industri')
@section('title', 'Penilaian Akhir')
@php $title = 'Penilaian Akhir'; @endphp

@push('styles')
<style>
.assess-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  margin-bottom:12px;overflow:hidden;
}
.assess-head {
  display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--warm);
  border-bottom:1px solid var(--border);
}
.assess-icon { width:34px;height:34px;border-radius:9px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
.assess-title { font-size:14px;font-weight:700;color:var(--text); }
.assess-body { padding:8px 16px; }
.score-row {
  display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:11px 0;border-bottom:1px solid var(--border);
}
.score-row:last-child { border-bottom:none; }
.score-label { font-size:13px;color:var(--text);flex:1; }
.score-input-wrap { display:flex;align-items:center;gap:6px;flex-shrink:0; }
.score-input {
  width:70px;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;
  font-size:14px;font-weight:700;text-align:center;font-family:inherit;outline:none;color:var(--primary);
}
.score-input:focus { border-color:var(--primary); }
.score-max { font-size:12px;color:var(--text-muted); }

.avg-card {
  background:var(--primary);border-radius:12px;
  padding:20px;margin-bottom:16px;color:#fff;text-align:center;
}
.avg-val { font-size:42px;font-weight:800;line-height:1; }
.avg-lbl { font-size:12px;opacity:0.85;margin-top:4px; }
.avg-bar-track { height:6px;background:rgba(255,255,255,0.25);border-radius:4px;margin-top:14px;overflow:hidden; }
.avg-bar-fill  { height:100%;background:#fff;border-radius:4px;transition:width .4s; }
.avg-quality { font-size:13px;font-weight:600;margin-top:10px; }

.notes-input {
  width:100%;padding:12px 14px;border:1.5px solid var(--border);border-radius:10px;
  font-size:13px;font-family:inherit;resize:vertical;min-height:90px;outline:none;color:var(--text);
}
.notes-input:focus { border-color:var(--primary); }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="{{ route('dosen-industri.mahasiswa.detail', $student) }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
  <div>
    <div class="page-title" style="margin-bottom:2px;">Penilaian Akhir</div>
    <div style="font-size:12px;color:var(--text-muted);">{{ $student->user->name }} · {{ $internship->position }}</div>
  </div>
</div>

<form method="POST" action="{{ route('dosen-industri.mahasiswa.penilaian.simpan', $student) }}" id="form-penilaian">
  @csrf

  @php $icons = ['clipboard','code','handshake','bulb']; $i = 0; @endphp
  @foreach($components as $component)
  @php $icon = $icons[$i % count($icons)]; $i++; @endphp
  <div class="assess-card">
    <div class="assess-head">
      <div class="assess-icon" style="color:var(--primary);"><x-icon :name="$icon" :size="18"/></div>
      <div class="assess-title">{{ $component->name }}</div>
    </div>
    <div class="assess-body">
      @foreach($component->detailedComponents as $detail)
      @php $current = $detail->scores->first()?->score; @endphp
      <div class="score-row">
        <div class="score-label">{{ $detail->name }}</div>
        <div class="score-input-wrap">
          <input type="number" name="scores[{{ $detail->id }}]" class="score-input js-score"
                 value="{{ $current !== null ? rtrim(rtrim(number_format($current,1),'0'),'.') : '' }}"
                 placeholder="1 - 10" min="1" max="10" step="0.5" oninput="clampScore(this);hitungRata()">
          <span class="score-max">/100</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach

  {{-- Rata-rata --}}
  <div class="avg-card">
    <div class="avg-lbl">Rata-rata Nilai Industri</div>
    <div class="avg-val" id="avg-value">0</div>
    <div class="avg-bar-track"><div class="avg-bar-fill" id="avg-bar" style="width:0%;"></div></div>
    <div class="avg-quality" id="avg-quality">Belum dinilai</div>
  </div>

  {{-- Catatan Akhir --}}
  <div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:10px;">Catatan Akhir</div>
    <textarea name="performance_notes" class="notes-input"
      placeholder="Catatan akhir untuk mahasiswa...">{{ $internship->performance_notes }}</textarea>
    @if($internship->performance_notes_by)
      <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
        Terakhir oleh {{ $internship->performance_notes_by }}
        @if($internship->performance_notes_date) · {{ \Illuminate\Support\Carbon::parse($internship->performance_notes_date)->format('d M Y') }} @endif
      </div>
    @endif
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:15px;">
    <x-icon name="save" :size="16"/> Simpan &amp; Kirim Penilaian
  </button>
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

function hitungRata() {
  var inputs = document.querySelectorAll('.js-score');
  var sum = 0, count = 0;
  inputs.forEach(function (inp) {
    var v = parseFloat(inp.value);
    if (!isNaN(v)) { sum += v; count++; }
  });
  var avg = count > 0 ? (sum / count) : 0;
  var rounded = Math.round(avg * 100) / 100;

  document.getElementById('avg-value').textContent = count > 0 ? rounded : '0';
  document.getElementById('avg-bar').style.width = Math.min(avg, 100) + '%';

  var q = 'Belum dinilai';
  if (count > 0) {
    if (avg >= 85)      q = 'Sangat Baik';
    else if (avg >= 70) q = 'Baik';
    else if (avg >= 55) q = 'Cukup';
    else                q = 'Kurang';
  }
  document.getElementById('avg-quality').textContent = q;
}
document.addEventListener('DOMContentLoaded', hitungRata);
</script>
@endpush
