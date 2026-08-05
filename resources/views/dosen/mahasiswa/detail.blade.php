@extends('layouts.dosen')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: var(--warm);
  border-radius: 14px;
  padding: 26px 28px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 20px;
}
.hero-avatar {
  width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
  background: var(--primary); border: none;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 800; color: #fff;
}
.hero-detail { flex: 1; }
.hero-sname  { font-size: 24px; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 6px; }
.hero-nim    { display: inline-block; background: var(--blue-tint); color: var(--primary); font-size: 12px; font-weight: 600; padding: 3px 12px; border-radius: 9999px; margin-bottom: 6px; }
.hero-email  { font-size: 13px; color: var(--text-secondary); }

.stats-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.stat-box    { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; padding: 16px; text-align: center; }
.stat-box-val { font-size: 28px; font-weight: 800; color: var(--primary); }
.stat-box-lbl { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.stat-box-icon { font-size: 22px; margin-bottom: 6px; }

.nilai-card { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 16px; }
.nilai-row  { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
.nilai-row:last-child { border-bottom: none; }
.nilai-row.total { border-top: 2px solid var(--border); margin-top: 4px; padding-top: 12px; font-weight: 700; }
.nilai-badge {
  background: var(--warm); color: var(--primary); font-weight: 700;
  font-size: 13px; padding: 4px 14px; border-radius: 20px;
}

/* Tabs */
.tab-nav { display: flex; border-bottom: 2px solid var(--border); margin-bottom: 16px; }
.tab-btn {
  padding: 10px 20px; font-size: 13px; font-weight: 600; cursor: pointer;
  border: none; background: none; color: var(--text-muted); border-bottom: 2px solid transparent;
  margin-bottom: -2px; font-family: inherit; transition: all .15s;
}
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* Bimbingan item styling (reuse exp-item but enhanced) */
.bimb-item { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
.bimb-item.status-rejected { border-color: #F0C4BE; background: var(--danger-bg); }
.bimb-item.status-approved { border-color: #A7E8CF; background: var(--success-bg); }
.bimb-header {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  cursor: pointer; user-select: none;
}
.bimb-body { padding: 0 16px 16px; border-top: 1px solid var(--border); }
.status-circle {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;
}
.sc-approved { background: var(--success-bg); color: var(--success-text); }
.sc-rejected  { background: var(--danger-bg); color: var(--danger); }
.sc-pending   { background: var(--warn-bg); color: var(--warn-text); }
.sc-progress  { background: var(--blue-tint); color: var(--primary); }
.action-row { display: flex; gap: 8px; margin-top: 14px; }
.btn-approve { background: var(--success-text); color: #fff; border: none; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-revisi  { background: #fff; color: var(--danger); border: 1.5px solid var(--danger); border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-approve:hover { background: var(--success-text); }
.btn-revisi:hover  { background: var(--danger-bg); }
.note-input {
  width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 12px; font-family: inherit; resize: vertical;
  min-height: 70px; outline: none; color: var(--text); margin-top: 10px;
}
.note-input:focus { border-color: var(--primary); }
.lecturer-note-box { background: var(--blue-tint); border: 1px solid #C7DCFF; border-radius: 8px; padding: 10px 12px; margin-top: 10px; font-size: 12px; color: var(--primary); }

/* Filter tabs */
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center; }
.filter-tab {
  padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;
  font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;
  cursor:pointer;text-decoration:none;transition:all .15s;
}
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

/* Logbook card (selaras dengan tampilan pembimbing industri) */
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

<x-form-errors/>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('error') }}
  </div>
@endif

{{-- Back Button --}}
<div style="margin-bottom:16px;">
  <a href="{{ route('dosen.dashboard') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
</div>

{{-- Hero --}}
<div class="student-hero">
  <x-avatar :user="$student->user" class="hero-avatar" />
  <div class="hero-detail">
    <div class="hero-sname">{{ $student->user->name }}</div>
    <div class="hero-nim">{{ $student->user->username }}</div>
    <div class="hero-email">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      {{ $student->user->email }}
    </div>
  </div>
</div>

<div class="grid-2" style="gap:16px;">

  {{-- LEFT: Stats + Info Magang + Nilai --}}
  <div>
    {{-- Stats --}}
    <div class="stats-row">
      <div class="stat-box">
        <div class="stat-box-icon" style="color:var(--primary);"><x-icon name="cap" :size="22"/></div>
        <div class="stat-box-val">{{ $student->guidances->count() }}</div>
        <div class="stat-box-lbl">Bimbingan</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-icon" style="color:var(--success-text);"><x-icon name="book" :size="22"/></div>
        <div class="stat-box-val">{{ $student->logBooks->count() }}</div>
        <div class="stat-box-lbl">Total Log</div>
      </div>
    </div>

    {{-- Info Magang --}}
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header" style="margin-bottom:12px;">
        <div class="card-title">Info Magang</div>
        @if(!$internship)
          <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum magang</span>
        @elseif($internship->is_finished)
          <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Selesai</span>
        @else
          <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
        @endif
      </div>
      @if($internship)
        <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);font-weight:600;">{{ $internship->company->name ?? '-' }}</div></div>
        <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
        <div class="info-row"><div class="info-key">Mulai</div><div class="info-val">{{ optional($internship->start_date)->format('d M Y') ?? '-' }}</div></div>
        <div class="info-row">
          <div class="info-key">Selesai</div>
          <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
        </div>
      @else
        {{-- Sudah diplot Kaprodi tapi magangnya belum terbentuk. Bimbingan sudah
             boleh masuk, jadi halaman ini tetap dibuka. --}}
        <div style="font-size:12.5px;color:var(--text-muted);padding:2px 0 10px;">
          Mahasiswa ini sudah diplot ke Anda, tetapi data magangnya belum dibuat Kaprodi.
          Bimbingan tetap bisa diajukan dan Anda tanggapi; logbook, laporan, dan nilai
          baru terbuka setelah magangnya disetujui.
        </div>
      @endif
      <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px;">
        <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
        <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
        <div class="info-row"><div class="info-key">Prodi</div><div class="info-val">{{ $student->study_program }}</div></div>
        <div class="info-row"><div class="info-key">T. Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
      </div>
    </div>

    {{-- Nilai --}}
    <div class="nilai-card">
      <div class="card-header" style="margin-bottom:14px;">
        <div class="card-title">Nilai</div>
        <a href="{{ route('dosen.mahasiswa.nilai', $student) }}" class="btn btn-primary btn-sm">
          + Input Nilai
        </a>
      </div>
      @php
        $allScoresList = [];
        foreach($assessments as $comp) {
          $compScores = $comp->detailedComponents->flatMap->scores->pluck('score')->filter();
          $avg = $compScores->count() > 0 ? round($compScores->avg(), 2) : null;
          $allScoresList[] = ['name' => $comp->name, 'avg' => $avg];
        }
        $filledScores = collect($allScoresList)->pluck('avg')->filter();
        $overallAvg   = $filledScores->count() > 0 ? round($filledScores->avg(), 2) : null;
      @endphp
      @foreach($allScoresList as $item)
      <div class="nilai-row">
        <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}</div>
        <div class="nilai-badge">{{ $item['avg'] ?? '-' }}</div>
      </div>
      @endforeach
      <div class="nilai-row total">
        <div style="font-size:13px;">Rata - rata</div>
        <div class="nilai-badge" style="background:var(--primary);color:#fff;">
          {{ $overallAvg ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  {{-- RIGHT: Tabs Bimbingan & Log Book --}}
  <div>
    <div class="card" style="padding-bottom:0;">
      <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('bimbingan', this)">
          Bimbingan ({{ $student->guidances->count() }})
        </button>
        <button class="tab-btn" onclick="switchTab('logbook', this)">
          Log Book ({{ $totalLog }})
        </button>
        <button class="tab-btn" onclick="switchTab('laporan', this)">
          Laporan Akhir
        </button>
      </div>

      {{-- TAB: BIMBINGAN --}}
      <div id="tab-bimbingan" class="tab-pane active" style="padding-bottom:8px;">
        @forelse($student->guidances as $g)
        @php
          $scClass = match($g->status) {
            'approved'    => 'sc-approved',
            'rejected'    => 'sc-rejected',
            'in-progress' => 'sc-progress',
            default       => 'sc-pending',
          };
          $scIcon = match($g->status) {
            'approved' => 'check', 'rejected' => 'x', default => null,
          };
          $itemClass = match($g->status) {
            'approved' => 'status-approved', 'rejected' => 'status-rejected', default => '',
          };
        @endphp
        <div class="bimb-item {{ $itemClass }}" id="bimbingan-{{ $g->id }}">
          <div class="bimb-header" onclick="toggle(this)">
            <div class="status-circle {{ $scClass }}">@if($scIcon)<x-icon :name="$scIcon" :size="13"/>@else—@endif</div>
            <div style="flex:1;">
              <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $g->title }}</div>
              <div style="font-size:11px;color:var(--text-muted);">{{ $g->date->format('d M Y') }}</div>
            </div>
            <span style="font-size:11px;padding:2px 9px;border-radius:20px;font-weight:600;
              background:{{ $g->status==='approved'?'var(--success-bg)':($g->status==='rejected'?'var(--danger-bg)':'var(--warn-bg)') }};
              color:{{ $g->status==='approved'?'var(--success-text)':($g->status==='rejected'?'var(--danger)':'var(--warn-text)') }};">
              {{ ucfirst($g->status) }}
            </span>
            <div class="chevron">▾</div>
          </div>
          <div class="bimb-body">
            <div class="field-label">Aktivitas</div>
            <div class="field-value" style="margin-bottom:10px;">{{ $g->activity }}</div>
            @if($g->name_file)
              <div class="file-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <a href="{{ route('berkas.bimbingan', $g) }}" target="_blank">File Bimbingan</a>
              </div>
            @endif
            @if($g->lecturer_note)
              <div class="lecturer-note-box">
                <strong>{{ $g->status === 'approved' ? 'Catatan dosen:' : 'Catatan sebelumnya:' }}</strong> {{ $g->lecturer_note }}
              </div>
            @endif

            @if(in_array($g->status, ['approved', 'rejected']))
              {{-- Keputusan sudah diambil: tombol disembunyikan --}}
              <div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
                @if($g->status === 'approved')
                  <x-icon name="check" :size="13"/> Bimbingan ini sudah disetujui.
                @else
                  <x-icon name="x" :size="13"/> Diminta revisi — menunggu mahasiswa mengirim ulang.
                @endif
              </div>
            @else
              {{-- Approve Form --}}
              <form method="POST" action="{{ route('dosen.mahasiswa.bimbingan.approve', [$student, $g]) }}"
                    style="margin-top:12px;" data-confirm="Setujui bimbingan ini?">
                @csrf
                <textarea name="note" class="note-input"
                  placeholder="Catatan untuk mahasiswa (opsional)...">{{ $g->lecturer_note }}</textarea>
                <div class="action-row">
                  <button type="submit" class="btn-approve"><x-icon name="check" :size="14"/> Setujui</button>
                  <button type="button" class="btn-revisi"
                    onclick="submitRevisi(this, {{ $g->id }})"><x-icon name="x" :size="14"/> Revisi</button>
                </div>
              </form>
              {{-- Revisi Form (hidden, triggered by JS) --}}
              <form id="form-revisi-{{ $g->id }}" method="POST"
                    action="{{ route('dosen.mahasiswa.bimbingan.revisi', [$student, $g]) }}" style="display:none;">
                @csrf
                <input type="hidden" name="note" id="note-revisi-{{ $g->id }}">
              </form>
            @endif
          </div>
        </div>
        @empty
          <p style="color:var(--text-muted);font-size:13px;padding:12px 0;">Belum ada data bimbingan.</p>
        @endforelse
      </div>

      {{-- TAB: LOG BOOK --}}
      <div id="tab-logbook" class="tab-pane" style="padding-bottom:8px;">
        <div class="filter-tabs">
          <a href="{{ route('dosen.mahasiswa.detail', [$student, 'filter' => 'semua', 'period' => $period]) }}"
             class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua ({{ $totalLog }})</a>
          <a href="{{ route('dosen.mahasiswa.detail', [$student, 'filter' => 'belum', 'period' => $period]) }}"
             class="filter-tab {{ $filter === 'belum' ? 'active' : '' }}">Belum Dicatat ({{ $totalLog - $sudahDicatat }})</a>
          <a href="{{ route('dosen.mahasiswa.detail', [$student, 'filter' => 'sudah', 'period' => $period]) }}"
             class="filter-tab {{ $filter === 'sudah' ? 'active' : '' }}">Sudah Dicatat ({{ $sudahDicatat }})</a>

          <form method="GET" action="{{ route('dosen.mahasiswa.detail', $student) }}" style="margin-left:auto;">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <select name="period" onchange="this.form.submit()" class="filter-tab" style="cursor:pointer;">
              <option value="semua" {{ $period === 'semua' ? 'selected' : '' }}>Semua Waktu</option>
              <option value="7hari" {{ $period === '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
              <option value="30hari" {{ $period === '30hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
              <option value="bulan_ini" {{ $period === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
            </select>
          </form>
        </div>
        @forelse($logBooks as $lb)
        @php $dt = $lb->date; $hasNote = !empty($lb->lecturer_note); @endphp
        <div class="lb-card" id="logbook-{{ $lb->id }}">
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
                @if($hasNote)
                  <span class="lb-badge-status bs-done" style="display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Dicatat</span>
                @else
                  <span class="lb-badge-status bs-pending">Belum</span>
                @endif
              </div>
              <div class="lb-desc">{{ $lb->activity }}</div>

              @if($hasNote)
                <div class="comment-box">
                  <div class="cb-label"><x-icon name="message" :size="13"/> Catatan Saya</div>
                  <div class="cb-text">{{ $lb->lecturer_note }}</div>
                </div>
              @endif

              @if($internship?->is_finished)
                {{-- Magang sudah ditutup Kaprodi: catatan jadi jejak akademik yang
                     sah. Tombolnya dihilangkan, bukan cuma ditolak server. --}}
                <div style="margin-top:8px;font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;">
                  <x-icon name="lock" :size="13"/>
                  Magang sudah selesai — catatan terkunci.
                </div>
              @else
              <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;"
                onclick="document.getElementById('nf-{{ $lb->id }}').style.display = (document.getElementById('nf-{{ $lb->id }}').style.display==='block'?'none':'block')">
                @if($hasNote)<x-icon name="pencil" :size="14"/> Edit Catatan @else+ Catatan @endif
              </button>

              <div id="nf-{{ $lb->id }}" class="comment-form" style="display:none;">
                <form method="POST" action="{{ route('dosen.mahasiswa.logbook.note', [$student, $lb]) }}">
                  @csrf
                  <textarea name="note" class="comment-input" placeholder="Tambahkan catatan..." required>{{ $lb->lecturer_note }}</textarea>
                  <div class="comment-actions">
                    <button type="submit" class="btn btn-primary btn-sm"><x-icon name="send" :size="14"/> Kirim</button>
                    @if($hasNote)
                    <button type="button" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"
                      onclick="confirmDialog('Hapus catatan?', function(){document.getElementById('del-note-{{ $lb->id }}').submit()}, 'danger')"><x-icon name="trash" :size="14"/> Hapus</button>
                    @endif
                  </div>
                </form>
                @if($hasNote)
                <form id="del-note-{{ $lb->id }}" method="POST" action="{{ route('dosen.mahasiswa.logbook.note.hapus', [$student, $lb]) }}" style="display:none;">
                  @csrf
                  @method('DELETE')
                </form>
                @endif
              </div>
              @endif
            </div>
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:40px;color:var(--text-muted);">
          <p>Tidak ada logbook pada filter ini.</p>
        </div>
        @endforelse
      </div>

      {{-- TAB: LAPORAN AKHIR --}}
      <div id="tab-laporan" class="tab-pane" style="padding-bottom:8px;">
        @php $rpt = $student->report; @endphp
        @if(!$rpt)
          <p style="color:var(--text-muted);font-size:13px;padding:12px 0;">Mahasiswa belum mengunggah laporan akhir.</p>
        @else
          @php
            $rBadge = match($rpt->status) {
              'approved' => ['var(--success-bg)', 'var(--success-text)', 'Disetujui'],
              'rejected' => ['var(--danger-bg)', 'var(--danger)', 'Perlu Revisi'],
              default    => ['var(--warn-bg)', 'var(--warn-text)', 'Menunggu'],
            };
          @endphp
          <div class="bimb-item open">
            <div class="bimb-header">
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $rpt->title }}</div>
                <div style="font-size:11px;color:var(--text-muted);">Diunggah {{ $rpt->updated_at->format('d M Y, H:i') }}</div>
              </div>
              <span style="font-size:11px;padding:2px 9px;border-radius:20px;font-weight:600;background:{{ $rBadge[0] }};color:{{ $rBadge[1] }};">
                {{ $rBadge[2] }}
              </span>
            </div>
            <div class="bimb-body" style="display:block;">
              <div class="file-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <a href="{{ route('berkas.laporan', $rpt) }}" target="_blank">Lihat File Laporan</a>
              </div>

              @if($rpt->lecturer_note)
                <div class="lecturer-note-box" style="margin-top:12px;">
                  <strong>{{ $rpt->status === 'approved' ? 'Catatan dosen:' : 'Catatan revisi:' }}</strong> {{ $rpt->lecturer_note }}
                </div>
              @endif

              @if($rpt->status === 'approved')
                <div style="margin-top:12px;font-size:12px;color:var(--text-muted);"><x-icon name="check" :size="13"/> Laporan ini sudah disetujui.</div>
              @elseif($rpt->status === 'rejected')
                <div style="margin-top:12px;font-size:12px;color:var(--text-muted);"><x-icon name="x" :size="13"/> Diminta revisi — menunggu mahasiswa mengirim ulang.</div>
              @else
                {{-- Approve Form --}}
                <form method="POST" action="{{ route('dosen.mahasiswa.laporan.approve', [$student, $rpt]) }}"
                      style="margin-top:12px;" data-confirm="Setujui laporan akhir ini?">
                  @csrf
                  <textarea name="note" class="note-input" placeholder="Catatan untuk mahasiswa (opsional)..."></textarea>
                  <div class="action-row">
                    <button type="submit" class="btn-approve"><x-icon name="check" :size="14"/> Setujui</button>
                    <button type="button" class="btn-revisi" onclick="submitRevisiLaporan(this)"><x-icon name="x" :size="14"/> Revisi</button>
                  </div>
                </form>
                <form id="form-revisi-laporan" method="POST"
                      action="{{ route('dosen.mahasiswa.laporan.revisi', [$student, $rpt]) }}" style="display:none;">
                  @csrf
                  <input type="hidden" name="note" id="note-revisi-laporan">
                </form>
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}

@if(request()->has('filter') || request()->has('period') || session('tab') === 'logbook')
document.addEventListener('DOMContentLoaded', function () {
  switchTab('logbook', document.querySelectorAll('.tab-btn')[1]);
});
@endif

// Dibuka dari notifikasi: pindah ke tab yang benar, gulir ke itemnya, lalu
// soroti sebentar. Tanpa ini anchor tak berguna karena tab lain disembunyikan.
document.addEventListener('DOMContentLoaded', function () {
  var hash = window.location.hash.slice(1);
  if (!hash) return;

  var tabs = document.querySelectorAll('.tab-btn');
  var peta = { bimbingan: 0, logbook: 1, laporan: 2 };
  var nama = hash.split('-')[0];

  if (peta[nama] !== undefined && tabs[peta[nama]]) {
    switchTab(nama, tabs[peta[nama]]);
  }

  var el = document.getElementById(hash);
  if (!el) return;

  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  el.style.transition = 'box-shadow .3s';
  el.style.boxShadow = '0 0 0 3px var(--primary)';
  setTimeout(function () { el.style.boxShadow = ''; }, 2200);

  var body = el.querySelector('.bimb-body');
  if (body) el.classList.add('open');
});

function submitRevisi(btn, id) {
  const noteEl = btn.closest('form').querySelector('textarea[name="note"]');
  const note   = noteEl ? noteEl.value.trim() : '';
  if (!note) {
    alertDialog('Catatan revisi wajib diisi!');
    noteEl.focus();
    return;
  }
  confirmDialog('Tandai bimbingan ini untuk revisi?', function () {
    document.getElementById('note-revisi-' + id).value = note;
    document.getElementById('form-revisi-' + id).submit();
  }, 'danger');
}

function submitRevisiLaporan(btn) {
  const noteEl = btn.closest('form').querySelector('textarea[name="note"]');
  const note   = noteEl ? noteEl.value.trim() : '';
  if (!note) {
    alertDialog('Catatan revisi wajib diisi!');
    noteEl.focus();
    return;
  }
  confirmDialog('Tandai laporan ini untuk revisi?', function () {
    document.getElementById('note-revisi-laporan').value = note;
    document.getElementById('form-revisi-laporan').submit();
  }, 'danger');
}
</script>
@endpush
