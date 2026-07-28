@extends('layouts.mahasiswa')
@section('title', 'Laporan Akhir')
@php $title = 'Laporan Akhir'; @endphp
@section('content')

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
  @if($errors->any())
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ $errors->first() }}
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
    <div class="page-title">Laporan Akhir Magang</div>
  </div>

  {{-- Status laporan --}}
  @if($report)
    @php
      $badge = match($report->status) {
        'approved' => ['var(--success-bg)', 'var(--success-text)', 'Disetujui'],
        'rejected' => ['var(--danger-bg)', 'var(--danger)', 'Perlu Revisi'],
        default    => ['var(--warn-bg)', 'var(--warn-text)', 'Menunggu Persetujuan'],
      };
    @endphp
    <div class="card" style="margin-bottom:16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $report->title }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
            Diunggah {{ $report->updated_at->format('d M Y, H:i') }}
          </div>
        </div>
        <span style="font-size:12px;padding:4px 12px;border-radius:20px;font-weight:600;background:{{ $badge[0] }};color:{{ $badge[1] }};">
          {{ $badge[2] }}
        </span>
      </div>

      <div class="file-badge" style="margin-top:14px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <a href="{{ Storage::url($report->file_path) }}" target="_blank">Lihat File Laporan</a>
      </div>

      @if($report->lecturer_note)
        <div style="margin-top:14px;padding:12px 14px;border-radius:8px;background:{{ $report->status==='rejected' ? 'var(--danger-bg)' : 'var(--warm)' }};border:1px solid {{ $report->status==='rejected' ? '#F0C4BE' : 'var(--border)' }};">
          <div class="field-label" style="margin-bottom:4px;">Catatan Dosen</div>
          <div style="font-size:13px;color:var(--text);">{{ $report->lecturer_note }}</div>
        </div>
      @endif

      @if($report->status === 'approved')
        <div style="margin-top:14px;font-size:13px;color:var(--success-text);font-weight:600;">
          <x-icon name="check" :size="13"/> Laporan akhir Anda telah disetujui dosen pembimbing.
        </div>
      @endif
    </div>
  @endif

  {{-- Form upload: tampil selama laporan belum disetujui dosen (belum ada / pending / ditolak) --}}
  @php $isRevisi = $report && $report->status === 'rejected'; @endphp
  @if(!$report || $report->status !== 'approved')
    <div class="card">
      <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">
        {{ !$report ? 'Unggah Laporan Akhir' : ($isRevisi ? 'Unggah Ulang Laporan (Revisi)' : 'Ganti File Laporan') }}
      </div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
        Format PDF atau Word (.doc/.docx), maksimal 10 MB.
      </div>
      <form method="POST" action="{{ route('mahasiswa.laporan.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label>Judul Laporan (opsional)</label>
          <input type="text" name="title" value="{{ old('title', $report->title ?? '') }}" placeholder="Laporan Akhir Magang">
        </div>
        <div class="form-group">
          <label>File Laporan</label>
          <input type="file" name="file" accept=".pdf,.doc,.docx" required>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
          <button type="submit" class="btn btn-primary">
            {{ !$report ? 'Unggah Laporan' : ($isRevisi ? 'Kirim Ulang' : 'Ganti File') }}
          </button>
        </div>
      </form>
    </div>
  @endif

@endsection
