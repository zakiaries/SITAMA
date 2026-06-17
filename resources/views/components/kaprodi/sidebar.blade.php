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
    <a class="nav-item {{ request()->routeIs('kaprodi.lowongan.*') ? 'active' : '' }}" href="{{ route('kaprodi.lowongan.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
      </svg>
      Lowongan
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('kaprodi.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('kaprodi.profile') }}" style="text-decoration:none;">
    @php $initials = collect(explode(' ', $sbUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;background:rgba(255,255,255,0.18);color:#fff;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Kaprodi</div>
    </div>
  </a>
</div>
