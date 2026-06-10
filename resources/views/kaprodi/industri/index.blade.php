@extends('layouts.kaprodi')
@section('title', 'Akun Industri')
@php $title = 'Akun Industri'; @endphp

@push('styles')
<style>
.section-tabs { display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border); }
.section-tab {
  padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid transparent;
  font-size:13px;font-weight:700;color:var(--text-muted);text-decoration:none;
  display:flex;align-items:center;gap:8px;transition:all .15s;
}
.section-tab.active { color:var(--primary);border-bottom-color:var(--primary); }
.sec-badge {
  background:#f1f5f9;color:var(--text-muted);font-size:11px;font-weight:700;
  padding:2px 8px;border-radius:20px;
}
.section-tab.active .sec-badge { background:var(--primary-light);color:var(--primary-text); }

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
.st-approved { background:#dcfce7;color:#16a34a; }
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

/* Permintaan mahasiswa */
.req-card { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px; }
.req-head { display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px; }
.req-mhs  { font-size:12px;color:var(--text-muted); }
.req-mhs strong { color:var(--primary); }
.req-cols { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
.req-col-title { font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px; }
.req-detail { font-size:12.5px;color:var(--text);display:flex;flex-direction:column;gap:5px; }
.req-detail .row { display:flex;gap:8px;align-items:flex-start; }
.req-detail .row svg { flex-shrink:0;margin-top:2px;color:var(--text-muted); }
.req-actions { display:flex;gap:8px;margin-top:14px;border-top:1px solid var(--border);padding-top:14px; }
.btn-approve { background:#16a34a;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-approve:hover { background:#15803d; }
.created-info { font-size:12px;color:#16a34a;margin-top:12px;display:flex;align-items:center;gap:6px; }

.cred-card { background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:18px;margin-bottom:16px; }
.cred-title { font-size:14px;font-weight:700;color:#15803d;margin-bottom:4px; }
.cred-warn  { font-size:12px;color:#166534;margin-bottom:14px; }
.cred-grid  { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.cred-block { background:#fff;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px; }
.cred-block .name { font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px; }
.cred-row { display:flex;justify-content:space-between;align-items:center;font-size:12.5px;padding:4px 0; }
.cred-row .k { color:var(--text-muted); }
.cred-row .v { font-weight:700;color:var(--text);font-family:monospace; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

@if(session('credentials'))
  @php $cred = session('credentials'); @endphp
  <div class="cred-card">
    <div class="cred-title">✓ Akun Berhasil Dibuat</div>
    <div class="cred-warn">
      Salin & sampaikan kredensial berikut ke pihak terkait sekarang juga &mdash; password ini
      <strong>tidak akan ditampilkan lagi</strong> setelah halaman ini dimuat ulang.
    </div>
    <div class="cred-grid">
      <div class="cred-block">
        <div class="name">Akun Perusahaan &middot; {{ $cred['company_name'] }}</div>
        <div class="cred-row"><span class="k">Username</span><span class="v">{{ $cred['company_username'] }}</span></div>
        <div class="cred-row"><span class="k">Password</span><span class="v">{{ $cred['company_password'] }}</span></div>
      </div>
      <div class="cred-block">
        <div class="name">Akun Pembimbing Industri &middot; {{ $cred['pic_name'] }}</div>
        <div class="cred-row"><span class="k">Username</span><span class="v">{{ $cred['pic_username'] }}</span></div>
        <div class="cred-row"><span class="k">Password</span><span class="v">{{ $cred['pic_password'] }}</span></div>
      </div>
    </div>
  </div>
@endif

{{-- Section switcher --}}
<div class="section-tabs">
  <a href="{{ route('kaprodi.industri.index', ['section' => 'verifikasi']) }}"
     class="section-tab {{ $section === 'verifikasi' ? 'active' : '' }}">
    Verifikasi Akun
    @if($companyCounts['pending'] > 0)<span class="sec-badge">{{ $companyCounts['pending'] }}</span>@endif
  </a>
  <a href="{{ route('kaprodi.industri.index', ['section' => 'permintaan']) }}"
     class="section-tab {{ $section === 'permintaan' ? 'active' : '' }}">
    Permintaan Mahasiswa
    @if($requestCounts['pending'] > 0)<span class="sec-badge">{{ $requestCounts['pending'] }}</span>@endif
  </a>
</div>

@if($section === 'verifikasi')

  <form method="GET" action="{{ route('kaprodi.industri.index') }}">
    <input type="hidden" name="section" value="verifikasi">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Cari nama perusahaan..." value="{{ request('search') }}">
    </div>
  </form>

  {{-- Tabs --}}
  <div class="filter-tabs">
    @php $tabs = ['pending'=>'Menunggu','verified'=>'Terverifikasi','rejected'=>'Ditolak']; @endphp
    @foreach($tabs as $key => $label)
    <a href="{{ route('kaprodi.industri.index', ['section' => 'verifikasi', 'status' => $key, 'search' => request('search')]) }}"
       class="filter-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $companyCounts[$key] }})</a>
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
      <button type="button" class="btn-reject" onclick="openRejectCompany({{ $company->id }}, '{{ addslashes($company->name) }}')">✕ Tolak</button>
    </div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Tidak ada perusahaan pada kategori ini.</p></div>
  @endforelse

  {{-- Modal Tolak Verifikasi --}}
  <div class="modal-overlay" id="modal-reject-company" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Tolak Verifikasi</div>
        <button class="modal-close" onclick="document.getElementById('modal-reject-company').classList.remove('open')">✕</button>
      </div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
        Tolak <strong id="reject-company-name" style="color:var(--text);"></strong>? Berikan alasan penolakan.
      </p>
      <form id="form-reject-company" method="POST" action="">
        @csrf
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Alasan Penolakan</label>
          <textarea name="rejection_reason" rows="4" required placeholder="Contoh: Dokumen tidak lengkap..."
            style="width:100%;border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;font-size:13px;font-family:inherit;resize:vertical;outline:none;color:var(--text);"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-reject-company').classList.remove('open')">Batal</button>
          <button type="submit" class="btn" style="background:#dc2626;color:#fff;">Tolak</button>
        </div>
      </form>
    </div>
  </div>

@else

  <div class="info-note" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12.5px;color:#1e40af;display:flex;gap:10px;align-items:flex-start;">
    <span style="font-size:16px;">ℹ️</span>
    <span>Permintaan ini diajukan mahasiswa yang magang di perusahaan yang belum terdaftar di SITAMA. Menyetujui akan otomatis membuat akun perusahaan & akun pembimbing industri, lalu menghubungkannya ke data magang mahasiswa terkait.</span>
  </div>

  {{-- Tabs --}}
  <div class="filter-tabs">
    @php $reqTabs = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak']; @endphp
    @foreach($reqTabs as $key => $label)
    <a href="{{ route('kaprodi.industri.index', ['section' => 'permintaan', 'status' => $key]) }}"
       class="filter-tab {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $requestCounts[$key] }})</a>
    @endforeach
  </div>

  @forelse($requests as $req)
  <div class="req-card">
    <div class="req-head">
      <div class="req-mhs">
        Diajukan oleh <strong>{{ $req->student->user->name ?? '-' }}</strong> ({{ $req->student->user->username ?? '-' }})
        &middot; {{ $req->created_at->format('d M Y, H:i') }}
      </div>
      @php
        $stClass = match($req->status) {
          'approved' => 'st-approved', 'rejected' => 'st-rejected', default => 'st-pending',
        };
        $stLabel = match($req->status) {
          'approved' => '✓ Disetujui', 'rejected' => '✕ Ditolak', default => '⏳ Menunggu',
        };
      @endphp
      <span class="st-badge {{ $stClass }}">{{ $stLabel }}</span>
    </div>

    <div class="req-cols">
      <div>
        <div class="req-col-title">Data Perusahaan</div>
        <div class="req-detail">
          <div class="row"><strong>{{ $req->company_name }}</strong></div>
          @if($req->company_field)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            {{ $req->company_field }}
          </div>
          @endif
          @if($req->company_address)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $req->company_address }}
          </div>
          @endif
          @if($req->company_email)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
            {{ $req->company_email }}
          </div>
          @endif
          @if($req->company_phone)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
            {{ $req->company_phone }}
          </div>
          @endif
        </div>
      </div>
      <div>
        <div class="req-col-title">Pembimbing Industri</div>
        <div class="req-detail">
          <div class="row"><strong>{{ $req->pic_name }}</strong></div>
          @if($req->pic_email)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
            {{ $req->pic_email }}
          </div>
          @endif
          @if($req->pic_phone)
          <div class="row">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg>
            {{ $req->pic_phone }}
          </div>
          @endif
        </div>
      </div>
    </div>

    @if($req->status === 'rejected' && $req->rejection_reason)
      <div class="reject-reason"><strong>Alasan ditolak:</strong> {{ $req->rejection_reason }}</div>
    @endif

    @if($req->status === 'approved')
      <div class="created-info">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Akun dibuat: {{ $req->createdCompany?->user?->username ?? '-' }} (perusahaan) &amp; {{ $req->createdLecturer?->user?->username ?? '-' }} (pembimbing industri)
      </div>
    @endif

    @if($req->status === 'pending')
    <div class="req-actions">
      <form method="POST" action="{{ route('kaprodi.industri-request.approve', $req) }}"
            data-confirm="Setujui pengajuan ini? Akun perusahaan & pembimbing industri akan otomatis dibuat dan dihubungkan ke data magang {{ $req->student->user->name ?? 'mahasiswa ini' }}.">
        @csrf
        <button type="submit" class="btn-approve">✓ Setujui &amp; Buat Akun</button>
      </form>
      <button type="button" class="btn-reject" onclick="openRejectRequest({{ $req->id }}, '{{ addslashes($req->company_name) }}')">✕ Tolak</button>
    </div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Tidak ada pengajuan pada kategori ini.</p></div>
  @endforelse

  {{-- Modal Tolak Permintaan --}}
  <div class="modal-overlay" id="modal-reject-request" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Tolak Pengajuan</div>
        <button class="modal-close" onclick="document.getElementById('modal-reject-request').classList.remove('open')">✕</button>
      </div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
        Tolak pengajuan untuk <strong id="reject-request-name" style="color:var(--text);"></strong>? Berikan alasan penolakan.
      </p>
      <form id="form-reject-request" method="POST" action="">
        @csrf
        <div class="form-group">
          <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Alasan Penolakan</label>
          <textarea name="rejection_reason" rows="4" required placeholder="Contoh: Data perusahaan tidak valid..."
            style="width:100%;border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;font-size:13px;font-family:inherit;resize:vertical;outline:none;color:var(--text);"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-reject-request').classList.remove('open')">Batal</button>
          <button type="submit" class="btn" style="background:#dc2626;color:#fff;">Tolak</button>
        </div>
      </form>
    </div>
  </div>

@endif

@endsection

@push('scripts')
<script>
function openRejectCompany(companyId, name) {
  document.getElementById('reject-company-name').textContent = name;
  document.getElementById('form-reject-company').action = '/kaprodi/industri/' + companyId + '/reject';
  document.getElementById('modal-reject-company').classList.add('open');
}
function openRejectRequest(reqId, name) {
  document.getElementById('reject-request-name').textContent = name;
  document.getElementById('form-reject-request').action = '/kaprodi/industri-request/' + reqId + '/reject';
  document.getElementById('modal-reject-request').classList.add('open');
}
</script>
@endpush
