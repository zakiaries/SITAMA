@extends('layouts.dosen-industri')
@section('title', 'Notifikasi')
@php $title = 'Notifikasi'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <div class="page-title">Notifikasi</div>
    @if($unreadCount > 0)
      <form method="POST" action="{{ route('dosen-industri.notifikasi.read-all') }}">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Tandai semua dibaca ({{ $unreadCount }})</button>
      </form>
    @endif
  </div>

  @forelse($notifications as $notif)
  <div class="alert-box" style="{{ $notif->is_read ? 'opacity:0.65;' : '' }}">
    <div class="alert-icon">
      <svg width="16" height="16" fill="none" stroke="#854f0b" stroke-width="2" viewBox="0 0 24 24">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
    </div>
    <div style="flex:1;">
      <div class="alert-title">
        {{ $notif->message }}
        @unless($notif->is_read)
          <span class="badge" style="background:#fee2e2;color:#dc2626;margin-left:6px;">Baru</span>
        @endunless
      </div>
      @if($notif->detail_text)
        <div class="alert-body">{{ $notif->detail_text }}</div>
      @else
        <div class="alert-body">{{ ucfirst($notif->category) }}</div>
      @endif
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
      <div class="alert-time">{{ $notif->date->format('d M Y') }}</div>
      @unless($notif->is_read)
        <form method="POST" action="{{ route('dosen-industri.notifikasi.read', $notif->id) }}">
          @csrf
          <button type="submit" class="btn btn-outline btn-sm">Tandai dibaca</button>
        </form>
      @endunless
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada notifikasi.</p>
  </div>
  @endforelse

  @if($notifications->hasPages())
    <div style="margin-top:16px;">
      {{ $notifications->links() }}
    </div>
  @endif

@endsection
