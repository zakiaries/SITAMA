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
    <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);"
         title="{{ $authUser->name }}">
      {{ $initials }}
    </div>
  </div>
</div>
