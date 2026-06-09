@extends('layouts.dosen')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px;
  padding: 28px 24px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 20px;
}
.student-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(45,62,110,0.80);
}
.student-hero > * { position: relative; z-index: 1; }
.hero-avatar {
  width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
  background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 800; color: #fff;
}
.hero-detail { flex: 1; }
.hero-sname  { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px; }
.hero-nim    { display: inline-block; background: rgba(255,255,255,0.18); color: rgba(255,255,255,0.9); font-size: 12px; padding: 2px 12px; border-radius: 20px; margin-bottom: 6px; }
.hero-email  { font-size: 12px; color: rgba(255,255,255,0.7); }

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
  background: #f1f5f9; color: var(--primary); font-weight: 700;
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
.bimb-item.status-rejected { border-color: #fca5a5; background: #fff8f8; }
.bimb-item.status-approved { border-color: #86efac; background: #f8fff9; }
.bimb-header {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  cursor: pointer; user-select: none;
}
.bimb-body { padding: 0 16px 16px; border-top: 1px solid var(--border); }
.status-circle {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;
}
.sc-approved { background: #dcfce7; color: #16a34a; }
.sc-rejected  { background: #fee2e2; color: #dc2626; }
.sc-pending   { background: #fef9c3; color: #854f0b; }
.sc-progress  { background: #eff6ff; color: #2563eb; }
.action-row { display: flex; gap: 8px; margin-top: 14px; }
.btn-approve { background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-revisi  { background: #fff; color: #dc2626; border: 1.5px solid #dc2626; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-approve:hover { background: #15803d; }
.btn-revisi:hover  { background: #fef2f2; }
.note-input {
  width: 100%; padding: 9px 12px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 12px; font-family: inherit; resize: vertical;
  min-height: 70px; outline: none; color: var(--text); margin-top: 10px;
}
.note-input:focus { border-color: var(--primary); }
.lecturer-note-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 12px; margin-top: 10px; font-size: 12px; color: #1e40af; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ session('error') }}
  </div>
@endif

{{-- Back Button --}}
<div style="margin-bottom:16px;">
  <a href="{{ route('dosen.dashboard') }}" class="btn btn-outline btn-sm">← Kembali</a>
</div>

{{-- Hero --}}
@php
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="student-hero">
  <div class="hero-avatar">{{ $initials }}</div>
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
        <div class="stat-box-icon">🎓</div>
        <div class="stat-box-val">{{ $student->guidances->count() }}</div>
        <div class="stat-box-lbl">Bimbingan</div>
      </div>
      <div class="stat-box">
        <div class="stat-box-icon">📒</div>
        <div class="stat-box-val">{{ $student->logBooks->count() }}</div>
        <div class="stat-box-lbl">Total Log</div>
      </div>
    </div>

    {{-- Info Magang --}}
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header" style="margin-bottom:12px;">
        <div class="card-title">Info Magang</div>
        @if($internship->is_finished)
          <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Selesai</span>
        @else
          <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
        @endif
      </div>
      <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);font-weight:600;">{{ $internship->company->name ?? '-' }}</div></div>
      <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
      <div class="info-row"><div class="info-key">Mulai</div><div class="info-val">{{ $internship->start_date->format('d M Y') }}</div></div>
      <div class="info-row">
        <div class="info-key">Selesai</div>
        <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
      </div>
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
          Log Book ({{ $student->logBooks->count() }})
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
            'approved' => '✓', 'rejected' => '✕', default => '—',
          };
          $itemClass = match($g->status) {
            'approved' => 'status-approved', 'rejected' => 'status-rejected', default => '',
          };
        @endphp
        <div class="bimb-item {{ $itemClass }}">
          <div class="bimb-header" onclick="toggle(this)">
            <div class="status-circle {{ $scClass }}">{{ $scIcon }}</div>
            <div style="flex:1;">
              <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $g->title }}</div>
              <div style="font-size:11px;color:var(--text-muted);">{{ $g->date->format('d M Y') }}</div>
            </div>
            <span style="font-size:11px;padding:2px 9px;border-radius:20px;font-weight:600;
              background:{{ $g->status==='approved'?'#dcfce7':($g->status==='rejected'?'#fee2e2':'#fef9c3') }};
              color:{{ $g->status==='approved'?'#16a34a':($g->status==='rejected'?'#dc2626':'#854f0b') }};">
              {{ ucfirst($g->status) }}
            </span>
            <div class="chevron">▾</div>
          </div>
          <div class="bimb-body">
            <div class="field-label">Aktivitas</div>
            <div class="field-value" style="margin-bottom:10px;">{{ $g->activity }}</div>
            @if($g->lecturer_note)
              <div class="lecturer-note-box">
                <strong>{{ $g->status === 'approved' ? 'Catatan dosen:' : 'Catatan sebelumnya:' }}</strong> {{ $g->lecturer_note }}
              </div>
            @endif

            @if(in_array($g->status, ['approved', 'rejected']))
              {{-- Keputusan sudah diambil: tombol disembunyikan --}}
              <div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
                @if($g->status === 'approved')
                  ✓ Bimbingan ini sudah disetujui.
                @else
                  ✕ Diminta revisi — menunggu mahasiswa mengirim ulang.
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
                  <button type="submit" class="btn-approve">✓ Setujui</button>
                  <button type="button" class="btn-revisi"
                    onclick="submitRevisi(this, {{ $g->id }})">✕ Revisi</button>
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
        @forelse($student->logBooks as $lb)
        <div class="bimb-item">
          <div class="bimb-header" onclick="toggle(this)">
            <div style="flex:1;">
              <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $lb->title }}</div>
              <div style="font-size:11px;color:var(--text-muted);">{{ $lb->date->format('d M Y') }}</div>
            </div>
            <div class="chevron">▾</div>
          </div>
          <div class="bimb-body">
            <div class="field-label">Aktivitas</div>
            <div class="field-value" style="margin-bottom:10px;">{{ $lb->activity }}</div>
            @if($lb->lecturer_note)
              <div class="lecturer-note-box"><strong>Catatan:</strong> {{ $lb->lecturer_note }}</div>
            @endif
            <form method="POST" action="{{ route('dosen.mahasiswa.logbook.note', [$student, $lb]) }}"
                  style="margin-top:10px;">
              @csrf
              <textarea name="note" class="note-input"
                placeholder="Tambahkan catatan...">{{ $lb->lecturer_note }}</textarea>
              <div class="action-row">
                <button type="submit" class="btn btn-primary btn-sm">
                  {{ $lb->lecturer_note ? '✏ Edit Catatan' : '💾 Simpan Catatan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
        @empty
          <p style="color:var(--text-muted);font-size:13px;padding:12px 0;">Belum ada data log book.</p>
        @endforelse
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
</script>
@endpush
