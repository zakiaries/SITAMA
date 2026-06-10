@php
  $authUser = Auth::user();
  $initials = collect(explode(' ', $authUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="topbar">
  <div class="tb-title">{{ $title ?? 'Dashboard' }}</div>
  <div class="tb-right">
    <span style="font-size:11px;color:var(--text-muted);background:var(--primary-light);border:1px solid var(--primary-light);padding:3px 10px;border-radius:20px;font-weight:600;">
      🏭 Pembimbing Industri
    </span>
    <a class="notif-btn" href="{{ route('dosen-industri.notifikasi') }}" style="text-decoration:none;color:inherit;" title="Notifikasi">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      @if(Auth::user()->notifications()->where('is_read', false)->count() > 0)
        <div class="notif-dot"></div>
      @endif
    </a>
    <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);"
         title="{{ $authUser->name }}">
      {{ $initials }}
    </div>
  </div>
</div>
