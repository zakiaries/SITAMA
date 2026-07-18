@php $sbUser = Auth::user(); @endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SITAMA</div>
    <div class="sub">Sistem Informasi Magang</div>
  </div>

  @php
    $kampusActive = request()->routeIs('mahasiswa.bimbingan')
        || request()->routeIs('mahasiswa.laporan')
        || request()->routeIs('mahasiswa.seminar');
    $industriActive = request()->routeIs('mahasiswa.magang-saya')
        || request()->routeIs('mahasiswa.internship-groups.*')
        || request()->routeIs('mahasiswa.lowongan*')
        || request()->routeIs('mahasiswa.ajukan-magang');
  @endphp

  <nav class="nav">
    <a class="nav-item {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}" href="{{ route('mahasiswa.dashboard') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Home
    </a>

    {{-- Kampus --}}
    <div class="nav-group {{ $kampusActive ? 'open' : '' }}">
      <div class="nav-item nav-group-header" onclick="this.parentElement.classList.toggle('open')">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Kampus
        <span class="nav-caret">▾</span>
      </div>
      <div class="nav-sub">
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.bimbingan') ? 'active' : '' }}" href="{{ route('mahasiswa.bimbingan') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
          Bimbingan
        </a>
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.laporan') ? 'active' : '' }}" href="{{ route('mahasiswa.laporan') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          Laporan Akhir
        </a>
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.seminar') ? 'active' : '' }}" href="{{ route('mahasiswa.seminar') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          Seminar
        </a>
      </div>
    </div>

    {{-- Industri --}}
    <div class="nav-group {{ $industriActive ? 'open' : '' }}">
      <div class="nav-item nav-group-header" onclick="this.parentElement.classList.toggle('open')">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        Industri
        <span class="nav-caret">▾</span>
      </div>
      <div class="nav-sub">
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.magang-saya') || request()->routeIs('mahasiswa.internship-groups.*') ? 'active' : '' }}" href="{{ route('mahasiswa.magang-saya') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
          Magang Saya
        </a>
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.lowongan*') ? 'active' : '' }}" href="{{ route('mahasiswa.lowongan') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
          Lowongan Magang
        </a>
        <a class="nav-item nav-sub-item {{ request()->routeIs('mahasiswa.ajukan-magang') ? 'active' : '' }}" href="{{ route('mahasiswa.ajukan-magang') }}">
          <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          Ajukan Magang
        </a>
      </div>
    </div>

    {{-- Log Book: milik bersama kampus & industri, jadi berdiri sendiri --}}
    <a class="nav-item {{ request()->routeIs('mahasiswa.logbook') ? 'active' : '' }}" href="{{ route('mahasiswa.logbook') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
      Log Book
    </a>

    {{-- Nilai: gabungan dari kampus & industri, jadi berdiri sendiri --}}
    <a class="nav-item {{ request()->routeIs('mahasiswa.nilai') ? 'active' : '' }}" href="{{ route('mahasiswa.nilai') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15a4 4 0 100-8 4 4 0 000 8z"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/></svg>
      Nilai
    </a>

    {{-- Chatbot rekomendasi (TF-IDF + Cosine Similarity) --}}
    <a class="nav-item {{ request()->routeIs('mahasiswa.chatbot') ? 'active' : '' }}" href="{{ route('mahasiswa.chatbot') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Chatbot
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('mahasiswa.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('mahasiswa.profile') }}" style="text-decoration:none;">
    @php $initials = collect(explode(' ', $sbUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
    <div class="avatar" style="width:36px;height:36px;font-size:12px;">
      {{ $initials }}
    </div>
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">{{ ucfirst(str_replace('_', ' ', $sbUser->role)) }}</div>
    </div>
  </a>
</div>
