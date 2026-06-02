@php $sbUser = Auth::user(); $sbCompany = $sbUser->company; @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SITAMA</div>
    <div class="sub">Portal Industri</div>
  </div>

  <nav class="nav">
    <a class="nav-item {{ request()->routeIs('industri.dashboard') ? 'active' : '' }}" href="{{ route('industri.dashboard') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Dashboard
    </a>
    <a class="nav-item {{ request()->routeIs('industri.lowongan.*') ? 'active' : '' }}" href="{{ route('industri.lowongan.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
      </svg>
      Kelola Lowongan
    </a>
    <a class="nav-item {{ request()->routeIs('industri.pelamar.*') ? 'active' : '' }}" href="{{ route('industri.pelamar.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
      </svg>
      Review Pelamar
    </a>
    <a class="nav-item {{ request()->routeIs('industri.profile') ? 'active' : '' }}" href="{{ route('industri.profile') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/>
      </svg>
      Profil Perusahaan
    </a>
  </nav>

  <div class="sb-user">
    @php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $sbCompany->name ?? $sbUser->name ?? '')))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;background:rgba(255,255,255,0.18);color:#fff;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbCompany->name ?? $sbUser->name }}</div>
      <div class="urole">Perusahaan</div>
    </div>
  </div>
</div>
