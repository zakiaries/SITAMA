@extends('layouts.dosen-industri')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: var(--warm);
  border-radius: 14px; padding: 26px 28px; margin-bottom: 20px;
  display: flex; align-items: center; gap: 20px;
}
.hero-avatar {
  width:72px;height:72px;border-radius:50%;flex-shrink:0;
  background:var(--primary);border:none;
  display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;
}
.hero-sname { font-size:24px;font-weight:800;color:var(--text);letter-spacing:-0.02em;margin-bottom:6px; }
.hero-nim   { display:inline-block;background:var(--blue-tint);color:var(--primary);font-size:12px;font-weight:600;padding:3px 12px;border-radius:9999px;margin-bottom:6px; }
.hero-pos   { font-size:13px;color:var(--text-secondary); }

.info-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px; }
@media(max-width:640px){ .info-grid { grid-template-columns:1fr; } }

/* Kartu konteks: data mahasiswa & pembimbing kampus */
.ctx-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px; }
@media(max-width:640px){ .ctx-grid { grid-template-columns:1fr; } }
.ctx-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px 18px; }
.ctx-card h4 { font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin:0 0 12px; }
.ctx-row { display:flex;justify-content:space-between;gap:12px;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border-subtle,#EEE); }
.ctx-row:last-child { border-bottom:none; }
.ctx-row .k { color:var(--text-muted);flex-shrink:0; }
.ctx-row .v { color:var(--text);font-weight:600;text-align:right;word-break:break-word; }

/* Bar progres logbook */
.prog-track { height:7px;background:var(--border);border-radius:9999px;overflow:hidden;margin-top:8px; }
.prog-fill  { height:100%;background:var(--primary);border-radius:9999px; }
.prog-fill.done { background:var(--success); }
.info-box  { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px 16px; }
.info-box .ib-label { font-size:11px;color:var(--text-muted);margin-bottom:4px; }
.info-box .ib-value { font-size:15px;font-weight:700;color:var(--text); }
.info-box.gold .ib-value { color:var(--warn-text); }
.info-box.blue .ib-value { color:var(--primary); }

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
  width:46px;height:46px;border-radius:10px;background:var(--blue-tint);color:var(--primary);
  display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;
}
.lb-day-box .num { font-size:15px;font-weight:800;line-height:1; }
.lb-day-box .lbl { font-size:8px;text-transform:uppercase;margin-top:1px; }
.lb-body  { flex:1;min-width:0; }
.lb-title { font-size:14px;font-weight:700;color:var(--text); }
.lb-date  { font-size:11px;color:var(--text-muted);margin-bottom:6px; }
.lb-desc  { font-size:13px;color:var(--text);line-height:1.5;margin-bottom:8px; }
.lb-badge-status { font-size:10px;font-weight:600;padding:2px 9px;border-radius:20px;flex-shrink:0; }
.bs-done { background:var(--success-bg);color:var(--success-text); }
.bs-pending { background:var(--warn-bg);color:var(--warn-text); }

