@extends('layouts.kaprodi')
@section('title', 'Verifikasi Industri')
@php $title = 'Verifikasi Industri'; @endphp

@push('styles')
<style>
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab {
  padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;
  font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;
  cursor:pointer;text-decoration:none;transition:all .15s;
}
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

.ind-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px; }
.ind-head { display:flex;align-items:flex-start;gap:14px; }
.ind-av   { width:48px;height:48px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;background:#f3e8ff;color:#6b21a8; }
.ind-info { flex:1;min-width:0; }
.ind-name { font-size:15px;font-weight:700;color:var(--text); }
.ind-field{ font-size:12px;color:var(--text-muted);margin-top:1px; }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-pending  { background:#f3e8ff;color:#7c3aed; }
.st-verified { background:#dcfce7;color:#16a34a; }
.st-rejected { background:#fee2e2;color:#dc2626; }
.ind-detail { margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted);display:flex;flex-direction:column;gap:5px; }
.ind-detail .row { display:flex;gap:8px; }
.ind-detail .row svg { flex-shrink:0;margin-top:2px; }
.reject-reason { background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:12px;color:#991b1b; }
.ind-actions { display:flex;gap:8px;margin-top:14px; }
.btn-verify { background:#1e3a6e;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-reject { background:#fff;color:#dc2626;border:1.5px solid #dc2626;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-verify:hover { background:#2d3e6e; }
.btn-reject:hover { background:#fef2f2; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('kaprodi.industri.index') }}">
  <input type="hidden" name="status" value="{{ $status }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari nama perusahaan..." value="{{ request('search') }}">
  </div>
</form>

{{-- Tabs --}}
<div class="filter-tabs">
  @php $tabs = ['pending'=>'Menunggu','verified'=>'Terverifikasi','rejected'=>'Ditolak']; @endphp
  @foreach($tabs as $key => $label)
  <a href="{{ route('kaprodi.industri.index', ['status' => $key, 'search' => request('search')]) }}"
     class="filter-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] }})</a>
  @endforeach
</div>

@forelse($companies as $company)
@php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','',$company->name)))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
<div class="ind-card">
  <div class="ind-head">
    <div class="ind-av">{{ $initials }}</div>
    <div class="ind-info">
      <div class="ind-name">{{ $company->name }}</div>
      <div class="ind-field">{{ $company->field ?? 'Bidang tidak dicantumkan' }}</div>
    </div>
    @php
      $stClass = match($company->verification_status) {
        'verified' => 'st-verified', 'rejected' => 'st-rejected', default => 'st-pending',
      };
      $stLabel = match($company->verification_status) {
        'verified' => '✓ Terverifikasi', 'rejected' => '✕ Ditolak', default => '⏳ Menunggu',
      };
    @endphp
    <span class="st-badge {{ $stClass }}">{{ $stLabel }}</span>
  </div>

  <div class="ind-detail">
    @if($company->address)
    <div class="row">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
      {{ $company->address }}
    </div>
    @endif
    @if($company->email)
    <div class="row">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      {{ $company->email }}
    </div>
    @endif
    @if($company->phone)
    <div class="row">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
      {{ $company->phone }}
    </div>
    @endif
  </div>

  @if($company->verification_status === 'rejected' && $company->rejection_reason)
    <div class="reject-reason"><strong>Alasan ditolak:</strong> {{ $company->rejection_reason }}</div>
  @endif

  @if($company->verification_status === 'pending')
  <div class="ind-actions">
    <form method="POST" action="{{ route('kaprodi.industri.verify', $company) }}" data-confirm="Verifikasi {{ $company->name }}?">
      @csrf
      <button type="submit" class="btn-verify">✓ Verifikasi Akun</button>
    </form>
    <button type="button" class="btn-reject" onclick="openReject({{ $company->id }}, '{{ addslashes($company->name) }}')">✕ Tolak</button>
  </div>
  @endif
</div>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Tidak ada perusahaan pada kategori ini.</p></div>
@endforelse

{{-- Modal Tolak --}}
<div class="modal-overlay" id="modal-reject" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Tolak Verifikasi</div>
      <button class="modal-close" onclick="document.getElementById('modal-reject').classList.remove('open')">✕</button>
    </div>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
      Tolak <strong id="reject-name" style="color:var(--text);"></strong>? Berikan alasan penolakan.
    </p>
    <form id="form-reject" method="POST" action="">
      @csrf
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Alasan Penolakan</label>
        <textarea name="rejection_reason" rows="4" required placeholder="Contoh: Dokumen tidak lengkap..."
          style="width:100%;border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;font-size:13px;font-family:inherit;resize:vertical;outline:none;color:var(--text);"></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-reject').classList.remove('open')">Batal</button>
        <button type="submit" class="btn" style="background:#dc2626;color:#fff;">Tolak</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openReject(companyId, name) {
  document.getElementById('reject-name').textContent = name;
  document.getElementById('form-reject').action = '/kaprodi/industri/' + companyId + '/reject';
  document.getElementById('modal-reject').classList.add('open');
}
</script>
@endpush
