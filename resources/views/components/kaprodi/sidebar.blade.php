@php $sbUser = Auth::user(); @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SITAMA</div>
    <div class="sub">Portal Kaprodi</div>
  </div>

  <nav class="nav">
    <a class="nav-item {{ request()->routeIs('kaprodi.dashboard') ? 'active' : '' }}" href="{{ route('kaprodi.dashboard') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Dashboard
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.mahasiswa.*') ? 'active' : '' }}" href="{{ route('kaprodi.mahasiswa.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
      </svg>
      Data Mahasiswa
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.dosen.*') ? 'active' : '' }}" href="{{ route('kaprodi.dosen.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
      Data Dosen
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.pengajuan-magang.*') ? 'active' : '' }}" href="{{ route('kaprodi.pengajuan-magang.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
      </svg>
      Pengajuan Magang
      @php $pendingCount = \App\Models\CompanyRequest::where('status','pending')->count(); @endphp
      @if($pendingCount > 0)
        <span style="background:var(--error);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:20px;margin-left:auto;">{{ $pendingCount }}</span>
      @endif
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('kaprodi.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('kaprodi.profile') }}" style="text-decoration:none;">
    @php $initials = collect(explode(' ', $sbUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Kaprodi</div>
    </div>
  </a>
</div>
