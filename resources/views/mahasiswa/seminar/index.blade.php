@extends('layouts.mahasiswa')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
@section('content')
        <div class="page-header"><div class="page-title">Seminar Magang</div></div>
        <div class="header-banner">
          <h2>🎓 Seminar Wajib Magang</h2>
          <p>Daftarkan diri Anda untuk mengikuti seminar magang. Absensi dapat dilakukan dengan scan QR code di lokasi seminar.</p>
        </div>
        <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:12px;">Daftar Seminar (3)</div>

        <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', ['id' => 1]) }}'">
          <div class="sem-row"><div><div class="sem-title">Hasil Magang dan Pembelajaran</div><div class="sem-prog">Program Teknik Informatika</div></div><span class="badge jadwal">Terjadwal</span></div>
          <div class="sem-meta">
            <div class="sem-mi"><label>Tanggal</label><span>25 Jan 2025</span></div>
            <div class="sem-mi"><label>Waktu</label><span>13:00 – 15:00 WIB</span></div>
            <div class="sem-mi"><label>Ruang</label><span>Aula Blok B Lt. 3</span></div>
          </div>
          <div class="sem-tap">Tap untuk detail »</div>
        </div>

        <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', ['id' => 2]) }}'">
          <div class="sem-meta">
            <div class="sem-mi"><label>Tanggal</label><span>1 Feb 2025</span></div>
            <div class="sem-mi"><label>Waktu</label><span>10:00 – 12:00 WIB</span></div>
            <div class="sem-mi"><label>Ruang</label><span>Aula Blok C Lt. 2</span></div>
          </div>
          <div class="sem-tap">Tap untuk detail »</div>
        </div>

        <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', ['id' => 3]) }}'">
          <div class="sem-meta">
            <div class="sem-mi"><label>Tanggal</label><span>18 Jan 2025</span></div>
            <div class="sem-mi"><label>Waktu</label><span>14:00 – 16:00 WIB</span></div>
            <div class="sem-mi"><label>Ruang</label><span>Aula Blok A Lt. 1</span></div>
          </div>
          <div class="sem-tap">Tap untuk detail »</div>
        </div>
@endsection

