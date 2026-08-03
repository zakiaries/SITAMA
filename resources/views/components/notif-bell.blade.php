{{--
  Lonceng notifikasi + panel melayang, dipakai keempat peran.

  Dulu lonceng hanya tautan ke halaman notifikasi, sementara kabar terbarunya
  ditempel sebagai kartu biasa di dashboard mahasiswa — mendorong turun isi
  halaman, tak bisa diklik, tak bisa ditutup, dan tak terlihat sama sekali dari
  halaman lain. Sekarang isinya melayang di atas halaman, bisa dibuka dari mana
  pun, dan tiap barisnya bisa langsung dibuka atau ditandai dibaca.

  Prop $peran = awalan nama route ('mahasiswa', 'dosen', 'dosen-industri',
  'kaprodi'), karena tiap peran punya controller notifikasinya sendiri.
--}}
@props(['peran'])

@php
  $penerima    = Auth::user();
  $terbaru     = $penerima->notifications()->orderByDesc('created_at')->take(5)->get();
  $belumDibaca = $penerima->notifications()->where('is_read', false)->count();
@endphp

<details class="notif-dd">
  <summary class="notif-btn" title="Notifikasi" aria-label="Notifikasi">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
    </svg>
    @if($belumDibaca > 0)
      <div class="notif-dot"></div>
    @endif
  </summary>

  <div class="notif-panel">
    <div class="notif-panel-head">
      <span>Notifikasi @if($belumDibaca > 0)<span class="notif-count">{{ $belumDibaca }}</span>@endif</span>
      @if($belumDibaca > 0)
        <form method="POST" action="{{ route($peran . '.notifikasi.read-all') }}" style="margin:0;">
          @csrf
          <button type="submit" class="notif-mark-all">Tandai semua</button>
        </form>
      @endif
    </div>

    @forelse($terbaru as $notif)
      <div class="notif-item {{ $notif->is_read ? 'notif-item-read' : '' }}">
        <div class="notif-item-main">
          <a class="notif-item-link" href="{{ route($peran . '.notifikasi.open', $notif->id) }}">{{ $notif->message }}</a>
          <div class="notif-item-body">{{ $notif->detail_text ?: ucfirst($notif->category) }}</div>
          <div class="notif-item-time">{{ $notif->date->format('d M Y') }}</div>
        </div>
        @unless($notif->is_read)
          <form method="POST" action="{{ route($peran . '.notifikasi.read', $notif->id) }}" class="notif-item-act">
            @csrf
            <button type="submit" title="Tandai dibaca" aria-label="Tandai dibaca">✓</button>
          </form>
        @endunless
      </div>
    @empty
      <div class="notif-empty">Belum ada notifikasi.</div>
    @endforelse

    <a class="notif-all" href="{{ route($peran . '.notifikasi') }}">Lihat semua notifikasi</a>
  </div>
</details>

<script>
  // Dua panel di topbar (notifikasi & menu pengguna) tak boleh terbuka
  // bersamaan — keduanya melayang di pojok kanan dan akan saling menimpa.
  (function () {
    var panels = document.querySelectorAll('.topbar details');

    panels.forEach(function (panel) {
      panel.addEventListener('toggle', function () {
        if (!panel.open) return;
        panels.forEach(function (lain) {
          if (lain !== panel) lain.open = false;
        });
      });
    });

    // Klik di luar panel menutupnya, seperti dropdown pada umumnya.
    document.addEventListener('click', function (e) {
      panels.forEach(function (panel) {
        if (panel.open && !panel.contains(e.target)) panel.open = false;
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      panels.forEach(function (panel) { panel.open = false; });
    });
  })();
</script>
