@extends('layouts.mahasiswa')
@section('title', 'Magang Saya')
@php $title = 'Magang Saya'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('error') }}
    </div>
  @endif

  {{-- Undangan Kelompok --}}
  @if($invitations->count())
  <div class="card" style="margin-bottom:20px;border-left:4px solid var(--purple-text);">
    <div class="card-title" style="margin-bottom:14px;color:var(--purple-text);">
      <x-icon name="users" :size="16"/> Undangan Kelompok
      <span style="background:var(--purple-bg);color:var(--purple-text);padding:2px 8px;border-radius:20px;font-size:11px;margin-left:6px;">
        {{ $invitations->count() }}
      </span>
    </div>

    @foreach($invitations as $inv)
    @php $leaderName = $inv->group->leaderApplication->student->user->name ?? '-'; @endphp
    <div style="padding:12px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
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
            <button type="submit" class="btn btn-primary btn-sm"><x-icon name="check" :size="14"/> Terima</button>
          </form>
          <form method="POST" action="{{ route('mahasiswa.internship-group-members.decline', $inv) }}">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm"><x-icon name="x" :size="14"/> Tolak</button>
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
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Selesai</span>
      @else
        <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
      @endif
    </div>
    <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val accent">{{ $internship->company->name ?? '-' }}</div></div>
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
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Sudah diunggah</span>
      @else
        <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum ada</span>
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
      <div style="color:var(--danger);font-size:12px;margin-top:8px;">{{ $message }}</div>
    @enderror
  </div>

  {{-- Ajukan Selesai Magang --}}
  @if(!$internship->is_finished)
  <div class="card" style="margin-bottom:20px;border-left:4px solid {{ $canRequestFinish ? 'var(--success)' : 'var(--text-muted)' }};">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Selesai Magang</div>
      @if($internship->finish_requested)
        <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Menunggu ACC Kaprodi</span>
      @endif
    </div>

    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
      @foreach($finishChecklist as $item)
      <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;">
        @if($item['met'])
          <span style="color:var(--success-text);margin-top:2px;display:inline-flex;"><x-icon name="check" :size="15"/></span>
          <span style="color:var(--text);">{{ $item['label'] }}</span>
        @else
          <span style="color:var(--danger);margin-top:2px;display:inline-flex;"><x-icon name="x" :size="15"/></span>
          <span style="color:var(--text-muted);">{{ $item['label'] }}
            <span style="font-size:11.5px;display:block;color:var(--danger);">{{ $item['hint'] }}</span>
          </span>
        @endif
      </div>
      @endforeach
    </div>

    @if(!$internship->finish_requested)
      @if($canRequestFinish)
        <form method="POST" action="{{ route('mahasiswa.magang-saya.ajukan-selesai') }}">
          @csrf
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            Ajukan Selesai Magang
          </button>
        </form>
      @else
        <button disabled class="btn" style="width:100%;justify-content:center;background:var(--warm-2);color:var(--text-muted);cursor:not-allowed;">
          Ajukan Selesai Magang
        </button>
        <p style="font-size:12px;color:var(--text-muted);margin-top:8px;text-align:center;">Lengkapi semua syarat di atas terlebih dahulu.</p>
      @endif
    @else
      <div style="background:var(--warn-bg);border:1px solid #F3D9A0;border-radius:8px;padding:10px 14px;font-size:12.5px;color:var(--warn-text);">
        Pengajuan selesai magang sudah dikirim ke Kaprodi. Kaprodi akan memeriksa dan memberikan ACC.
      </div>
    @endif
  </div>
  @endif

  {{-- Log Book Terbaru --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Log Book Terbaru</div>
      <a href="{{ route('mahasiswa.logbook') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    @forelse($logBooks as $lb)
    <div style="padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div style="font-weight:700;font-size:13px;color:var(--text);">{{ $lb->title }}</div>
        <div style="font-size:11px;color:var(--text-muted);">{{ $lb->date->format('d M Y') }}</div>
      </div>
      @if($lb->lecturer_note)
      <div class="field-group note-blue" style="margin-top:8px;">
        <div class="field-label">Catatan Dosen Pembimbing (Kampus)</div>
        <div class="field-value">{{ $lb->lecturer_note }}</div>
      </div>
      @endif
      @if($lb->industry_note)
      <div class="field-group note-green" style="margin-top:8px;">
        <div class="field-label">Catatan Pembimbing Industri</div>
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
    <a href="{{ route('mahasiswa.ajukan-magang') }}" class="btn btn-primary btn-sm">+ Ajukan Magang</a>
  </div>

  @forelse($applications as $app)
  @php
    $statusStyle = match($app->status) {
      'accepted' => ['bg' => 'var(--success-bg)', 'text' => 'var(--success-text)'],
      'rejected' => ['bg' => 'var(--danger-bg)', 'text' => 'var(--danger)'],
      default    => ['bg' => 'var(--warn-bg)', 'text' => 'var(--warn-text)'],
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
          <span style="background:var(--purple-bg);color:var(--purple-text);padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><x-icon name="users" :size="12"/> Kelompok</span>
        @elseif($app->type === 'solo')
          <span style="background:var(--blue-tint);color:var(--primary);padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:5px;"><x-icon name="user" :size="12"/> Solo</span>
        @endif
        <span style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }};padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">
          {{ ucfirst($app->status) }}
        </span>
      </div>
    </div>

    {{-- Anggota kelompok (jika group) --}}
    @if($app->type === 'group' && $app->internshipGroup)
    @php $group = $app->internshipGroup; @endphp
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border-subtle);">
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;font-weight:600;">ANGGOTA KELOMPOK</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        {{-- Ketua (diri sendiri) --}}
        <div style="display:flex;align-items:center;gap:5px;background:var(--blue-tint);border:1px solid #C7DCFF;padding:4px 10px;border-radius:20px;">
          <span style="font-size:12px;font-weight:600;color:var(--primary);">{{ Auth::user()->name }}</span>
          <span style="font-size:10px;color:var(--primary);">(Ketua)</span>
        </div>
        {{-- Anggota lain --}}
        @foreach($group->members as $member)
        @php
          $mColor = match($member->status) {
            'accepted' => ['bg' => 'var(--success-bg)', 'border' => '#A7E8CF', 'text' => 'var(--success-text)', 'icon' => 'check'],
            'rejected' => ['bg' => 'var(--danger-bg)', 'border' => '#F0C4BE', 'text' => 'var(--danger)', 'icon' => 'x'],
            default    => ['bg' => 'var(--warn-bg)', 'border' => '#F3D9A0', 'text' => 'var(--warn-text)', 'icon' => 'clock'],
          };
        @endphp
        <div style="display:flex;align-items:center;gap:5px;background:{{ $mColor['bg'] }};border:1px solid {{ $mColor['border'] }};padding:4px 10px;border-radius:20px;">
          <span style="font-size:12px;font-weight:600;color:{{ $mColor['text'] }};">{{ $member->student->user->name ?? '-' }}</span>
          <span style="display:inline-flex;"><x-icon :name="$mColor['icon']" :size="11"/></span>
        </div>
        @endforeach
      </div>
      @if($group->members->where('status', '!=', 'rejected')->count() < 3)
      <a href="{{ route('mahasiswa.internship-groups.invite', $group) }}"
         style="font-size:12px;color:var(--purple-text);display:inline-flex;align-items:center;gap:4px;margin-top:10px;">
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
    <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="clipboard" :size="40"/></div>
    <p style="font-size:14px;margin-bottom:16px;">Belum ada pendaftaran magang.</p>
    <a href="{{ route('mahasiswa.ajukan-magang') }}" class="btn btn-primary">Ajukan Magang <x-icon name="arrow-right" :size="14"/></a>
  </div>
  @endforelse

@endsection
