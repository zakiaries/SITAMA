@extends('layouts.industri')
@section('title', 'Dashboard')
@php $title = 'Dashboard'; @endphp

@push('styles')
<style>
.ind-hero {
  background: #2d3e6e;
  background-image: url('{{ asset("images/pattern.png") }}');
  background-size: 200px;
  border-radius: 14px; padding: 26px 24px; margin-bottom: 20px;
  position: relative; overflow: hidden; display: flex; align-items: center; gap: 18px;
}
.ind-hero::before { content:''; position:absolute; inset:0; background:rgba(45,62,110,0.82); }
.ind-hero > * { position:relative; z-index:1; }
.hero-av { width:60px;height:60px;border-radius:14px;flex-shrink:0;background:rgba(255,255,255,0.2);border:2.5px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff; }
.hero-name { font-size:20px;font-weight:800;color:#fff;margin-bottom:3px; }
.hero-addr { font-size:12px;color:rgba(255,255,255,0.75);margin-bottom:6px; }

.stat-grid { display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px; }
.stat-card-i { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:18px; }
.stat-card-i .sc-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:19px;margin-bottom:10px; }
.stat-card-i .sc-val { font-size:28px;font-weight:800;color:var(--text);line-height:1; }
.stat-card-i .sc-lbl { font-size:12px;color:var(--text-muted);margin-top:4px; }

.intern-row { display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border); }
.intern-row:last-child { border-bottom:none; }
.intern-av { width:38px;height:38px;border-radius:50%;background:var(--primary-light);color:var(--primary-text);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0; }
</style>
@endpush

@section('content')

@php $initials = collect(explode(' ', preg_replace('/^PT\.?\s*/i','', $company->name)))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
<div class="ind-hero">
  <div class="hero-av">{{ $initials }}</div>
  <div style="flex:1;">
    <div class="hero-name">{{ $company->name }}</div>
    <div class="hero-addr">📍 {{ $company->address ?? 'Alamat belum diisi' }}</div>
    @if($company->verification_status === 'verified')
      <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px;">✓ Akun Terverifikasi</span>
    @else
      <span style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px;">⏳ Menunggu Verifikasi</span>
    @endif
  </div>
</div>

<div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:20px;">
  Pengelolaan lowongan & rekrutmen magang ditangani oleh pihak kampus (Polines). Akun ini digunakan
  untuk memantau mahasiswa yang sedang magang di perusahaan Anda.
</div>

{{-- Statistik --}}
<div class="stat-grid">
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#e8eef8;">👨‍💼</div>
    <div class="sc-val">{{ $magangAktif }}</div>
    <div class="sc-lbl">Magang Aktif</div>
  </div>
  <div class="stat-card-i">
    <div class="sc-icon" style="background:#dcfce7;">✓</div>
    <div class="sc-val">{{ $magangSelesai }}</div>
    <div class="sc-lbl">Magang Selesai</div>
  </div>
</div>

{{-- Daftar mahasiswa magang --}}
<div class="card-title" style="margin-bottom:14px;">Mahasiswa Magang</div>
<div class="card">
  @forelse($internships as $it)
  @php $nm = $it->student->user->name ?? '-'; $init = collect(explode(' ', $nm))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
  <div class="intern-row">
    <div class="intern-av">{{ $init }}</div>
    <div style="flex:1;">
      <div style="font-size:13px;font-weight:700;color:var(--text);">{{ $nm }}</div>
      <div style="font-size:12px;color:var(--text-muted);">
        {{ $it->position ?? 'Posisi belum diisi' }}
        @if($it->lecturerIndustry && $it->lecturerIndustry->user) · Pembimbing: {{ $it->lecturerIndustry->user->name }} @endif
      </div>
    </div>
    @if($it->is_finished)
      <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Selesai</span>
    @else
      <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
    @endif
  </div>
  @empty
  <p style="color:var(--text-muted);font-size:13px;padding:8px 0;">Belum ada mahasiswa yang magang di perusahaan Anda.</p>
  @endforelse
</div>

@endsection
