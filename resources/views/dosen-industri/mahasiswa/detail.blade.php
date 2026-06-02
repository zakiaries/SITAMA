@extends('layouts.dosen-industri')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px; padding: 28px 24px; margin-bottom: 20px;
  position: relative; overflow: hidden; display: flex; align-items: center; gap: 20px;
}
.student-hero::before { content:''; position:absolute; inset:0; background:rgba(45,62,110,0.80); }
.student-hero > * { position:relative; z-index:1; }
.hero-avatar {
  width:72px;height:72px;border-radius:50%;flex-shrink:0;
  background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.4);
  display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;
}
.hero-sname { font-size:20px;font-weight:800;color:#fff;margin-bottom:4px; }
.hero-nim   { display:inline-block;background:rgba(255,255,255,0.18);color:rgba(255,255,255,0.9);font-size:12px;padding:2px 12px;border-radius:20px;margin-bottom:6px; }
.hero-pos   { font-size:12px;color:rgba(255,255,255,0.8); }

.info-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px; }
.info-box  { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px 16px; }
.info-box .ib-label { font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.info-box .ib-value { font-size:15px;font-weight:700;color:var(--text); }
.info-box.gold .ib-value { color:#b45309; }
.info-box.blue .ib-value { color:#1d4ed8; }

/* Filter tabs */
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab {
  padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;
  font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;
  cursor:pointer;text-decoration:none;transition:all .15s;
}
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

/* Logbook card */
.lb-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:12px; }
.lb-card-head { display:flex;gap:12px;align-items:flex-start; }
.lb-day-box {
  width:46px;height:46px;border-radius:10px;background:#e8eef8;color:#0c2a5c;
  display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;
}
.lb-day-box .num { font-size:15px;font-weight:800;line-height:1; }
.lb-day-box .lbl { font-size:8px;text-transform:uppercase;margin-top:1px; }
.lb-body  { flex:1;min-width:0; }
.lb-title { font-size:14px;font-weight:700;color:var(--text); }
.lb-date  { font-size:11px;color:var(--text-muted);margin-bottom:6px; }
.lb-desc  { font-size:13px;color:var(--text);line-height:1.5;margin-bottom:8px; }
.lb-badge-status { font-size:10px;font-weight:600;padding:2px 9px;border-radius:20px;flex-shrink:0; }
.bs-done { background:#dcfce7;color:#16a34a; }
.bs-pending { background:#fef3c7;color:#92400e; }

.comment-box { background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;margin-top:8px; }
.comment-box .cb-label { font-size:10px;font-weight:700;color:#1e40af;margin-bottom:3px;text-transform:uppercase; }
.comment-box .cb-text  { font-size:12px;color:#1e40af; }
.comment-form { margin-top:10px; }
.comment-input {
  width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;
  font-size:12px;font-family:inherit;resize:vertical;min-height:60px;outline:none;color:var(--text);
}
.comment-input:focus { border-color:var(--primary); }
.comment-actions { display:flex;gap:8px;margin-top:8px; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px;">
  <a href="{{ route('dosen-industri.dashboard') }}" class="btn btn-outline btn-sm">← Kembali</a>
</div>

{{-- Hero --}}
@php $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
<div class="student-hero">
  <div class="hero-avatar">{{ $initials }}</div>
  <div style="flex:1;">
    <div class="hero-sname">{{ $student->user->name }}</div>
    <div class="hero-nim">{{ $student->user->username }}</div>
    <div class="hero-pos">{{ $internship->position }} · {{ $internship->company->name ?? '-' }}</div>
  </div>
  @if($internship->is_finished)
    <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;">✓ Selesai</span>
  @else
    <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:4px 12px;border-radius:20px;">🔄 Aktif Magang</span>
  @endif
</div>

{{-- Info Grid 2x2 --}}
<div class="info-grid">
  <div class="info-box">
    <div class="ib-label">📅 Mulai Magang</div>
    <div class="ib-value">{{ $internship->start_date->format('d M Y') }}</div>
  </div>
  <div class="info-box gold">
    <div class="ib-label">🏁 Selesai</div>
    <div class="ib-value">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum' }}</div>
  </div>
  <div class="info-box blue">
    <div class="ib-label">📒 Total Logbook</div>
    <div class="ib-value">{{ $totalLog }} entri</div>
  </div>
  <div class="info-box">
    <div class="ib-label">💬 Sudah Dikomentari</div>
    <div class="ib-value">{{ $sudahDikomen }}/{{ $totalLog }}</div>
  </div>
</div>

{{-- CTA Penilaian Akhir --}}
<a href="{{ route('dosen-industri.mahasiswa.penilaian', $student) }}" class="btn btn-primary"
   style="width:100%;justify-content:center;padding:14px;font-size:15px;margin-bottom:24px;">
  ⭐ Beri Penilaian Akhir
</a>

{{-- Logbook Section --}}
<div class="card-title" style="margin-bottom:14px;">Logbook Mahasiswa</div>

{{-- Filter Tabs --}}
<div class="filter-tabs">
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'semua']) }}"
     class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua ({{ $totalLog }})</a>
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'belum']) }}"
     class="filter-tab {{ $filter === 'belum' ? 'active' : '' }}">Belum Dikomen ({{ $totalLog - $sudahDikomen }})</a>
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'sudah']) }}"
     class="filter-tab {{ $filter === 'sudah' ? 'active' : '' }}">Sudah Dikomen ({{ $sudahDikomen }})</a>
