  <div class="sidebar">
    <div class="sb-logo">
      <div class="name">SITAMA</div>
      <div class="sub">Sistem Informasi Magang</div>
    </div>

    <nav class="nav">
      <a class="nav-item {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}" href="{{ route('mahasiswa.dashboard') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Home
      </a>
      <a class="nav-item {{ request()->routeIs('mahasiswa.bimbingan') ? 'active' : '' }}" href="{{ route('mahasiswa.bimbingan') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
        Bimbingan
      </a>
      <a class="nav-item {{ request()->routeIs('mahasiswa.logbook') ? 'active' : '' }}" href="{{ route('mahasiswa.logbook') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        Log Book
      </a>
      <a class="nav-item {{ request()->routeIs('mahasiswa.lowongan') ? 'active' : '' }}" href="{{ route('mahasiswa.lowongan') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        Lowongan
      </a>
      <a class="nav-item {{ request()->routeIs('mahasiswa.seminar') ? 'active' : '' }}" href="{{ route('mahasiswa.seminar') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Seminar
      </a>
      <a class="nav-item {{ request()->routeIs('mahasiswa.profile') ? 'active' : '' }}" href="{{ route('mahasiswa.profile') }}">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
    </nav>

    <div class="sb-user">
      <div class="avatar" style="width:36px;height:36px;font-size:12px;background:rgba(255,255,255,0.18);color:#fff;">BS</div>
      <div>
        <div class="uname">Budi Santoso</div>
        <div class="urole">Student</div>
      </div>
    </div>
  </div>
