@extends('layouts.kaprodi')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.kaprodi-hero {
  background: var(--warm);
  border-radius: 14px; padding: 24px 26px; margin-bottom: 20px;
  display: flex; align-items: center; gap: 18px;
}
.hero-av {
  width:64px;height:64px;border-radius:50%;flex-shrink:0;
  background:var(--primary);color:#fff;
  display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;
}
.hero-role { font-size:11px;font-weight:700;letter-spacing:.06em;color:var(--primary);text-transform:uppercase;margin-bottom:2px; }
.hero-name { font-size:26px;font-weight:800;color:var(--text);letter-spacing:-0.02em;line-height:1.1;margin-bottom:4px;display:flex;align-items:center;gap:10px;flex-wrap:wrap; }
.hero-prog { font-size:13px;color:var(--text-secondary); }
.hero-badge { display:inline-flex;align-items:center;gap:5px;background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:700;padding:4px 10px;border-radius:9999px; }

.stat-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px; }
/* Dua berjajar, bukan satu: kartu angka masih terbaca di lebar segitu. */
@media (max-width: 760px) { .stat-grid { grid-template-columns:repeat(2,1fr); } }
.stat-card-k {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px;
}
.stat-card-k .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px; }
.stat-card-k .sc-val  { font-size:28px;font-weight:800;color:var(--text);line-height:1; }
.stat-card-k .sc-lbl  { font-size:12px;color:var(--text-muted);margin-top:4px; }
.stat-card-k .sc-sub  { font-size:11px;color:var(--text-muted);margin-top:6px; }

.chart-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px; }
/* Grafik dipaksa satu kolom: dua bagan berdampingan di layar ponsel
   membuat sumbu dan labelnya saling menindih sampai tak terbaca. */
@media (max-width: 760px) { .chart-grid { grid-template-columns:1fr; } }
.chart-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:20px;
}
.chart-card.full { grid-column:1 / -1; }
.chart-title { font-size:13px;font-weight:700;color:var(--text);margin-bottom:14px; }

.filter-bar {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px 18px;
  display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;
}
{{-- Aturan `.filter-bar select` dibuang: pemilih periode kini punya gayanya
     sendiri di simama.css dan dipakai bersama halaman Data Mahasiswa. Selama
     aturan lokal ini ada, dropdown di dua halaman itu tampak berbeda padahal
     komponennya sama. --}}
.export-btns { display:flex;gap:8px;margin-left:auto; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="kaprodi-hero">
  <x-avatar :user="$user" class="hero-av" />
  <div style="flex:1;">
    <div class="hero-role">KETUA PROGRAM STUDI</div>
    <div class="hero-name">{{ $user->name }}<span class="hero-badge"><x-icon name="star" :size="12"/> Superadmin</span></div>
    <div class="hero-prog">Teknik Informatika · Politeknik Negeri Semarang</div>
  </div>
</div>

{{-- Banner Notifikasi: Mahasiswa Menunggu Persetujuan --}}
@if($pendingMahasiswa > 0)
<div style="background:var(--warn-bg);border:1.5px solid #F3D9A0;border-radius:14px;padding:18px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
  <div style="width:48px;height:48px;border-radius:12px;background:#FBEBC8;color:var(--warn-text);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <x-icon name="bell" :size="22"/>
  </div>
  <div style="flex:1;min-width:200px;">
    <div style="font-size:15px;font-weight:800;color:var(--warn-text);margin-bottom:2px;">
      {{ $pendingMahasiswa }} pendaftar baru menunggu persetujuan
    </div>
    <div style="font-size:12px;color:var(--warn-text);">
      @foreach($pendingList as $p){{ $p->user->name }}@if(!$loop->last), @endif @endforeach
      @if($pendingMahasiswa > 3) dan {{ $pendingMahasiswa - 3 }} lainnya @endif
      perlu Anda tinjau sebelum bisa masuk ke sistem.
    </div>
  </div>
  <a href="{{ route('kaprodi.mahasiswa.index', ['status' => 'pending']) }}"
     style="background:var(--warn-text);color:#fff;font-size:13px;font-weight:700;padding:10px 20px;border-radius:10px;text-decoration:none;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:6px;">
    Tinjau Sekarang <x-icon name="arrow-right" :size="15"/>
  </a>
</div>
@endif

{{-- Filter & Export --}}
<div class="filter-bar">
  <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;">Periode Magang</div>
  <form method="GET" action="{{ route('kaprodi.dashboard') }}" style="display:flex;align-items:center;gap:8px;flex:1;flex-wrap:wrap;">
    <x-periode-select :periode="$periode" :list="$periodeList" :tanpa="$tanpaPeriode"/>
  </form>
  <div class="export-btns">
    <a href="{{ route('kaprodi.dashboard.export-excel', ['periode' => $periode]) }}"
       class="btn btn-sm" style="background:#16a34a;color:#fff;border:none;display:flex;align-items:center;gap:6px;">
      <x-icon name="doc" :size="14"/> Excel
    </a>
    <button onclick="window.print()"
       class="btn btn-sm" style="background:var(--primary);color:#fff;border:none;display:flex;align-items:center;gap:6px;">
      <x-icon name="doc" :size="14"/> PDF
    </button>
  </div>
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--blue-tint);color:var(--primary);"><x-icon name="cap" :size="20"/></div>
    <div class="sc-val">{{ $totalMahasiswa }}</div>
    <div class="sc-lbl">Total Mahasiswa</div>
    <div class="sc-sub">{{ $aktif }} aktif · {{ $selesai }} selesai</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--warn-bg);color:var(--warn-text);"><x-icon name="alert" :size="20"/></div>
    <div class="sc-val">{{ $belumMagang }}</div>
    <div class="sc-lbl">Belum Magang</div>
    <div class="sc-sub">belum ada data magang</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--success-bg);color:var(--success-text);"><x-icon name="calendar" :size="20"/></div>
    <div class="sc-val">{{ $totalSeminar }}</div>
    <div class="sc-lbl">Seminar</div>
    <div class="sc-sub">total terjadwal</div>
  </div>
  <div class="stat-card-k">
    <div class="sc-icon" style="background:var(--purple-bg);color:var(--purple-text);"><x-icon name="cap" :size="20"/></div>
    <div class="sc-val">{{ $totalDosen }}</div>
    <div class="sc-lbl">Dosen Kampus</div>
    <div class="sc-sub">pembimbing aktif</div>
  </div>
