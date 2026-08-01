@php
  $sbUser = Auth::user();
  // Penanda: hal yang menunggu keputusan kaprodi, dihitung persis sama dengan
  // tab tujuannya supaya angkanya cocok setelah menu diklik.
  //   Data Mahasiswa    -> tab "Menunggu" (mahasiswa baru daftar, belum bisa masuk)
  //   Pengajuan Magang  -> tab "Menunggu" (permintaan akun industri)
  $mhsMenunggu      = \App\Models\Student::where('status', 'pending')->count();
  $pengajuanMenunggu = \App\Models\CompanyRequest::where('status', 'pending')->count();
@endphp
<div class="sidebar">
  <div class="sb-logo">
    <div class="name">SIMAMA</div>
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
      @if($mhsMenunggu > 0)
        <span class="nav-badge" title="{{ $mhsMenunggu }} mahasiswa baru mendaftar dan menunggu persetujuan akun">
          {{ $mhsMenunggu > 99 ? '99+' : $mhsMenunggu }}
        </span>
      @endif
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
      @if($pengajuanMenunggu > 0)
        <span class="nav-badge" title="{{ $pengajuanMenunggu }} pengajuan akun industri menunggu review">
          {{ $pengajuanMenunggu > 99 ? '99+' : $pengajuanMenunggu }}
        </span>
      @endif
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.lowongan.*') ? 'active' : '' }}" href="{{ route('kaprodi.lowongan.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
      Lowongan Magang
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.seminar.*') ? 'active' : '' }}" href="{{ route('kaprodi.seminar.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
      Seminar
    </a>
    <a class="nav-item {{ request()->routeIs('kaprodi.chatbot.*') ? 'active' : '' }}" href="{{ route('kaprodi.chatbot.index') }}">
      <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      FAQ Chatbot
    </a>
  </nav>

  <a class="sb-user {{ request()->routeIs('kaprodi.profile') ? 'sb-user-active' : '' }}"
     href="{{ route('kaprodi.profile') }}" style="text-decoration:none;">
    <x-avatar :user="$sbUser" class="avatar" :size="36" :font="12" />
    <div>
      <div class="uname">{{ $sbUser->name }}</div>
      <div class="urole">Kaprodi</div>
    </div>
  </a>
</div>
