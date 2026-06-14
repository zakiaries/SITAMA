@extends('layouts.mahasiswa')
@section('title', 'Magang Saya')
@php $title = 'Magang Saya'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('error') }}
    </div>
  @endif

  {{-- Undangan Kelompok --}}
  @if($invitations->count())
  <div class="card" style="margin-bottom:20px;border-left:4px solid #7c3aed;">
    <div class="card-title" style="margin-bottom:14px;color:#7c3aed;">
      👥 Undangan Kelompok
      <span style="background:#f3e8ff;color:#7c3aed;padding:2px 8px;border-radius:20px;font-size:11px;margin-left:6px;">
        {{ $invitations->count() }}
      </span>
    </div>

    @foreach($invitations as $inv)
    @php $leaderName = $inv->group->leaderApplication->student->user->name ?? '-'; @endphp
    <div style="padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
          <div style="font-weight:700;font-size:14px;color:var(--text);">{{ $inv->group->jobListing->title }}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $inv->group->jobListing->company->name }}</div>
          <div style="font-size:11px;margin-top:4px;color:var(--text-muted);">
            Diundang oleh: <strong style="color:var(--text);">{{ $leaderName }}</strong>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
          <form method="POST" action="{{ route('mahasiswa.internship-group-members.accept', $inv) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">✓ Terima</button>
          </form>
          <form method="POST" action="{{ route('mahasiswa.internship-group-members.decline', $inv) }}">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;">✕ Tolak</button>
          </form>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @endif

  {{-- Magang Aktif --}}
  @if($internship)
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Magang Aktif</div>
      @if($internship->is_finished)
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Selesai</span>
      @else
        <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
      @endif
    </div>
    <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);font-weight:600;">{{ $internship->company->name ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
    <div class="info-row">
      <div class="info-key">Dosen Pembimbing</div>
      <div class="info-val">{{ $internship->lecturer && $internship->lecturer->user ? $internship->lecturer->user->name : 'Belum ditugaskan' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Pembimbing Industri</div>
      <div class="info-val">{{ $internship->lecturerIndustry && $internship->lecturerIndustry->user ? $internship->lecturerIndustry->user->name : 'Belum ditugaskan' }}</div>
    </div>
  </div>

  {{-- Sertifikat Magang --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Sertifikat Magang</div>
      @if($internship->certificate_path)
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Sudah diunggah</span>
      @else
        <span style="background:#fef9c3;color:#854f0b;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum ada</span>
      @endif
    </div>

    @if($internship->certificate_path)
      <div class="file-badge" style="margin-bottom:14px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <a href="{{ Storage::url($internship->certificate_path) }}" target="_blank">Lihat Sertifikat</a>
      </div>
    @endif

    <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">
      Unggah sertifikat magang dari perusahaan (PDF/JPG/PNG, maks 10 MB).
      {{ $internship->certificate_path ? 'Mengunggah ulang akan menggantikan file lama.' : '' }}
    </div>

    <form method="POST" action="{{ route('mahasiswa.magang-saya.sertifikat') }}" enctype="multipart/form-data"
          style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      @csrf
      <input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required>
      <button type="submit" class="btn btn-primary btn-sm">
        {{ $internship->certificate_path ? 'Ganti Sertifikat' : 'Unggah Sertifikat' }}
      </button>
    </form>
    @error('certificate')
      <div style="color:#dc2626;font-size:12px;margin-top:8px;">{{ $message }}</div>
    @enderror
  </div>

  {{-- Log Book Terbaru --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Log Book Terbaru</div>
      <a href="{{ route('mahasiswa.logbook') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    @forelse($logBooks as $lb)
    <div style="padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div style="font-weight:700;font-size:13px;color:var(--text);">{{ $lb->title }}</div>
        <div style="font-size:11px;color:var(--text-muted);">{{ $lb->date->format('d M Y') }}</div>
      </div>
      @if($lb->lecturer_note)
      <div class="field-group" style="border-left:3px solid #2563eb;padding-left:10px;margin-top:8px;">
        <div class="field-label" style="color:#2563eb;">Catatan Dosen Pembimbing (Kampus)</div>
        <div class="field-value">{{ $lb->lecturer_note }}</div>
      </div>
      @endif
      @if($lb->industry_note)
      <div class="field-group" style="border-left:3px solid #16a34a;padding-left:10px;margin-top:8px;">
        <div class="field-label" style="color:#16a34a;">Catatan Pembimbing Industri</div>
        <div class="field-value">{{ $lb->industry_note }}</div>
      </div>
      @endif
    </div>
    @empty
      <p style="color:var(--text-muted);font-size:13px;padding:8px 0;">Belum ada log book.</p>
    @endforelse
  </div>
  @endif

  {{-- Pendaftaran Saya --}}
  <div class="page-header">
    <div class="page-title">Pendaftaran Saya</div>
    <a href="{{ route('mahasiswa.lowongan') }}" class="btn btn-primary btn-sm">+ Cari Lowongan</a>
  </div>

  @forelse($applications as $app)
  @php
    $statusStyle = match($app->status) {
      'accepted' => ['bg' => '#dcfce7', 'text' => '#16a34a'],
      'rejected' => ['bg' => '#fee2e2', 'text' => '#dc2626'],
      default    => ['bg' => '#fef9c3', 'text' => '#854f0b'],
    };
  @endphp
  <div class="card" style="margin-bottom:12px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
      <div style="flex:1;">
        <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $app->jobListing->title }}</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $app->jobListing->company->name }}</div>
      </div>
      <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
        @if($app->type === 'group')
          <span style="background:#f3e8ff;color:#7c3aed;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">👥 Kelompok</span>
        @elseif($app->type === 'solo')
          <span style="background:#eff6ff;color:#2563eb;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">🧑‍💻 Solo</span>
        @endif
        <span style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }};padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">
          {{ ucfirst($app->status) }}
        </span>
      </div>
    </div>

    {{-- Anggota kelompok (jika group) --}}
    @if($app->type === 'group' && $app->internshipGroup)
    @php $group = $app->internshipGroup; @endphp
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;font-weight:600;">ANGGOTA KELOMPOK</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        {{-- Ketua (diri sendiri) --}}
        <div style="display:flex;align-items:center;gap:5px;background:#eff6ff;border:1px solid #bfdbfe;padding:4px 10px;border-radius:20px;">
          <span style="font-size:12px;font-weight:600;color:#1e40af;">{{ Auth::user()->name }}</span>
          <span style="font-size:10px;color:#2563eb;">(Ketua)</span>
        </div>
        {{-- Anggota lain --}}
        @foreach($group->members as $member)
        @php
          $mColor = match($member->status) {
            'accepted' => ['bg' => '#dcfce7', 'border' => '#86efac', 'text' => '#166534', 'icon' => '✓'],
            'rejected' => ['bg' => '#fee2e2', 'border' => '#fca5a5', 'text' => '#991b1b', 'icon' => '✕'],
            default    => ['bg' => '#fef9c3', 'border' => '#fde047', 'text' => '#713f12', 'icon' => '⏳'],
          };
        @endphp
        <div style="display:flex;align-items:center;gap:5px;background:{{ $mColor['bg'] }};border:1px solid {{ $mColor['border'] }};padding:4px 10px;border-radius:20px;">
          <span style="font-size:12px;font-weight:600;color:{{ $mColor['text'] }};">{{ $member->student->user->name ?? '-' }}</span>
          <span style="font-size:10px;">{{ $mColor['icon'] }}</span>
        </div>
        @endforeach
      </div>
      @if($group->members->where('status', '!=', 'rejected')->count() < 3)
      <a href="{{ route('mahasiswa.internship-groups.invite', $group) }}"
         style="font-size:12px;color:#7c3aed;display:inline-flex;align-items:center;gap:4px;margin-top:10px;">
        + Undang lebih banyak
      </a>
      @endif
    </div>
    @endif

    <div style="font-size:11px;color:var(--text-muted);margin-top:10px;">
      Didaftarkan: {{ $app->created_at->format('d M Y, H:i') }}
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
    <div style="font-size:40px;margin-bottom:12px;">📋</div>
    <p style="font-size:14px;margin-bottom:16px;">Belum ada pendaftaran magang.</p>
    <a href="{{ route('mahasiswa.lowongan') }}" class="btn btn-primary">Cari Lowongan →</a>
  </div>
  @endforelse

@endsection
