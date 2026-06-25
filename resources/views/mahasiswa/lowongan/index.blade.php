@extends('layouts.mahasiswa')
@section('title', 'Lowongan Magang')
@php
  $title = 'Lowongan Magang';
@endphp
@section('content')

  <div class="page-header"><div class="page-title">Lowongan Magang</div></div>

  <div style="background:var(--blue-tint);border:1px solid #C7DCFF;color:var(--primary);padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:16px;">
    Lowongan di bawah adalah <strong>informasi/pengumuman</strong> dari kampus. Untuk melamar, hubungi
    kontak (PIC) yang tercantum secara langsung di luar aplikasi.
  </div>

  <form method="GET" action="{{ route('mahasiswa.lowongan') }}">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Cari posisi atau perusahaan..." value="{{ request('search') }}">
    </div>
  </form>

  <div style="font-size:12px;color:var(--text-muted);margin-bottom:13px;">
    Lowongan Tersedia — <strong style="color:var(--text);">{{ $lowongans->count() }} posisi tersedia</strong>
  </div>

  @forelse($lowongans as $job)
  @php
    $companyName = $job->company_display_name;
    $initials    = collect(explode(' ', $companyName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
    $colors = [
      ['bg'=>'var(--blue-tint)','text'=>'var(--primary)'],['bg'=>'var(--success-bg)','text'=>'var(--success-text)'],
      ['bg'=>'var(--warn-bg)','text'=>'var(--warn-text)'],['bg'=>'var(--danger-bg)','text'=>'var(--danger)'],
      ['bg'=>'var(--purple-bg)','text'=>'var(--purple-text)'],
    ];
    $color = $colors[$job->id % count($colors)];
    $hasContact = $job->pic_name || $job->pic_email || $job->pic_phone;
    $ajukanUrl = $job->company_id
      ? route('mahasiswa.ajukan-magang', ['company_id' => $job->company_id])
      : route('mahasiswa.ajukan-magang', ['company_name' => $companyName]);
  @endphp
  <div class="job-card" style="cursor:pointer;" onclick="window.location.href='{{ $ajukanUrl }}'" title="Ajukan magang di perusahaan ini">
    <div class="job-top">
      <div class="job-logo" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
      <div class="job-info">
        <div class="job-head">
          <div>
            <div class="job-title">{{ $companyName }}</div>
          </div>
          @if($job->created_at->diffInDays() <= 3)
            <span class="badge baru">Baru</span>
          @endif
        </div>
        <div class="job-tags">
          @php $skills = $job->skills ?? []; @endphp
          @foreach(array_slice($skills, 0, 3) as $skill)
            <span class="tag">{{ $skill }}</span>
          @endforeach
          @if(count($skills) > 3)
            <span style="font-size:11px;color:var(--primary);font-weight:600;cursor:pointer;text-decoration:underline;"
              data-skills='{{ json_encode($skills) }}'
              data-title="{{ addslashes($companyName) }}"
              onclick="event.stopPropagation(); openSkillsModal(this)">+{{ count($skills) - 3 }} keahlian lainnya</span>
          @endif
        </div>
      </div>
    </div>

    @if($job->description)
    <div style="font-size:12.5px;color:var(--text);margin-top:12px;line-height:1.6;">{{ $job->description }}</div>
    @endif

    <div class="job-footer" style="flex-wrap:wrap;gap:10px;">
      <div class="job-loc">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        {{ $job->location ?? 'Lokasi tidak dicantumkan' }} · {{ $job->job_type }}
      </div>
    </div>

    @if($hasContact)
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:6px;">KONTAK UNTUK MELAMAR</div>
      <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:12.5px;color:var(--text);">
        @if($job->pic_name)
          <span><x-icon name="user" :size="13"/> {{ $job->pic_name }}</span>
        @endif
        @if($job->pic_phone)
          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->pic_phone) }}" target="_blank" onclick="event.stopPropagation()" style="color:var(--primary);text-decoration:none;"><x-icon name="phone-mobile" :size="13"/> {{ $job->pic_phone }}</a>
        @endif
        @if($job->pic_email)
          <a href="mailto:{{ $job->pic_email }}" onclick="event.stopPropagation()" style="color:var(--primary);text-decoration:none;"><x-icon name="mail" :size="13"/> {{ $job->pic_email }}</a>
        @endif
      </div>
    </div>
    @else
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted);">
      Kontak belum dicantumkan — tanyakan ke Kaprodi untuk info lebih lanjut.
    </div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Tidak ada lowongan yang tersedia.</p>
  </div>
  @endforelse

{{-- ═══════════════════════════════════════════
     MODAL LIHAT KEAHLIAN
═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-skills" onclick="if(event.target===this)closeSkillsModal()">
  <div class="modal-box" style="max-width:380px;padding:20px;">
    <div class="modal-header" style="margin-bottom:14px;">
      <div class="modal-title">Keahlian yang Dibutuhkan</div>
      <button class="modal-close" onclick="closeSkillsModal()"><x-icon name="x" :size="14"/></button>
    </div>
    <div id="skills-modal-job" style="font-size:12px;color:var(--text-muted);margin-bottom:12px;"></div>
    <div id="skills-modal-list" class="job-tags" style="flex-wrap:wrap;"></div>
  </div>
</div>

@push('scripts')
<script>
function openSkillsModal(el) {
  var skills = JSON.parse(el.getAttribute('data-skills'));
  document.getElementById('skills-modal-job').textContent = el.getAttribute('data-title');

  var list = document.getElementById('skills-modal-list');
  list.innerHTML = '';
  skills.forEach(function (skill) {
    var span = document.createElement('span');
    span.className = 'tag';
    span.textContent = skill;
    list.appendChild(span);
  });

  document.getElementById('modal-skills').classList.add('open');
}

function closeSkillsModal() {
  document.getElementById('modal-skills').classList.remove('open');
}
</script>
@endpush

@endsection
