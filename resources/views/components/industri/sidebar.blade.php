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
    <a class="nav-item {{ request()->routeIs('industri.profile') ? 'active' : '' }}" href="{{ route('industri.profile') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
      </svg>
      Profil Perusahaan
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('industri.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('industri.profile') }}" style="text-decoration:none;">
    @php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $sbCompany->name ?? $sbUser->name ?? '')))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;background:rgba(255,255,255,0.18);color:#fff;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbCompany->name ?? $sbUser->name }}</div>
      <div class="urole">Perusahaan</div>
    </div>
  </a>
</div>
