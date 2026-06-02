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
