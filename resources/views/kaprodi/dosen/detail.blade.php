@extends('layouts.kaprodi')
@section('title', 'Detail Dosen')
@php $title = 'Detail Dosen'; @endphp

@push('styles')
<style>
.mhs-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
}
.mhs-av   { width:44px;height:44px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px; }
.mhs-info { flex:1;min-width:0; }
.mhs-name { font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px; }
.mhs-meta { font-size:12px;color:var(--text-muted); }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-aktif   { background:var(--blue-tint);color:var(--primary); }
.st-selesai { background:var(--success-bg);color:var(--success-text); }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
  <div style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('kaprodi.dosen.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
    <div>
      <div class="page-title" style="margin-bottom:2px;">{{ $lecturer->user->name ?? '-' }}</div>
      <div style="font-size:12px;color:var(--text-muted);">
        {{ $lecturer->user->username ?? '-' }}
        <span style="margin-left:6px;background:{{ $isIndustry ? 'var(--warn-bg)' : 'var(--blue-tint)' }};color:{{ $isIndustry ? 'var(--warn-text)' : 'var(--primary)' }};font-size:10px;font-weight:600;padding:1px 8px;border-radius:12px;">
          {{ $isIndustry ? 'Pembimbing Industri' : 'Dosen Kampus' }}
        </span>
        &middot; {{ $students->count() }} mahasiswa{{ $isIndustry ? ' yang dibimbing di industri' : ' bimbingan' }}
      </div>
    </div>
  </div>
  <button type="button" class="btn btn-outline btn-sm"
    onclick="document.getElementById('modal-reset-pw').style.display='flex'">
    <x-icon name="key" :size="14"/> Reset Password
  </button>
</div>

@forelse($students as $student)
@php
  $internship = $student->internships->first();
  $colors = [
    ['bg'=>'var(--blue-tint)','text'=>'var(--primary)'],['bg'=>'var(--success-bg)','text'=>'var(--success-text)'],
    ['bg'=>'var(--warn-bg)','text'=>'var(--warn-text)'],['bg'=>'var(--purple-bg)','text'=>'var(--purple-text)'],
  ];
  $color    = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="mhs-card">
  <div class="mhs-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
  <div class="mhs-info">
    <div class="mhs-name">{{ $student->user->name }}</div>
    <div class="mhs-meta">{{ $student->user->username }} · {{ $student->the_class }}@if($internship?->company) · {{ $internship->company->name }}@endif</div>
  </div>
  @if($internship?->is_finished)
    <span class="st-badge st-selesai">Selesai</span>
  @else
    <span class="st-badge st-aktif">Aktif</span>
  @endif
</div>
@empty
<div style="text-align:center;padding:40px;color:var(--text-muted);"><p>Dosen ini belum membimbing mahasiswa.</p></div>
@endforelse

{{-- Modal Reset Password --}}
<div id="modal-reset-pw" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:400px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:4px;">Reset Password</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;">
      Reset password akun <strong>{{ $lecturer->user->name ?? '-' }}</strong>
      <span style="font-size:11.5px;color:var(--text-muted);">({{ $lecturer->user->username ?? '-' }})</span>
    </p>
    <form method="POST" action="{{ route('kaprodi.dosen.reset-password', $lecturer) }}">
      @csrf
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Password Baru <span style="color:var(--danger);">*</span></label>
          <input type="password" name="new_password" required minlength="6"
            style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;"
            placeholder="Minimal 6 karakter">
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Konfirmasi Password <span style="color:var(--danger);">*</span></label>
          <input type="password" name="new_password_confirmation" required minlength="6"
            style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:16px;">
        <button type="submit" class="btn btn-primary btn-sm">Simpan Password</button>
        <button type="button" class="btn btn-outline btn-sm"
          onclick="document.getElementById('modal-reset-pw').style.display='none'">Batal</button>
      </div>
    </form>
  </div>
</div>

@endsection
