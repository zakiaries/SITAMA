<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Daftar Penilaian Magang — Pembimbing Industri</title>
</head>
<body>

@include('mahasiswa.nilai._dokumen')

@php
  /**
   * Rincian sub-butir tiap komponen disalin PERSIS dari form resmi "Form Nilai
   * Pembimbing Industri". Sub-butir ini TIDAK dinilai satu per satu — nilainya
   * satu per komponen, sesuai keputusan rancangan penilaian — jadi ia dicetak
   * sebagai keterangan di dalam sel yang sama, seperti di formnya.
   *
   * Dicocokkan lewat nama komponen; komponen yang tak dikenali tetap tercetak
   * tanpa rincian, bukan hilang.
   */
  $rincian = [
      'Keterampilan dalam Menjalankan Tugas' => [
          'a. Kesesuaian dalam instruksi',
          'b. Kualitas hasil pekerjaan',
          'c. Ketepatan waktu',
          'd. Kemampuan memecahkan masalah',
      ],
      'Komunikasi' => [
          'a. Bekerja dalam kelompok (kerja sama)',
          'b. Hubungan dengan atasan',
          'c. Hubungan dengan rekan sekerja',
          'd. Hubungan dengan relasi',
      ],
      'Sikap Potensial' => [
          'a. Sikap menghadapi pekerjaan',
          'b. Disiplin kerja',
          'c. Loyalitas/kesetiaan',
          'd. Semangat/Motivasi kerja',
          'e. Penampilan',
      ],
  ];

  $semuaSkor = collect();
@endphp

<table class="nilai">
  <tr>
    <th rowspan="2" style="width:20px;">No</th>
    <th rowspan="2">Komponen yang Dinilai</th>
    <th colspan="10">Nilai</th>
    <th rowspan="2" class="ket">Keterangan</th>
  </tr>
  <tr>@for($n = 1; $n <= 10; $n++)<th class="sk">{{ $n }}</th>@endfor</tr>

  @foreach($industri as $komponen)
    @php
      $skor = optional($komponen->detailedComponents->first()?->scores->first())->score;
      if ($skor !== null) $semuaSkor->push($skor);
      $sub = $rincian[$komponen->name] ?? [];
    @endphp
    <tr>
      <td class="no">{{ $loop->iteration }}</td>
      <td>
        {{ $komponen->name }}{{ $sub ? ' :' : '' }}
        @foreach($sub as $s)<span class="sub">{{ $s }}</span>@endforeach
      </td>
      @for($n = 1; $n <= 10; $n++)
        <td class="sk">{{ (int) round((float) $skor) === $n ? '√' : '' }}</td>
      @endfor
      <td class="ket"></td>
    </tr>
  @endforeach

  <tr class="total">
    <td colspan="2">Total Nilai</td>
    <td colspan="10" style="text-align:center;">{{ $semuaSkor->sum() ?: '-' }}</td>
    <td class="ket"></td>
  </tr>
  <tr class="total">
    <td colspan="2">Nilai Rata-rata</td>
    <td colspan="10" style="text-align:center;">{{ $nilai['industry']['average'] ?? '-' }}</td>
    <td class="ket"></td>
  </tr>
</table>

<div class="note">
  Catatan :<br>
  &#9642; Nilai rata-rata = Total Nilai dibagi {{ $industri->count() }} komponen.<br>
  &#9642; Nilai Akhir = Nilai rata-rata dari perusahaan + Nilai rata-rata dari dosen pembimbing.
</div>

<table class="foot">
  <tr>
    <td style="width:55%;"></td>
    <td>
      Semarang, {{ now()->translatedFormat('d F Y') }}<br>
      Pembimbing Industri,
      <div class="sign-space"></div>
      {{-- Tanpa baris NIP/NIK. Yang tersimpan untuk pembimbing industri adalah
           nama pengguna untuk masuk sistem ("industri1"), bukan nomor induk
           kepegawaian mana pun — mencetaknya sebagai NIP justru memalsukan
           keterangan di dokumen yang ditandatangani. --}}
      ({{ $internship->lecturerIndustry->user->name ?? '.....................................' }})
    </td>
  </tr>
</table>

</body>
</html>
