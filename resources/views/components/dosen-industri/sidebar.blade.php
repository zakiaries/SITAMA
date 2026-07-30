@php $sbUser = Auth::user(); @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SIMAMA</div>
    <div class="sub">Portal Industri</div>
  </div>

  <nav class="nav">
    <a class="nav-item {{ request()->routeIs('dosen-industri.dashboard') ? 'active' : '' }}"
       href="{{ route('dosen-industri.dashboard') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Dashboard
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('dosen-industri.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('dosen-industri.profile') }}" style="text-decoration:none;">
    <x-avatar :user="$sbUser" class="avatar" :size="36" :font="12" />
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Pembimbing Industri</div>
    </div>
  </a>
</div>
