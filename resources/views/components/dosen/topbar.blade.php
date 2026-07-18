@php
  $authUser = Auth::user();
  $initials = collect(explode(' ', $authUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="topbar">
  <div class="tb-title">{{ $title ?? 'Dashboard' }}</div>
  <div class="tb-right">
    <a class="notif-btn" href="{{ route('dosen.notifikasi') }}" style="text-decoration:none;color:inherit;" title="Notifikasi">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      @if(Auth::user()->notifications()->where('is_read', false)->count() > 0)
        <div class="notif-dot"></div>
      @endif
    </a>
    <style>
      .user-dd > summary{list-style:none;cursor:pointer;outline:none;}
      .user-dd > summary::-webkit-details-marker{display:none;}
      .user-dd[open] > summary .avatar{box-shadow:0 0 0 3px var(--primary-light);}
      .user-dd .dd-item:hover{background:var(--bg,#f4f5f7);}
    </style>
    <details class="user-dd" style="position:relative;">
      <summary title="{{ $authUser->name }}">
        <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);">{{ $initials }}</div>
      </summary>
      <div style="position:absolute;right:0;top:calc(100% + 10px);min-width:190px;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.13);z-index:200;overflow:hidden;">
        <div style="padding:11px 14px;border-bottom:1px solid var(--border);">
          <div style="font-weight:700;font-size:13px;line-height:1.2;">{{ $authUser->name }}</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ ucfirst(str_replace('_',' ', $authUser->role)) }}</div>
        </div>
        <a class="dd-item" href="{{ route('dosen.profile') }}" style="display:flex;align-items:center;gap:9px;padding:11px 14px;font-size:13px;color:var(--text);text-decoration:none;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Profil Saya
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;border-top:1px solid var(--border);">
          @csrf
          <button class="dd-item" type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:11px 14px;font-size:13px;color:var(--danger);background:none;border:none;cursor:pointer;text-align:left;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </button>
        </form>
      </div>
    </details>
  </div>
</div>
