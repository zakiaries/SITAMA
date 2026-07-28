<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
@php
  // Nama hari/bulan dalam Bahasa Indonesia untuk dokumen resmi.
  \Carbon\Carbon::setLocale('id');

  // Logo di-embed sebagai data URI agar pasti tampil di dompdf.
  $logoData = null;
  $logoPath = public_path('images/logo.png');
  if (is_file($logoPath)) {
      $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
  }

  // Semester & tahun akademik diturunkan dari tanggal seminar (kalender Polines:
  // Gasal ~Agu–Jan, Genap ~Feb–Jul).
  $tgl   = $seminar->date ?? now();
  $bulan = (int) $tgl->format('n');
  $tahun = (int) $tgl->format('Y');
  if ($bulan >= 8) {          // Agu–Des → Gasal, TA thn/thn+1
      $semester = 'Gasal'; $ta = $tahun . '/' . ($tahun + 1);
  } elseif ($bulan == 1) {    // Jan → Gasal, TA thn-1/thn
      $semester = 'Gasal'; $ta = ($tahun - 1) . '/' . $tahun;
  } else {                     // Feb–Jul → Genap, TA thn-1/thn
      $semester = 'Genap'; $ta = ($tahun - 1) . '/' . $tahun;
  }

  $presenters = $seminar->presenters;
  $firstMajor = optional(optional($presenters->first())->student)->major;
  $waktu = $seminar->time ? (', pukul ' . $seminar->time . ' WIB') : '';
@endphp
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  @page { margin: 22px 30px; }
  body { color:#111; font-size:11px; }

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

  .para { text-align:justify; margin-bottom:8px; line-height:1.5; }
  table.ident { margin:0 0 14px 6px; font-size:11px; }
  table.ident td { padding:1px 0; vertical-align:top; }
  table.ident td.k { width:118px; }
  table.ident td.s { width:12px; }

  table.att { width:100%; border-collapse:collapse; }
  table.att th, table.att td { border:1px solid #000; padding:5px 7px; font-size:10.5px; }
  table.att th { text-align:center; font-weight:bold; }
  table.att td.no { text-align:center; width:34px; }
  table.att td.nim { width:120px; }
  table.att td.ttd { width:150px; }
  table.att td.ttd .v { color:#0a7; font-size:9px; }
  .empty { text-align:center; color:#777; padding:16px; font-style:italic; }

  .note { font-size:9px; color:#666; margin-top:8px; font-style:italic; }
  table.foot { margin-top:22px; width:100%; }
  table.foot td { vertical-align:top; font-size:11px; text-align:center; }
  .sign-space { height:58px; }
</style>
</head>
<body>

  {{-- ── Kop dokumen ── --}}
  <table class="kop">
    <tr>
      <td class="logo">@if($logoData)<img src="{{ $logoData }}" alt="Logo">@endif</td>
      <td class="judul">Daftar Hadir Mahasiswa dan<br>Berita Acara Seminar Magang</td>
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

  {{-- ── Judul berita acara ── --}}
  <div class="head">
    <div class="b">Berita Acara</div>
    <div class="b">Pelaksanaan Seminar Magang</div>
    <div class="b">Semester {{ $semester }} Tahun Akademik {{ $ta }}</div>
    <div class="b">Program Studi {{ $seminar->program ?? '-' }}</div>
    @if($firstMajor)<div class="b">Jurusan {{ $firstMajor }}</div>@endif
  </div>

  {{-- ── Paragraf pelaksanaan ── --}}
  <div class="para">
    Pada hari ini <strong>{{ $tgl->translatedFormat('l') }}</strong>, tanggal
    {{ $tgl->translatedFormat('d') }} bulan {{ $tgl->translatedFormat('F') }} tahun
    {{ $tgl->translatedFormat('Y') }}{{ $waktu }}, bertempat di
    {{ $seminar->location ?? 'ruang seminar' }} telah dilaksanakan Seminar Magang:
  </div>

  {{-- ── Identitas penyaji ── --}}
  @forelse($presenters as $p)
  <table class="ident">
    @if($presenters->count() > 1)
    <tr><td class="k">Penyaji</td><td class="s">:</td><td><strong>{{ $loop->iteration }}.</strong></td></tr>
    @endif
    <tr><td class="k">Nama</td><td class="s">:</td><td>{{ $p->student->user->name ?? '-' }}</td></tr>
    <tr><td class="k">NIM</td><td class="s">:</td><td>{{ $p->student->user->username ?? '-' }}</td></tr>
    <tr><td class="k">Kelas</td><td class="s">:</td><td>{{ $p->student->the_class ?? '-' }}</td></tr>
    <tr><td class="k">Judul</td><td class="s">:</td><td>{{ optional($p->student->report)->title ?? $seminar->title }}</td></tr>
    <tr><td class="k">Dosen Pembimbing</td><td class="s">:</td><td>{{ $seminar->lecturer->user->name ?? '-' }}</td></tr>
  </table>
  @empty
  <table class="ident">
    <tr><td class="k">Judul</td><td class="s">:</td><td>{{ $seminar->title }}</td></tr>
    <tr><td class="k">Dosen Pembimbing</td><td class="s">:</td><td>{{ $seminar->lecturer->user->name ?? '-' }}</td></tr>
  </table>
  @endforelse

  {{-- ── Daftar hadir audiens (otomatis dari scan QR) ── --}}
  <table class="att">
    <thead>
      <tr>
        <th class="no">No</th>
        <th class="nim">NIM</th>
        <th>Nama Mahasiswa</th>
        <th class="ttd">Tanda Tangan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($seminar->attendances as $i => $a)
      <tr>
        <td class="no">{{ $i + 1 }}</td>
        <td class="nim">{{ $a->nim ?? '-' }}</td>
        <td>{{ $a->name ?? ($a->student->user->name ?? '-') }}</td>
        <td class="ttd"><span class="v">&#10003; Hadir via QR &middot; {{ $a->created_at?->format('d/m/Y H:i') }}</span></td>
      </tr>
      @empty
      <tr><td colspan="4" class="empty">Belum ada audiens yang mengisi daftar hadir.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="note">
    Kolom Tanda Tangan diisi otomatis oleh sistem: kehadiran diverifikasi saat audiens memindai QR
    dan masuk (login) akun SIMAMA — 1 akun = 1 kehadiran. Jumlah hadir: {{ $seminar->attendances->count() }} audiens.
  </div>

  {{-- ── Pengesahan dosen ── --}}
  <table class="foot">
    <tr>
      <td style="width:58%;"></td>
      <td>
        {{ ($seminar->witnessed_at ?? now())->translatedFormat('d F Y') }}<br>
        Mengetahui, Dosen Pembimbing
        <div class="sign-space"></div>
        <div>( {{ $seminar->lecturer->user->name ?? '..........................' }} )</div>
      </td>
    </tr>
  </table>

</body>
</html>
