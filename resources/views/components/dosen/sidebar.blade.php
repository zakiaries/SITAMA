@php $sbUser = Auth::user(); @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SITAMA</div>
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
  </nav>

  <a class="sb-user {{ request()->routeIs('dosen.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('dosen.profile') }}" style="text-decoration:none;">
    @php $initials = collect(explode(' ', $sbUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;background:rgba(255,255,255,0.18);color:#fff;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Dosen</div>
    </div>
  </a>
</div>