.comment-box { background:var(--blue-tint);border:1px solid #C7DCFF;border-radius:8px;padding:10px 12px;margin-top:8px; }
.comment-box .cb-label { font-size:10px;font-weight:700;color:var(--primary);margin-bottom:3px;text-transform:uppercase; }
.comment-box .cb-text  { font-size:12px;color:var(--primary); }
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
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif

<div style="margin-bottom:16px;">
  <a href="{{ route('dosen-industri.dashboard') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
</div>

{{-- Hero --}}
<div class="student-hero">
  <x-avatar :user="$student->user" class="hero-avatar" />
  <div style="flex:1;">
    <div class="hero-sname">{{ $student->user->name }}</div>
    <div class="hero-nim">{{ $student->user->username }}</div>
    <div class="hero-pos">{{ $internship->position }} · {{ $internship->company->name ?? '-' }}</div>
  </div>
  @if($internship->is_finished)
    <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:4px 12px;border-radius:9999px;display:inline-flex;align-items:center;gap:5px;"><x-icon name="check" :size="12"/> Selesai</span>
  @else
    <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:4px 12px;border-radius:9999px;display:inline-flex;align-items:center;gap:5px;"><x-icon name="refresh" :size="12"/> Aktif Magang</span>
  @endif
</div>

{{-- Konteks: siapa mahasiswanya & siapa dosen kampusnya (untuk koordinasi) --}}
<div class="ctx-grid">
  <div class="ctx-card">
    <h4>Data Mahasiswa</h4>
    <div class="ctx-row">
      <span class="k">Email</span>
      <span class="v"><a href="mailto:{{ $student->user->email }}">{{ $student->user->email }}</a></span>
    </div>
    <div class="ctx-row">
      <span class="k">Program Studi</span>
      <span class="v">{{ $student->study_program ?: '-' }}</span>
    </div>
    <div class="ctx-row">
      <span class="k">Jurusan</span>
      <span class="v">{{ $student->major ?: '-' }}</span>
    </div>
    <div class="ctx-row">
      <span class="k">Kelas</span>
      <span class="v">{{ $student->the_class ?: '-' }}</span>
    </div>
    <div class="ctx-row">
      <span class="k">Tahun Akademik</span>
      <span class="v">{{ $student->academic_year ?: '-' }}</span>
    </div>
  </div>

  <div class="ctx-card">
    <h4>Pembimbing Kampus</h4>
    @if($internship->lecturer?->user)
      <div class="ctx-row">
        <span class="k">Nama</span>
        <span class="v">{{ $internship->lecturer->user->name }}</span>
      </div>
      <div class="ctx-row">
        <span class="k">Email</span>
        <span class="v"><a href="mailto:{{ $internship->lecturer->user->email }}">{{ $internship->lecturer->user->email }}</a></span>
      </div>
      <p style="font-size:12px;color:var(--text-muted);line-height:1.6;margin:12px 0 0;">
        Hubungi dosen pembimbing kampus bila ada kendala akademik atau perilaku mahasiswa
        yang perlu ditindaklanjuti pihak kampus.
      </p>
    @else
      <p style="font-size:13px;color:var(--text-muted);margin:0;">
        Dosen pembimbing kampus belum ditetapkan Kaprodi.
      </p>
    @endif
  </div>
</div>

{{-- Info Grid 2x2 --}}
<div class="info-grid">
  <div class="info-box">
    <div class="ib-label"><x-icon name="calendar" :size="13"/> Mulai Magang</div>
    <div class="ib-value">{{ $internship->start_date->format('d M Y') }}</div>
  </div>
  <div class="info-box gold">
    <div class="ib-label"><x-icon name="flag" :size="13"/> Selesai</div>
    <div class="ib-value">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum' }}</div>
  </div>
  <div class="info-box blue">
    <div class="ib-label"><x-icon name="book" :size="13"/> Logbook Terkumpul</div>
    <div class="ib-value">{{ $totalLog }} / {{ $minLogbook }} minimum</div>
    @php $pct = $minLogbook > 0 ? min(100, (int) round($totalLog / $minLogbook * 100)) : 0; @endphp
    <div class="prog-track">
      <div class="prog-fill {{ $totalLog >= $minLogbook ? 'done' : '' }}" style="width:{{ $pct }}%;"></div>
    </div>
  </div>
  <div class="info-box">
    <div class="ib-label"><x-icon name="message" :size="13"/> Sudah Dikomentari</div>
    <div class="ib-value">{{ $sudahDikomen }}/{{ $totalLog }}</div>
    <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
      @if($logTerakhir)
        Logbook terakhir {{ \Carbon\Carbon::parse($logTerakhir)->translatedFormat('d M Y') }}
        ({{ \Carbon\Carbon::parse($logTerakhir)->diffForHumans() }})
      @else
        Mahasiswa belum mengisi logbook
      @endif
    </div>
  </div>
</div>

{{-- Status penilaian oleh pembimbing industri (tugas utama peran ini) --}}
<div class="ctx-card" style="margin-bottom:16px;">
  <h4>Penilaian Anda</h4>
  <div class="ctx-row">
    <span class="k">Komponen dinilai</span>
    <span class="v">{{ $komponenDinilai }} / {{ $komponenTotal }}</span>
  </div>
  <div class="ctx-row">
    <span class="k">Rata-rata nilai industri</span>
    <span class="v">
      @if($nilaiIndustri['average'] !== null)
        {{ number_format($nilaiIndustri['average'], 2) }} <span style="font-weight:400;color:var(--text-muted);">/ 10</span>
      @else
        <span style="font-weight:400;color:var(--text-muted);">Belum dinilai</span>
      @endif
    </span>
  </div>

  @if($internship->performance_notes)
    <div style="background:var(--warm);border-radius:8px;padding:10px 12px;margin-top:12px;">
      <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;">Catatan Kinerja</div>
      <div style="font-size:12.5px;color:var(--text);line-height:1.55;">{{ $internship->performance_notes }}</div>
      <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
        {{ $internship->performance_notes_by }}@if($internship->performance_notes_date) · {{ \Carbon\Carbon::parse($internship->performance_notes_date)->translatedFormat('d M Y') }}@endif
      </div>
    </div>
  @endif

  <a href="{{ route('dosen-industri.mahasiswa.penilaian', $student) }}" class="btn btn-primary"
     style="width:100%;justify-content:center;padding:13px;font-size:14px;margin-top:14px;">
    <x-icon name="star" :size="16"/>
    {{ $komponenDinilai > 0 ? 'Ubah / Lanjutkan Penilaian' : 'Beri Penilaian Akhir' }}
  </a>
</div>

{{-- Logbook Section --}}
<div class="card-title" style="margin-bottom:14px;">Logbook Mahasiswa</div>

{{-- Filter Tabs --}}
<div class="filter-tabs">
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'semua', 'period' => $period]) }}"
     class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua ({{ $totalLog }})</a>
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'belum', 'period' => $period]) }}"
     class="filter-tab {{ $filter === 'belum' ? 'active' : '' }}">Belum Dikomen ({{ $totalLog - $sudahDikomen }})</a>
  <a href="{{ route('dosen-industri.mahasiswa.detail', [$student, 'filter' => 'sudah', 'period' => $period]) }}"
     class="filter-tab {{ $filter === 'sudah' ? 'active' : '' }}">Sudah Dikomen ({{ $sudahDikomen }})</a>

  <form method="GET" action="{{ route('dosen-industri.mahasiswa.detail', $student) }}" style="margin-left:auto;">
    <input type="hidden" name="filter" value="{{ $filter }}">
    <select name="period" onchange="this.form.submit()" class="filter-tab" style="cursor:pointer;">
      <option value="semua" {{ $period === 'semua' ? 'selected' : '' }}>Semua Waktu</option>
      <option value="7hari" {{ $period === '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
      <option value="30hari" {{ $period === '30hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
      <option value="bulan_ini" {{ $period === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
    </select>
  </form>
</div>

{{-- Logbook List --}}
@forelse($logBooks as $lb)
@php $dt = $lb->date; $hasComment = !empty($lb->industry_note); @endphp
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
          <span class="lb-badge-status bs-done" style="display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Dikomen</span>
        @else
          <span class="lb-badge-status bs-pending">Belum</span>
        @endif
      </div>
      <div class="lb-desc">{{ $lb->activity }}</div>

      @if($hasComment)
        <div class="comment-box">
          <div class="cb-label"><x-icon name="message" :size="13"/> Komentar Saya</div>
          <div class="cb-text">{{ $lb->industry_note }}</div>
        </div>
      @endif

      <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;"
        onclick="document.getElementById('cf-{{ $lb->id }}').style.display = (document.getElementById('cf-{{ $lb->id }}').style.display==='block'?'none':'block')">
        @if($hasComment)<x-icon name="pencil" :size="14"/> Edit Komentar @else+ Komentar @endif
      </button>

      <div id="cf-{{ $lb->id }}" class="comment-form" style="display:none;">
        <form method="POST" action="{{ route('dosen-industri.mahasiswa.logbook.komentar', [$student, $lb]) }}">
          @csrf
          <textarea name="komentar" class="comment-input" placeholder="Masukkan komentar..." required>{{ $lb->industry_note }}</textarea>
          <div class="comment-actions">
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="send" :size="14"/> Kirim</button>
            @if($hasComment)
            <button type="button" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"
              onclick="confirmDialog('Hapus komentar?', function(){document.getElementById('del-{{ $lb->id }}').submit()}, 'danger')"><x-icon name="trash" :size="14"/> Hapus</button>
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
