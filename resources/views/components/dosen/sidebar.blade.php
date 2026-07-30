@php $sbUser = Auth::user(); @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SIMAMA</div>
    <div class="sub">Portal Dosen</div>
  </div>

  <nav class="nav">
    <a class="nav-item {{ request()->routeIs('dosen.dashboard') ? 'active' : '' }}"
       href="{{ route('dosen.dashboard') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Dashboard
    </a>
    <a class="nav-item {{ request()->routeIs('dosen.seminar.*') ? 'active' : '' }}"
       href="{{ route('dosen.seminar.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
      Seminar Bimbingan
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('dosen.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('dosen.profile') }}" style="text-decoration:none;">
    <x-avatar :user="$sbUser" class="avatar" :size="36" :font="12" />
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Dosen</div>
    </div>
  </a>
</div>
