@extends('layouts.mahasiswa')
@section('title', 'Detail Seminar')
@php $title = 'Detail Seminar'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  <div style="display:flex;align-items:center;gap:11px;margin-bottom:20px;">
    <button class="btn btn-outline btn-sm" onclick="window.location='{{ route('mahasiswa.seminar') }}'"><x-icon name="arrow-left" :size="14"/> Kembali</button>
    <div class="page-title">Detail Seminar</div>
  </div>

  <div class="grid-2">
    {{-- Info seminar --}}
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
        <div>
          <div style="font-size:17px;font-weight:800;color:var(--primary);">{{ $seminar->title }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $seminar->program }}</div>
        </div>
        @php
          $stMap = [
            'pending'   => ['Menunggu ACC', 'var(--warn-bg)', 'var(--warn-text)'],
            'scheduled' => ['Disetujui', 'var(--success-bg)', 'var(--success-text)'],
            'rejected'  => ['Ditolak', 'var(--danger-bg)', 'var(--danger)'],
            'completed' => ['Selesai', 'var(--success-bg)', 'var(--success-text)'],
            'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
          ];
          $st = $stMap[$seminar->status] ?? [ucfirst($seminar->status), 'var(--blue-tint)', 'var(--primary)'];
        @endphp
        <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
      </div>
      <table class="detail-table" style="width:100%;border-collapse:collapse;">
        @if($seminar->date)<tr><td>Tanggal</td><td>{{ $seminar->date->format('d M Y') }}</td></tr>@endif
        @if($seminar->time)<tr><td>Waktu</td><td>{{ $seminar->time }}</td></tr>@endif
        @if($seminar->location)<tr><td>Ruang</td><td>{{ $seminar->location }}</td></tr>@endif
        @if($seminar->organizer)<tr><td>Pengampu</td><td>{{ $seminar->organizer }}</td></tr>@endif
      </table>

      @if($seminar->description)
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;">Deskripsi</div>
        <div style="font-size:13px;color:var(--text);">{{ $seminar->description }}</div>
      </div>
      @endif

      {{-- Status khusus milik sendiri --}}
      @if($isOwner && $seminar->status === 'pending')
        <div style="margin-top:16px;background:var(--warn-bg);color:var(--warn-text);padding:12px;border-radius:8px;font-size:12.5px;">
          Pengajuan sedang menunggu persetujuan Kaprodi. QR daftar hadir akan muncul setelah disetujui.
        </div>
      @elseif($isOwner && $seminar->status === 'rejected')
        <div style="margin-top:16px;background:var(--danger-bg);color:var(--danger);padding:12px;border-radius:8px;font-size:12.5px;">
          <strong>Ditolak Kaprodi:</strong> {{ $seminar->rejection_reason }}<br>
          <span style="color:var(--text-muted);">Silakan ubah jadwal di halaman Seminar lalu ajukan ulang.</span>
        </div>
      @endif

      {{-- Pendaftaran audiens untuk seminar umum (bukan milik mahasiswa) --}}
      @unless($isOwner || $seminar->student_id)
      <div style="margin-top:16px;">
        @if(!$isRegistered)
        <form method="POST" action="{{ route('mahasiswa.seminar.register', $seminar->id) }}">
          @csrf
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <x-icon name="check" :size="15"/> Daftar Seminar Ini
          </button>
        </form>
        @else
        <div style="background:var(--success-bg);color:var(--success-text);padding:12px;border-radius:8px;text-align:center;font-weight:600;">
          <x-icon name="check" :size="14"/> Anda sudah terdaftar
        </div>
        @endif
      </div>
      @endunless
    </div>

    {{-- Panel kanan --}}
    <div class="card">
      @if($isOwner && $seminar->status === 'scheduled')
        @php $guests = $seminar->attendances->count(); $min = \App\Models\Seminar::MIN_GUESTS; $met = $guests >= $min; @endphp

        {{-- QR daftar hadir --}}
        <div style="text-align:center;padding-bottom:16px;border-bottom:1px solid var(--border);margin-bottom:16px;">
          <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:10px;">QR Daftar Hadir (Berita Acara)</div>
          <div style="display:inline-block;background:#fff;padding:12px;border:1.5px solid var(--border);border-radius:12px;">
            {!! $qrSvg !!}
          </div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:10px;">Tamu memindai QR ini untuk mengisi identitas &amp; tanda tangan.</div>
          <div style="display:flex;gap:8px;align-items:center;margin-top:10px;background:var(--warm);border-radius:8px;padding:8px 10px;">
            <code style="flex:1;font-size:11px;word-break:break-all;text-align:left;">{{ $qrUrl }}</code>
            <button type="button" class="btn btn-outline btn-sm" onclick="navigator.clipboard.writeText('{{ $qrUrl }}')">Salin</button>
          </div>
        </div>

        {{-- Progress tamu --}}
        <div style="margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:700;color:var(--primary);">Tamu Hadir</span>
            <span style="font-size:13px;font-weight:800;color:{{ $met ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ $min }} @if($met)<x-icon name="check" :size="12"/>@endif</span>
          </div>
          <div style="height:8px;background:var(--border);border-radius:20px;overflow:hidden;">
            <div style="height:100%;width:{{ min(100, $guests / $min * 100) }}%;background:{{ $met ? 'var(--success-text)' : 'var(--warning)' }};"></div>
          </div>
          <div style="font-size:11px;color:{{ $met ? 'var(--success-text)' : 'var(--text-muted)' }};margin-top:6px;">
            {{ $met ? 'Jumlah tamu minimal sudah terpenuhi.' : 'Butuh ' . ($min - $guests) . ' tamu lagi (minimal ' . $min . ').' }}
          </div>
        </div>

        {{-- Cetak berita acara --}}
        <a href="{{ route('mahasiswa.seminar.berita-acara', $seminar->id) }}" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:16px;">
          <x-icon name="download" :size="15"/> Cetak Berita Acara (PDF)
        </a>

        {{-- Daftar tamu --}}
        <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:12px;">
          Daftar Hadir Tamu ({{ $seminar->attendances->count() }})
        </div>
        @forelse($seminar->attendances as $a)
        <div class="audience-row">
          <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;">{{ $a->name }}</div>
            <div style="font-size:11px;color:var(--text-muted);">
              {{ $a->nim }}@if($a->kelas) · {{ $a->kelas }}@endif @if($a->prodi) · {{ $a->prodi }}@endif
            </div>
          </div>
          @if($a->signature_path)
            <img src="{{ Storage::url($a->signature_path) }}" alt="ttd" style="height:34px;width:auto;border:1px solid var(--border);border-radius:4px;background:#fff;">
          @endif
        </div>
        @empty
        <p style="color:var(--text-muted);font-size:13px;">Belum ada tamu yang mengisi berita acara.</p>
        @endforelse

      @elseif(!$isOwner && $seminar->student_id)
        {{-- Seminar hasil magang mahasiswa lain: info penyaji saja --}}
        <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:12px;">Penyaji</div>
        <div style="font-size:14px;font-weight:600;">{{ $seminar->student->user->name ?? '-' }}</div>

      @else
        {{-- Seminar umum: daftar peserta terdaftar --}}
        <div style="font-size:13px;font-weight:700;color:var(--primary);margin-bottom:14px;">
          Peserta Terdaftar ({{ $seminar->registrations->count() }})
        </div>
        @forelse($seminar->registrations as $reg)
        @php $initials = collect(explode(' ', $reg->student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join(''); @endphp
        <div class="audience-row">
          <div class="avatar" style="width:32px;height:32px;font-size:11px;background:var(--primary-light);color:var(--primary-text);">{{ $initials }}</div>
          <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;">{{ $reg->student->user->name ?? '-' }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ ucfirst($reg->status) }}</div>
          </div>
        </div>
        @empty
        <p style="color:var(--text-muted);font-size:13px;">Belum ada peserta terdaftar.</p>
        @endforelse
      @endif
    </div>
  </div>
@endsection