</div>

{{-- Logbook List --}}
@forelse($logBooks as $lb)
@php $dt = $lb->date; $hasComment = !empty($lb->lecturer_note); @endphp
<div class="lb-card">
  <div class="lb-card-head">
    <div class="lb-day-box">
      <div class="num">{{ $dt->format('d') }}</div>
      <div class="lbl">{{ strtoupper($dt->locale('id')->isoFormat('MMM')) }}</div>
    </div>
    <div class="lb-body">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
        <div>
          <div class="lb-title">{{ $lb->title }}</div>
          <div class="lb-date">{{ $dt->format('d M Y') }}</div>
        </div>
        @if($hasComment)
          <span class="lb-badge-status bs-done">✓ Dikomen</span>
        @else
          <span class="lb-badge-status bs-pending">Belum</span>
        @endif
      </div>
      <div class="lb-desc">{{ $lb->activity }}</div>

      @if($hasComment)
        <div class="comment-box">
          <div class="cb-label">💬 Komentar Saya</div>
          <div class="cb-text">{{ $lb->lecturer_note }}</div>
        </div>
      @endif

      <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;"
        onclick="document.getElementById('cf-{{ $lb->id }}').style.display = (document.getElementById('cf-{{ $lb->id }}').style.display==='block'?'none':'block')">
        {{ $hasComment ? '✏ Edit Komentar' : '+ Komentar' }}
      </button>

      <div id="cf-{{ $lb->id }}" class="comment-form" style="display:none;">
        <form method="POST" action="{{ route('dosen-industri.mahasiswa.logbook.komentar', [$student, $lb]) }}">
          @csrf
          <textarea name="komentar" class="comment-input" placeholder="Masukkan komentar..." required>{{ $lb->lecturer_note }}</textarea>
          <div class="comment-actions">
            <button type="submit" class="btn btn-primary btn-sm">➤ Kirim</button>
            @if($hasComment)
            <button type="button" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;"
              onclick="if(confirm('Hapus komentar?'))document.getElementById('del-{{ $lb->id }}').submit()">🗑 Hapus</button>
            @endif
          </div>
        </form>
        @if($hasComment)
        <form id="del-{{ $lb->id }}" method="POST" action="{{ route('dosen-industri.mahasiswa.logbook.komentar.hapus', [$student, $lb]) }}" style="display:none;">
          @csrf
          @method('DELETE')
        </form>
        @endif
      </div>
    </div>
  </div>
</div>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);">
  <p>Tidak ada logbook pada filter ini.</p>
</div>
@endforelse

@endsection
