<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { font-family: DejaVu Sans, sans-serif; }
  @page { margin: 26px 34px; }
  body { color:#1a1a1a; font-size:12px; }
  .title { text-align:center; margin-bottom:4px; }
  .title h1 { font-size:16px; text-transform:uppercase; letter-spacing:.5px; margin:0; }
  .title .subtitle { font-size:12px; color:#555; margin-top:2px; }
  hr { border:none; border-top:2px solid #222; margin:10px 0 14px; }
  .info { width:100%; font-size:12px; margin-bottom:14px; }
  .info td { padding:2px 0; vertical-align:top; }
  .info td.k { width:130px; color:#555; }
  .info td.s { width:12px; }
  table.att { width:100%; border-collapse:collapse; margin-top:6px; }
  table.att th, table.att td { border:1px solid #999; padding:6px 8px; font-size:11px; vertical-align:middle; }
  table.att th { background:#eef1f6; text-align:left; font-size:10.5px; text-transform:uppercase; letter-spacing:.3px; }
  table.att td.no { text-align:center; width:26px; }
  table.att td.sig { width:120px; text-align:center; }
  table.att td.sig img { max-height:44px; max-width:112px; }
  .empty { text-align:center; color:#888; padding:18px; font-style:italic; }
  .foot { margin-top:26px; width:100%; }
  .foot td { vertical-align:top; font-size:12px; }
  .sign-space { height:64px; }
  .muted { color:#666; font-size:11px; }
</style>
</head>
<body>

  <div class="title">
    <h1>Berita Acara Seminar Magang</h1>
    <div class="subtitle">Daftar Hadir Peserta</div>
  </div>
  <hr>

  <table class="info">
    <tr><td class="k">Judul Seminar</td><td class="s">:</td><td>{{ $seminar->title }}</td></tr>
    <tr><td class="k">Penyaji</td><td class="s">:</td><td>{{ $seminar->student->user->name ?? '-' }}</td></tr>
    <tr><td class="k">Program Studi</td><td class="s">:</td><td>{{ $seminar->program ?? '-' }}</td></tr>
    <tr><td class="k">Hari / Tanggal</td><td class="s">:</td><td>{{ $seminar->date?->translatedFormat('l, d F Y') ?? '-' }}</td></tr>
    <tr><td class="k">Waktu</td><td class="s">:</td><td>{{ $seminar->time ?? '-' }}</td></tr>
    <tr><td class="k">Tempat</td><td class="s">:</td><td>{{ $seminar->location ?? '-' }}</td></tr>
    <tr><td class="k">Jumlah Hadir</td><td class="s">:</td><td>{{ $attendances->count() }} peserta</td></tr>
  </table>

  <table class="att">
    <thead>
      <tr>
        <th class="no">No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Kelas</th>
        <th>Prodi</th>
        <th class="sig" style="text-align:center;">Tanda Tangan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($attendances as $i => $a)
      <tr>
        <td class="no">{{ $i + 1 }}</td>
        <td>{{ $a->name }}</td>
        <td>{{ $a->nim }}</td>
        <td>{{ $a->kelas ?? '-' }}</td>
        <td>{{ $a->prodi ?? '-' }}</td>
        <td class="sig">
          @if($a->signature_data)<img src="{{ $a->signature_data }}" alt="ttd">@endif
        </td>
      </tr>
      @empty
      <tr><td colspan="6" class="empty">Belum ada tamu yang mengisi berita acara.</td></tr>
      @endforelse
    </tbody>
  </table>

  <table class="foot">
    <tr>
      <td style="width:60%;"></td>
      <td>
        <div class="muted">{{ now()->translatedFormat('d F Y') }}</div>
        Penyaji,
        <div class="sign-space"></div>
        <div>( {{ $seminar->student->user->name ?? '..........................' }} )</div>
      </td>
    </tr>
  </table>

</body>
</html>