</div>

{{-- Charts Row 1: Status + Top Perusahaan --}}
<div class="chart-grid">
  <div class="chart-card">
    <div class="chart-title">Status Mahasiswa</div>
    <div style="max-width:260px;margin:0 auto;">
      <canvas id="chartStatus" style="min-height:220px;"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-title">Top Perusahaan Magang</div>
    <canvas id="chartCompanies" style="max-height:240px;"></canvas>
  </div>
</div>

{{-- Charts Row 2: Per Prodi (full width) --}}
<div class="chart-grid">
  <div class="chart-card full">
    <div class="chart-title">Distribusi Mahasiswa per Program Studi</div>
    <canvas id="chartProdi" style="max-height:220px;"></canvas>
  </div>
</div>

{{-- Charts Row 3: Logbook + Nilai --}}
<div class="chart-grid">
  <div class="chart-card">
    <div class="chart-title">Progress Logbook (min. {{ \App\Models\Internship::MIN_LOGBOOK }} entri)</div>
    <div style="max-width:240px;margin:0 auto;">
      <canvas id="chartLogbook" style="min-height:200px;"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-title">Status Penilaian Mahasiswa</div>
    <canvas id="chartNilai" style="max-height:240px;"></canvas>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
const palette = {
  blue:   '#2D3E6E',
  sky:    '#4E6FB5',
  green:  '#22c55e',
  yellow: '#eab308',
  red:    '#ef4444',
  purple: '#8b5cf6',
  orange: '#f97316',
  teal:   '#14b8a6',
};

// Chart 1: Donut — Status mahasiswa
new Chart(document.getElementById('chartStatus'), {
  type: 'doughnut',
  data: {
    labels: @json($chartStatus['labels']),
    datasets: [{
      data: @json($chartStatus['data']),
      backgroundColor: [palette.yellow, palette.blue, palette.green],
      borderWidth: 2,
      borderColor: '#fff',
    }],
  },
  options: {
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16 } },
    },
    cutout: '62%',
  },
});

// Chart 2: Bar — Top perusahaan
new Chart(document.getElementById('chartCompanies'), {
  type: 'bar',
  data: {
    labels: @json($chartCompanies['labels']),
    datasets: [{
      label: 'Mahasiswa',
      data: @json($chartCompanies['data']),
      backgroundColor: palette.sky,
      borderRadius: 5,
    }],
  },
  options: {
    indexAxis: 'y',
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, ticks: { precision: 0 } },
      y: { ticks: { font: { size: 11 } } },
    },
  },
});

// Chart 3: Bar — Distribusi prodi
new Chart(document.getElementById('chartProdi'), {
  type: 'bar',
  data: {
    labels: @json($chartProdi['labels']),
    datasets: [{
      label: 'Mahasiswa',
      data: @json($chartProdi['data']),
      backgroundColor: [palette.blue, palette.sky, palette.teal, palette.purple, palette.orange, palette.green],
      borderRadius: 5,
    }],
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { precision: 0 } },
    },
  },
});

// Chart 4: Donut — Progress logbook
new Chart(document.getElementById('chartLogbook'), {
  type: 'doughnut',
  data: {
    labels: @json($chartLogbook['labels']),
    datasets: [{
      data: @json($chartLogbook['data']),
      backgroundColor: [palette.green, palette.red],
      borderWidth: 2,
      borderColor: '#fff',
    }],
  },
  options: {
    plugins: {
      legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 14 } },
    },
    cutout: '60%',
  },
});

// Chart 5: Bar — Status nilai
new Chart(document.getElementById('chartNilai'), {
  type: 'bar',
  data: {
    labels: @json($chartNilai['labels']),
    datasets: [{
      label: 'Mahasiswa',
      data: @json($chartNilai['data']),
      backgroundColor: [palette.blue, palette.sky, palette.green, palette.yellow],
      borderRadius: 5,
    }],
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { precision: 0 } },
    },
  },
});
</script>

<style>
@media print {
  .filter-bar .export-btns,
  nav, aside, header { display: none !important; }
  .chart-card { break-inside: avoid; }
  body { background: #fff !important; }
}
</style>
@endpush
