<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Lembar Nilai Magang</title>
@php
  \Carbon\Carbon::setLocale('id');

  // Logo di-embed sebagai data URI agar pasti tampil di dompdf.
  $logoData = null;
  $logoPath = public_path('images/logo.png');
  if (is_file($logoPath)) {
      $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
  }

  $student = $internship->student;
  $periode = $student?->period;

  // Skor satu butir. Tiap butir hanya dinilai sekali oleh satu penilai, jadi
  // yang diambil skor pertama — bukan rata-rata.
  $skor = fn ($detail) => optional($detail->scores->first())->score;
@endphp
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  body { font-size: 11px; color: #000; margin: 0; }

  table.kop { width:100%; border-collapse:collapse; margin-bottom:14px; }
  table.kop td { border:1px solid #000; vertical-align:middle; padding:6px; }
  table.kop td.logo { width:74px; text-align:center; }
  table.kop td.logo img { width:52px; height:52px; }
  table.kop td.judul { text-align:center; font-weight:bold; font-size:13px; text-transform:uppercase; }
  table.kop td.meta { width:150px; font-size:10px; padding:0; }
  table.kop td.meta table { width:100%; border-collapse:collapse; }
  table.kop td.meta table td { border:none; border-bottom:1px solid #000; padding:2px 5px; }
  table.kop td.meta table tr:last-child td { border-bottom:none; }

  .head { text-align:center; margin:2px 0 12px; line-height:1.45; }
  .head .b { font-weight:bold; text-transform:uppercase; }

  table.ident { margin:0 0 14px 6px; font-size:11px; }
  table.ident td { padding:1px 0; vertical-align:top; }
  table.ident td.k { width:130px; }
  table.ident td.s { width:12px; }

  .sec { font-weight:bold; margin:14px 0 6px; font-size:11.5px; }
  table.nilai { width:100%; border-collapse:collapse; }
  table.nilai th, table.nilai td { border:1px solid #000; padding:4px 7px; font-size:10.5px; }
  table.nilai th { text-align:center; font-weight:bold; background:#eee; }
  table.nilai td.no { text-align:center; width:30px; }
  table.nilai td.sk { text-align:center; width:60px; }
  table.nilai td.grup { font-weight:bold; }
  table.nilai tr.rata td { font-weight:bold; }

  table.akhir { width:100%; border-collapse:collapse; margin-top:10px; }
  table.akhir td { border:1px solid #000; padding:6px 8px; font-size:11px; }
  table.akhir td.lbl { font-weight:bold; }
  table.akhir td.val { text-align:center; width:80px; font-weight:bold; font-size:13px; }

  .note { font-size:9px; color:#555; margin-top:6px; font-style:italic; }
  table.foot { margin-top:26px; width:100%; }
  table.foot td { vertical-align:top; font-size:11px; text-align:center; }
  .sign-space { height:60px; }
</style>
</head>
<body>

  {{-- ── Kop dokumen ── --}}
  <table class="kop">
    <tr>
      <td class="logo">@if($logoData)<img src="{{ $logoData }}" alt="Logo">@endif</td>
      <td class="judul">Lembar Nilai<br>Pelaksanaan Magang</td>
      <td class="meta">
        <table>
          <tr><td>No. PM</td><td>:</td></tr>
          <tr><td>Revisi</td><td>:</td></tr>
          <tr><td>Tanggal</td><td>:</td></tr>
          <tr><td>Halaman</td><td>:</td></tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- Periode diambil dari periode magang mahasiswanya, bukan dari tanggal
       dokumen dicetak — lembar ini bisa saja dicetak semester berikutnya. --}}
  <div class="head">
    <div class="b">Nilai Pelaksanaan Magang</div>
    @if($periode)
      <div class="b">Semester {{ \App\Models\Period::SEMESTER[$periode->semester] ?? $periode->semester }}
        Tahun Akademik {{ $periode->academic_year }}</div>
    @endif
    <div class="b">Program Studi {{ $student->study_program ?? '-' }}</div>
  </div>

  {{-- ── Identitas ── --}}
  <table class="ident">
    <tr><td class="k">Nama Mahasiswa</td><td class="s">:</td><td>{{ $student->user->name ?? '-' }}</td></tr>
    <tr><td class="k">NIM</td><td class="s">:</td><td>{{ $student->user->username ?? '-' }}</td></tr>
    <tr><td class="k">Kelas</td><td class="s">:</td><td>{{ $student->the_class ?? '-' }}</td></tr>
    <tr><td class="k">Tempat Magang</td><td class="s">:</td><td>{{ $internship->company->name ?? '-' }}</td></tr>
    <tr><td class="k">Posisi</td><td class="s">:</td><td>{{ $internship->position ?? '-' }}</td></tr>
    <tr><td class="k">Periode Magang</td><td class="s">:</td><td>
      {{ $internship->start_date?->translatedFormat('d F Y') ?? '-' }} &ndash;
      {{ $internship->end_date?->translatedFormat('d F Y') ?? '-' }}
    </td></tr>
  </table>

  {{-- ── Nilai dosen pembimbing (berbobot) ── --}}
  <div class="sec">A. Penilaian Dosen Pembimbing</div>
  <table class="nilai">
    <tr><th style="width:30px;">No</th><th>Aspek Penilaian</th><th style="width:60px;">Nilai</th></tr>
    @foreach($dosen as $komponen)
      <tr>
        <td class="no">{{ $loop->iteration }}</td>
        <td class="grup" colspan="2">
          {{ $komponen->name }}@if($komponen->weight) (bobot {{ (int) $komponen->weight }}%)@endif
        </td>
      </tr>
      @foreach($komponen->detailedComponents as $detail)
        <tr>
          <td class="no"></td>
          <td>{{ $detail->name }}</td>
          <td class="sk">{{ $skor($detail) ?? '-' }}</td>
        </tr>
      @endforeach
    @endforeach
    <tr class="rata">
      <td colspan="2">Rata-rata berbobot (Proposal 20% + Laporan 80%)</td>
      <td class="sk">{{ $nilai['lecturer']['average'] ?? '-' }}</td>
    </tr>
  </table>

  {{-- ── Nilai pembimbing industri ── --}}
  <div class="sec">B. Penilaian Pembimbing Industri</div>
  <table class="nilai">
    <tr><th style="width:30px;">No</th><th>Aspek Penilaian</th><th style="width:60px;">Nilai</th></tr>
    @foreach($industri as $komponen)
      <tr>
        <td class="no">{{ $loop->iteration }}</td>
        <td>{{ $komponen->name }}</td>
        <td class="sk">{{ $skor($komponen->detailedComponents->first()) ?? '-' }}</td>
      </tr>
    @endforeach
    <tr class="rata">
      <td colspan="2">Rata-rata ({{ $industri->count() }} komponen)</td>
      <td class="sk">{{ $nilai['industry']['average'] ?? '-' }}</td>
    </tr>
  </table>

  {{-- ── Nilai akhir ── --}}
  <table class="akhir">
    <tr>
      <td class="lbl">Nilai Akhir (Dosen Pembimbing + Pembimbing Industri)</td>
      <td class="val">{{ $nilai['final'] ?? '-' }}</td>
    </tr>
  </table>
  <div class="note">Skala tiap aspek 1&ndash;10. Nilai akhir merupakan penjumlahan rata-rata kedua penilai, maksimal 20.</div>

  {{-- ── Tanda tangan ── --}}
  <table class="foot">
    <tr>
      <td>
        Pembimbing Industri,
        <div class="sign-space"></div>
        <u>{{ $internship->lecturerIndustry->user->name ?? '.....................................' }}</u>
      </td>
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
