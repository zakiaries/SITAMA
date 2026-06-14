@extends('layouts.mahasiswa')
@section('title', 'Detail Seminar')
@php $title = 'Detail Seminar'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div style="display:flex;align-items:center;gap:11px;margin-bottom:20px;">
    <button class="btn btn-outline btn-sm" onclick="window.location='{{ route('mahasiswa.seminar') }}'">← Kembali</button>
    <div class="page-title">Detail Seminar</div>
  </div>

  <div class="grid-2">
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
        <div>
          <div style="font-size:17px;font-weight:800;color:var(--primary);">{{ $seminar->title }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $seminar->program }}</div>
        </div>
        <span class="badge jadwal">{{ ucfirst($seminar->status) }}</span>
      </div>
      <table class="detail-table" style="width:100%;border-collapse:collapse;">
        @if($seminar->date)
        <tr><td>Tanggal</td><td>{{ $seminar->date->format('d M Y') }}</td></tr>
        @endif
        @if($seminar->time)
        <tr><td>Waktu</td><td>{{ $seminar->time }}</td></tr>
        @endif
        @if($seminar->location)
        <tr><td>Ruang</td><td>{{ $seminar->location }}</td></tr>
        @endif
        @if($seminar->organizer)
        <tr><td>Pengampu</td><td>{{ $seminar->organizer }}</td></tr>
        @endif
      </table>

      @if($seminar->description)
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;">Deskripsi</div>
        <div style="font-size:13px;color:var(--text);">{{ $seminar->description }}</div>
      </div>
      @endif

      <div style="margin-top:16px;">
        @if(!$isRegistered)
        <form method="POST" action="{{ route('mahasiswa.seminar.register', $seminar->id) }}">
          @csrf
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            ✓ Daftar Seminar Ini
          </button>
        </form>
        @else
        <div style="background:#dcfce7;color:#16a34a;padding:12px;border-radius:8px;text-align:center;font-weight:600;">
          ✓ Anda sudah terdaftar
        </div>
        @endif
      </div>
    </div>

    <div class="card">
      @php
        $aud = $seminar->audienceCount();
        $min = \App\Models\Seminar::MIN_AUDIENCE;
        $met = $aud >= $min;
      @endphp

      {{-- Progress audiens (hanya relevan untuk seminar hasil magang mahasiswa) --}}
      @if($seminar->student_id)
      <div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
          <span style="font-size:13px;font-weight:700;color:var(--primary);">Kuota Audiens</span>
          <span style="font-size:13px;font-weight:800;color:{{ $met ? '#16a34a' : '#854f0b' }};">{{ $aud }}/{{ $min }} {{ $met ? '✓' : '' }}</span>
        </div>
        <div style="height:8px;background:#e5e7eb;border-radius:20px;overflow:hidden;">
          <div style="height:100%;width:{{ min(100, $aud / $min * 100) }}%;background:{{ $met ? '#16a34a' : '#f59e0b' }};"></div>
        </div>
        <div style="font-size:11px;color:{{ $met ? '#16a34a' : 'var(--text-muted)' }};margin-top:6px;">
          {{ $met ? 'Kuota audiens minimal sudah terpenuhi.' : 'Butuh ' . ($min - $aud) . ' audiens lagi (minimal ' . $min . ').' }}
        </div>
      </div>
      @endif

      <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:14px;">
        Peserta Terdaftar ({{ $seminar->registrations->count() }})
      </div>
      @forelse($seminar->registrations as $reg)
      @php
        $initials = collect(explode(' ', $reg->student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
        $isPresenter = $seminar->student_id && $reg->student_id === $seminar->student_id;
      @endphp
      <div class="audience-row">
        <div class="avatar" style="width:32px;height:32px;font-size:11px;background:var(--primary-light);color:var(--primary-text);">
          {{ $initials }}
        </div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;">
            {{ $reg->student->user->name ?? '-' }}
            @if($isPresenter)
              <span style="font-size:10px;background:#eff6ff;color:#2563eb;padding:1px 7px;border-radius:20px;margin-left:4px;">Penyaji</span>
            @endif
          </div>
          <div style="font-size:11px;color:var(--text-muted);">{{ $isPresenter ? 'Mahasiswa penyaji' : 'Audiens' }} · {{ ucfirst($reg->status) }}</div>
        </div>
      </div>
      @empty
      <p style="color:var(--text-muted);font-size:13px;">Belum ada peserta terdaftar.</p>
      @endforelse
    </div>
  </div>
@endsection
