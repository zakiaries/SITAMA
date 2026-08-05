<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Daftar Penilaian Magang — Dosen Pembimbing</title>
</head>
<body>

@include('mahasiswa.nilai._dokumen')

@php
  /**
   * Susunan baris disalin PERSIS dari form resmi "Form Nilai Dosen Pembimbing",
   * termasuk penomorannya dan baris kepala yang tidak dinilai
   * ("2. Kelengkapan proposal Magang", "2. Bahasa", "3. Isi").
   *
   * Baris yang dinilai mengambil butir rubrik BERURUTAN dari basis data —
   * bukan dicocokkan lewat nama — supaya memperbaiki ejaan di seeder tak
   * diam-diam mengosongkan lembar ini.
   */
  /**
   * Judul komponen disalin apa adanya, termasuk ketidakkonsistenannya: form
   * resmi menulis "bobot nilai 20 %" untuk Proposal tapi "bobot 80 %" untuk
   * Laporan. Menyeragamkannya akan membuat lembar ini berbeda dari yang
   * dikenali bagian akademik.
   */
  $judulKomponen = [
      'Proposal' => 'Proposal (bobot nilai 20 %)',
      'Laporan'  => 'Laporan (bobot 80 %)',
  ];

  $susunan = [
      'Proposal' => [
          ['t' => '1. Tujuan dari sasaran Magang',            'nilai' => true],
          ['t' => '2. Kelengkapan proposal Magang',           'nilai' => false],
          ['t' => 'a. Kesesuaian antara tujuan dan sasaran',  'nilai' => true,  'in' => 1],
          ['t' => 'b. Kesesuaian perencanaan kerja',          'nilai' => true,  'in' => 1],
          ['t' => 'c. Sistematika penulisan',                 'nilai' => true,  'in' => 1],
      ],
      'Laporan' => [
          ['t' => '1. Sistematika penulisan',                 'nilai' => true],
          ['t' => '2. Bahasa',                                'nilai' => false],
          ['t' => 'a. Mudah dan dimengerti',                  'nilai' => true,  'in' => 1],
          ['t' => 'b. Bahasa Indonesia EYD',                  'nilai' => true,  'in' => 1],
          ['t' => '3. Isi',                                   'nilai' => false],
          ['t' => 'a. Kualitas aktivitas mahasiswa',          'nilai' => true,  'in' => 1],
          ['t' => 'b. Pengalaman baru yang diperoleh',        'nilai' => true,  'in' => 1],
          ['t' => 'c. Kemampuan memecahkan masalah',          'nilai' => true,  'in' => 1],
          ['t' => 'd. Kemampuan menyimpulkan',                'nilai' => true,  'in' => 1],
          ['t' => 'e. Kelengkapan lampiran',                  'nilai' => true,  'in' => 1],
      ],
  ];

  $skorButir = function ($detail) {
      return optional($detail?->scores->first())->score;
  };

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

  @foreach($dosen as $komponen)
    @php
      $baris  = $susunan[$komponen->name] ?? [];
      $butir  = $komponen->detailedComponents->values();
      $urutan = 0;   // penunjuk butir rubrik berikutnya
      $judul  = $judulKomponen[$komponen->name]
          ?? $komponen->name . ($komponen->weight !== null ? ' (bobot ' . (int) $komponen->weight . ' %)' : '');
    @endphp
    <tr>
      <td class="no">{{ $loop->iteration }}</td>
      <td class="grup" colspan="11">{{ $judul }}</td>
      <td class="ket"></td>
    </tr>
    @forelse($baris as $b)
      @php
        $skor = null;
        if ($b['nilai']) {
            $skor = $skorButir($butir[$urutan] ?? null);
            $urutan++;
            if ($skor !== null) $semuaSkor->push($skor);
        }
      @endphp
      <tr>
        <td class="no"></td>
        <td><span class="{{ isset($b['in']) ? 'sub2' : 'sub' }}">{{ $b['t'] }}</span></td>
        @for($n = 1; $n <= 10; $n++)
          <td class="sk">{{ $b['nilai'] && (int) round((float) $skor) === $n ? '√' : '' }}</td>
        @endfor
        <td class="ket"></td>
      </tr>
    @empty
      {{-- Rubrik tak dikenali susunan resmi: cetak apa adanya agar lembarnya
           tetap terpakai, bukan tampil kosong tanpa penjelasan. --}}
      @foreach($butir as $detail)
        @php $skor = $skorButir($detail); if ($skor !== null) $semuaSkor->push($skor); @endphp
        <tr>
          <td class="no"></td>
          <td><span class="sub">{{ $detail->name }}</span></td>
          @for($n = 1; $n <= 10; $n++)
            <td class="sk">{{ (int) round((float) $skor) === $n ? '√' : '' }}</td>
          @endfor
          <td class="ket"></td>
        </tr>
      @endforeach
    @endforelse
  @endforeach

  <tr class="total">
    <td colspan="2">Total Nilai</td>
    <td colspan="10" style="text-align:center;">{{ $semuaSkor->sum() ?: '-' }}</td>
    <td class="ket"></td>
  </tr>
  <tr class="total">
    <td colspan="2">Nilai Rata-rata</td>
    <td colspan="10" style="text-align:center;">{{ $nilai['lecturer']['average'] ?? '-' }}</td>
    <td class="ket"></td>
  </tr>
</table>

<div class="note">
  Catatan :<br>
  &#9642; Nilai rata-rata dihitung berbobot: Proposal 20 % + Laporan 80 %.<br>
  &#9642; Nilai Akhir = Nilai rata-rata dari perusahaan + Nilai rata-rata dari dosen pembimbing.<br>
  &#9642; Selanjutnya nilai akhir diserahkan ke Kaprodi, sebagai nilai matakuliah tersebut.
</div>

<table class="foot">
  <tr>
    <td style="width:55%;"></td>
    <td>
      Semarang, {{ now()->translatedFormat('d F Y') }}<br>
      Dosen Pembimbing,
      <div class="sign-space"></div>
      <u>{{ $internship->lecturer->user->name ?? '.....................................' }}</u><br>
      NIP. {{ $internship->lecturer->user->username ?? '' }}
    </td>
  </tr>
</table>

</body>
</html>
