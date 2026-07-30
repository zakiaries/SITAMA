{{--
  Avatar user: tampilkan foto profil bila ada, kalau tidak inisial namanya.
  Dipakai di SEMUA tempat (sidebar, topbar, dashboard, daftar, detail) supaya
  foto profil tak cuma terlihat di halaman profil sendiri.

  Class SENGAJA tidak diberi default: tiap pemanggil membawa class-nya sendiri
  (.avatar / .hero-av / .student-av / .profile-av) agar tampilannya tak berubah.

  Contoh:
    <x-avatar :user="$sbUser" class="avatar" :size="36" :font="12" />
    <x-avatar :user="$student->user" class="student-av" style="background:#eef;color:#334;" />
--}}
@props(['user' => null, 'size' => null, 'font' => null])

@php
  $avInitials = collect(explode(' ', $user->name ?? ''))
      ->filter()
      ->take(2)
      ->map(fn ($w) => strtoupper($w[0] ?? ''))
      ->join('');

  $avUrl = $user?->photoUrl();

  // Ukuran hanya ditulis bila diminta; kalau tidak, biarkan CSS class yang mengatur.
  $avStyle = 'overflow:hidden;';
  if ($size) {
      $avFont  = $font ?: max(10, (int) round(((int) $size) * 0.34));
      $avStyle .= 'width:' . (int) $size . 'px;height:' . (int) $size . 'px;font-size:' . (int) $avFont . 'px;';
  }
@endphp

<div {{ $attributes->merge(['style' => $avStyle]) }}>
  @if($avUrl)
    <img src="{{ $avUrl }}" alt="Foto profil {{ $user->name }}"
         style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
  @else
    {{ $avInitials !== '' ? $avInitials : '?' }}
  @endif
</div>
