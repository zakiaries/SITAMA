@php
  $authUser = Auth::user();
  $initials = collect(explode(' ', $authUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="topbar">
  <div class="tb-title">{{ $title ?? 'Dashboard' }}</div>
  <div class="tb-right">
    <span style="font-size:11px;color:#92400e;background:#fef9c3;border:1px solid #fde047;padding:3px 10px;border-radius:20px;font-weight:600;">
      ⭐ Superadmin
    </span>
    <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);"
         title="{{ $authUser->name }}">
      {{ $initials }}
    </div>
  </div>
</div>
