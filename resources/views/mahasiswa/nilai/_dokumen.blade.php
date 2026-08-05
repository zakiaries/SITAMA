{{--
  Kop, gaya, dan identitas yang dipakai bersama kedua lembar nilai.

  Lembarnya memang DUA dan terpisah — satu untuk dosen pembimbing, satu untuk
  pembimbing industri — karena masing-masing ditandatangani orang yang berbeda
  dan diserahkan sendiri-sendiri, persis seperti form resminya.

  Judul, susunan identitas, dan petunjuk pengisiannya disalin apa adanya dari
  form resmi Polines. Satu-satunya tambahan adalah baris Periode Magang, supaya
  lembar yang sudah dikumpulkan tak tertukar antar-angkatan.
--}}
@php
  \Carbon\Carbon::setLocale('id');

  $logoData = null;
  $logoPath = public_path('images/logo.png');
  if (is_file($logoPath)) {
      $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
  }

  $student = $internship->student;
  $periode = $student?->period;
@endphp
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { font-size: 11px; color: #000; margin: 0; }

  .judul-dok { text-align:center; font-weight:bold; font-size:14px; text-transform:uppercase;
               letter-spacing:.5px; margin-bottom:14px; }
  table.logo-kop { width:100%; border-collapse:collapse; margin-bottom:6px; }
  table.logo-kop td { vertical-align:middle; padding:0; border:none; }
  table.logo-kop td.l { width:60px; }
  table.logo-kop td.l img { width:52px; height:52px; }

  table.ident { margin:0 0 10px 0; font-size:11px; width:100%; }
  table.ident td { padding:1.5px 0; vertical-align:top; }
  table.ident td.k { width:130px; }
  table.ident td.s { width:10px; }

  .petunjuk { font-size:10.5px; margin:0 0 12px 0; font-style:italic; }

  /* Tabel nilai meniru form resmi: sepuluh kolom skala, penilai mencentang
     salah satunya. Skor yang sudah tersimpan dicetak sebagai centang di kolom
     yang sesuai, jadi lembarnya terbaca sama seperti yang diisi tangan. */
  table.nilai { width:100%; border-collapse:collapse; }
  table.nilai th, table.nilai td { border:1px solid #000; padding:3px 4px; font-size:9.5px; }
  table.nilai th { text-align:center; font-weight:bold; vertical-align:middle; }
  table.nilai td.no { text-align:center; width:20px; vertical-align:top; }
  table.nilai td.sk, table.nilai th.sk { text-align:center; width:15px; }
  table.nilai td.sk { font-weight:bold; }
  table.nilai td.grup { font-weight:bold; }
  table.nilai td.ket, table.nilai th.ket { width:95px; }
  table.nilai tr.total td { font-weight:bold; }
  table.nilai .sub { padding-left:14px; display:block; }
  table.nilai .sub2 { padding-left:26px; display:block; }

  .note { font-size:9px; color:#333; margin-top:8px; line-height:1.55; }
  table.foot { margin-top:18px; width:100%; }
  table.foot td { vertical-align:top; font-size:11px; }
  .sign-space { height:56px; }
</style>

<table class="logo-kop">
  <tr>
    <td class="l">@if($logoData)<img src="{{ $logoData }}" alt="Logo">@endif</td>
    <td><div class="judul-dok">Daftar Penilaian Magang</div></td>
    <td class="l"></td>
  </tr>
</table>

<table class="ident">
  <tr><td class="k">Nama Mahasiswa</td><td class="s">:</td><td>{{ $student->user->name ?? '-' }}</td></tr>
  <tr><td class="k">NIM</td><td class="s">:</td><td>{{ $student->user->username ?? '-' }}</td></tr>
  <tr><td class="k">Tempat Magang</td><td class="s">:</td><td>{{ $internship->company->name ?? '-' }}</td></tr>
  <tr><td class="k">Alamat</td><td class="s">:</td><td>{{ $internship->company->address ?: '-' }}</td></tr>
  @if($periode)
    <tr><td class="k">Periode Magang</td><td class="s">:</td><td>{{ $periode->label }}</td></tr>
  @endif
</table>

<div class="petunjuk">
  Petunjuk Pengisian : Berilah tanda cek (&#8730;) pada setiap ruang/kolom angka 1 sampai 10,
  yang menunjukkan tingkat kompetensi mahasiswa
</div>
