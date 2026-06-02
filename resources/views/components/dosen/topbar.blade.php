@php
  $authUser = Auth::user();
  $initials = collect(explode(' ', $authUser->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="topbar">
  <div class="tb-title">{{ $title ?? 'Dashboard' }}</div>
  <div class="tb-right">
    <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);"
         title="{{ $authUser->name }}">
      {{ $initials }}
    </div>
  </div>
</div>
