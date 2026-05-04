@extends('layouts.mahasiswa')
@section('title', 'Detail Seminar')
@php $title = 'Detail Seminar'; @endphp
@section('content')
        <div style="display:flex;align-items:center;gap:11px;margin-bottom:20px;">
          <button class="btn btn-outline btn-sm" onclick="window.location='{{ route('mahasiswa.seminar') }}'">← Kembali</button>
          <div class="page-title">Detail Seminar</div>
        </div>
        <div class="grid-2">
          <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
              <div><div style="font-size:17px;font-weight:800;color:var(--primary);">Hasil Magang dan Pembelajaran</div><div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Program Teknik Informatika</div></div>
              <span class="badge jadwal">Terjadwal</span>
            </div>
            <table class="detail-table" style="width:100%;border-collapse:collapse;">
              <tr><td>Tanggal</td><td>25 Jan 2025</td></tr>
              <tr><td>Waktu</td><td>13:00 – 15:00 WIB</td></tr>
              <tr><td>Ruang</td><td>Aula Blok B Lantai 3</td></tr>
            </table>
            <div style="border-top:1px solid var(--border);margin:14px 0;padding-top:14px;">
              <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Pembimbing &amp; Pengampu</div>
              <div style="font-size:13px;font-weight:600;color:var(--text);">Dr. Rina Fiati, S.Kom., M.Cs.</div>
              <div style="font-size:13px;color:var(--text-muted);">Aida Fitri, S.Kom., M.Sc.</div>
            </div>
            <button class="btn btn-primary" style="width:100%;justify-content:center;">□ Scan QR untuk Absensi</button>
          </div>

          <div class="card">
            <div style="text-align:center;padding:10px 0 16px;">
              <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:16px;">QR Code Absensi Seminar</div>
              <div style="width:140px;height:140px;border:2.5px solid var(--primary);border-radius:10px;margin:0 auto;display:flex;align-items:center;justify-content:center;background:var(--primary-light);">
                <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
                  <rect x="5" y="5" width="36" height="36" rx="3" fill="#1e3a6e"/>
                  <rect x="11" y="11" width="24" height="24" rx="2" fill="white"/>
                  <rect x="16" y="16" width="14" height="14" fill="#1e3a6e"/>
                  <rect x="59" y="5" width="36" height="36" rx="3" fill="#1e3a6e"/>
                  <rect x="65" y="11" width="24" height="24" rx="2" fill="white"/>
                  <rect x="70" y="16" width="14" height="14" fill="#1e3a6e"/>
                  <rect x="5" y="59" width="36" height="36" rx="3" fill="#1e3a6e"/>
                  <rect x="11" y="65" width="24" height="24" rx="2" fill="white"/>
                  <rect x="16" y="70" width="14" height="14" fill="#1e3a6e"/>
                  <rect x="59" y="59" width="12" height="12" fill="#1e3a6e"/>
                  <rect x="75" y="59" width="12" height="12" fill="#1e3a6e"/>
                  <rect x="59" y="75" width="12" height="12" fill="#1e3a6e"/>
                  <rect x="75" y="75" width="12" height="12" fill="#1e3a6e"/>
                </svg>
              </div>
              <div style="font-size:11px;color:var(--text-muted);margin-top:10px;">Scan QR saat tiba di lokasi seminar</div>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:14px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:11px;">
                <div style="font-size:13px;font-weight:700;color:var(--primary);">Daftar sebagai Audience</div>
                <span class="see-all">Lihat semua</span>
              </div>
              <div class="audience-row">
                <div class="avatar" style="width:32px;height:32px;font-size:11px;background:var(--primary-light);color:var(--primary-text);">AM</div>
                <div style="flex:1;"><div style="font-size:13px;font-weight:600;">Alif Rahman Maulana</div><div style="font-size:11px;color:var(--text-muted);">14 Jan 2025 · 13:00 · Aula B.301</div></div>
                <button class="btn btn-outline btn-sm">Daftar</button>
              </div>
              <div class="audience-row">
                <div class="avatar" style="width:32px;height:32px;font-size:11px;background:var(--success-bg);color:var(--success-text);">AP</div>
                <div style="flex:1;"><div style="font-size:13px;font-weight:600;">Alvina Putri Aulia</div><div style="font-size:11px;color:var(--text-muted);">16 Jan 2025 · 10:00 · Aula C.201</div></div>
                <button class="btn btn-outline btn-sm">Daftar</button>
              </div>
            </div>
          </div>
        </div>
@endsection
