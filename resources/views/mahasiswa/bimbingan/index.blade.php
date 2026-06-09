@extends('layouts.mahasiswa')
@section('title', 'Bimbingan')
@php $title = 'Bimbingan'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  {{-- Dosen Pembimbing --}}
  <div class="card" style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
    <div class="avatar" style="width:42px;height:42px;font-size:14px;background:var(--primary-light);color:var(--primary-text);">
      @if($lecturer && $lecturer->user)
        {{ collect(explode(' ', $lecturer->user->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('') }}
      @else
        ?
      @endif
    </div>
    <div>
      <div class="field-label">Dosen Pembimbing</div>
      <div style="font-size:14px;font-weight:700;color:var(--text);">
        {{ $lecturer && $lecturer->user ? $lecturer->user->name : 'Belum ditugaskan oleh Kaprodi' }}
      </div>
    </div>
  </div>

  <div class="page-header">
    <div class="page-title">Daftar Bimbingan</div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-bimb').classList.add('open')">+ Tambah Bimbingan</button>
  </div>

  <form method="GET" action="{{ route('mahasiswa.bimbingan') }}">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Pencarian..." value="{{ request('search') }}">
    </div>
  </form>

  @forelse($guidances as $g)
  <div class="exp-item {{ $loop->first ? 'open' : '' }}">
    <div class="exp-header" onclick="toggle(this)">
      <div class="status-dot {{ $g->status === 'approved' ? 'done' : ($g->status === 'rejected' ? 'rejected' : 'pending') }}">
        @if($g->status === 'approved') ✓
        @elseif($g->status === 'rejected') ✕
        @else —
        @endif
      </div>
      <div>
        <div class="exp-title">{{ $g->title }}</div>
        <div class="exp-date">{{ $g->date->format('d/m/Y') }}</div>
      </div>
      <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:{{ $g->status==='approved'?'#dcfce7':($g->status==='rejected'?'#fee2e2':'#fef9c3') }};color:{{ $g->status==='approved'?'#16a34a':($g->status==='rejected'?'#dc2626':'#854f0b') }};">
        {{ ucfirst($g->status) }}
      </span>
      <div class="chevron">▾</div>
    </div>
    <div class="exp-body">
      <div class="field-label">Aktivitas</div>
      <div class="field-value">{{ $g->activity }}</div>
      @if($g->lecturer_note)
      <div class="field-group">
        <div class="field-label">Catatan Dosen</div>
        <div class="field-value">{{ $g->lecturer_note }}</div>
      </div>
      @endif
      @if($g->name_file)
      <div class="file-badge">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <a href="{{ Storage::url($g->name_file) }}" target="_blank">File Bimbingan</a>
      </div>
      @endif
      @if($g->status === 'rejected')
      <div style="margin-top:14px;">
        <button class="btn btn-primary btn-sm"
          onclick="openRevisi({{ $g->id }}, @js($g->title), '{{ $g->date->format('Y-m-d') }}', @js($g->activity))">
          ↺ Revisi & Kirim Ulang
        </button>
      </div>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada data bimbingan.</p>
  </div>
  @endforelse

  {{-- Modal Revisi --}}
  <div class="modal-overlay" id="modal-revisi">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Revisi Bimbingan</div>
        <button class="modal-close" onclick="document.getElementById('modal-revisi').classList.remove('open')">✕</button>
      </div>
      <form method="POST" id="form-revisi" action="" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label>Judul Bimbingan</label>
          <input type="text" name="title" id="revisi-title" required>
        </div>
        <div class="form-group">
          <label>Tanggal</label>
          <input type="date" name="date" id="revisi-date" required>
        </div>
        <div class="form-group">
          <label>Aktivitas / Deskripsi</label>
          <textarea name="activity" id="revisi-activity" rows="4" required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
        </div>
        <div class="form-group">
          <label>Ganti File (opsional)</label>
          <input type="file" name="file" accept=".pdf,.doc,.docx">
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-revisi').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Kirim Ulang</button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  function openRevisi(id, title, date, activity) {
    var form = document.getElementById('form-revisi');
    form.action = '{{ url('mahasiswa/bimbingan') }}/' + id;
    document.getElementById('revisi-title').value = title;
    document.getElementById('revisi-date').value = date;
    document.getElementById('revisi-activity').value = activity;
    document.getElementById('modal-revisi').classList.add('open');
  }
</script>
@endpush
