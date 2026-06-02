@php
  $authUser    = Auth::user();
  $authCompany = $authUser->company;
  $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $authCompany->name ?? $authUser->name ?? '')))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
  $verified = ($authCompany->verification_status ?? '') === 'verified';
@endphp
<div class="topbar">
  <div class="tb-title">{{ $title ?? 'Dashboard' }}</div>
  <div class="tb-right">
    @if($verified)
      <span style="font-size:11px;color:#16a34a;background:#dcfce7;border:1px solid #86efac;padding:3px 10px;border-radius:20px;font-weight:600;">✓ Terverifikasi</span>
    @else
      <span style="font-size:11px;color:#92400e;background:#fef9c3;border:1px solid #fde047;padding:3px 10px;border-radius:20px;font-weight:600;">⏳ Menunggu Verifikasi</span>
    @endif
    <div class="avatar" style="width:34px;height:34px;font-size:11px;background:var(--primary-light);color:var(--primary-text);"
         title="{{ $authCompany->name ?? $authUser->name }}">
      {{ $initials }}
    </div>
  </div>
</div>
