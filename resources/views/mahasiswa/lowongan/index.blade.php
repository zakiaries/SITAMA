@extends('layouts.mahasiswa')
@section('title', 'Lowongan Magang')
@php
  $title    = 'Lowongan Magang';
  $myName   = $user->name ?? '';
  $myNim    = $user->username ?? '';
  $myInit   = collect(explode(' ', $myName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
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

  <div class="page-header"><div class="page-title">Lowongan Magang</div></div>

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
    $initials     = collect(explode(' ', $job->company->name))->take(2)->map(fn($w) => strtoupper($w[0]))->join('');
    $alreadyApplied = in_array($job->id, $appliedIds);
    $colors = [
      ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
      ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#fcebeb','text'=>'#791f1f'],
      ['bg'=>'#eeedfe','text'=>'#3c3489'],
    ];
    $color = $colors[$job->id % count($colors)];
  @endphp
  <div class="job-card">
    <div class="job-top">
      <div class="job-logo" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
      <div class="job-info">
        <div class="job-head">
          <div>
            <div class="job-title">{{ $job->title }}</div>
            <div class="job-company">
              <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
              {{ $job->company->name }}
            </div>
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
              data-title="{{ addslashes($job->title) }}"
              onclick="openSkillsModal(this)">+{{ count($skills) - 3 }} keahlian lainnya</span>
          @endif
        </div>
      </div>
    </div>
    <div class="job-footer">
      <div class="job-loc">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        {{ $job->location ?? 'Lokasi tidak dicantumkan' }}
      </div>
      @if($alreadyApplied)
        <button class="btn btn-sm" style="background:#dcfce7;color:#16a34a;cursor:default;border:none;" disabled>✓ Terdaftar</button>
      @else
        <button type="button" class="btn btn-primary btn-sm"
          onclick="openApplyModal({{ $job->id }}, '{{ addslashes($job->title) }}', '{{ addslashes($job->company->name) }}')">
          + Daftar
        </button>
      @endif
    </div>
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
      <button class="modal-close" onclick="closeSkillsModal()">✕</button>
    </div>
    <div id="skills-modal-job" style="font-size:12px;color:var(--text-muted);margin-bottom:12px;"></div>
    <div id="skills-modal-list" class="job-tags" style="flex-wrap:wrap;"></div>
  </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL MULTI-STEP PENDAFTARAN
═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-apply" onclick="if(event.target===this)closeApplyModal()">
  <div class="modal-box" style="max-width:400px;padding:0;">

    {{-- ── STEP 1: PILIH MODE ── --}}
    <div id="step-mode">
      <div style="padding:20px 20px 0;">
        <div class="modal-header" style="margin-bottom:14px;">
          <div class="modal-title">Pilih Mode Magang</div>
          <button class="modal-close" onclick="closeApplyModal()">✕</button>
        </div>

        {{-- Info lowongan --}}
        <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:18px;">
          <div id="modal-job-logo" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;"></div>
          <div>
            <div id="modal-job-title" style="font-weight:700;font-size:14px;color:var(--text);"></div>
            <div id="modal-company-name" style="font-size:12px;color:var(--text-muted);margin-top:2px;"></div>
          </div>
        </div>
      </div>

      {{-- Mode cards --}}
      <div style="padding:0 20px 20px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <button onclick="goToStep('solo')"
          style="padding:22px 12px;border:1.5px solid #e5e7eb;border-radius:14px;background:#fff;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;transition:all .15s;"
          onmouseover="this.style.borderColor='var(--primary)';this.style.background='#eff6ff'"
          onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff'">
          <span style="font-size:32px;">🧑‍💻</span>
          <span style="font-weight:700;color:var(--primary);font-size:14px;">Solo</span>
          <span style="font-size:11px;color:var(--text-muted);">Daftar sendiri</span>
        </button>
        <button onclick="goToStep('group')"
          style="padding:22px 12px;border:1.5px solid #e5e7eb;border-radius:14px;background:#fff;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;transition:all .15s;"
          onmouseover="this.style.borderColor='#7c3aed';this.style.background='#f5f3ff'"
          onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#fff'">
          <span style="font-size:32px;">👥</span>
          <span style="font-weight:700;color:#7c3aed;font-size:14px;">Ajak Teman</span>
          <span style="font-size:11px;color:var(--text-muted);">Buat kelompok</span>
        </button>
      </div>
    </div>

    {{-- ── STEP 2: KONFIRMASI SOLO ── --}}
    <div id="step-solo" style="display:none;padding:20px;">
      <div class="modal-header" style="margin-bottom:18px;">
        <div class="modal-title">Konfirmasi Pendaftaran Solo</div>
        <button class="modal-close" onclick="closeApplyModal()">✕</button>
      </div>

      {{-- Student card --}}
      <div style="display:flex;align-items:center;gap:12px;border:1.5px solid #e5e7eb;border-radius:12px;padding:12px 14px;margin-bottom:16px;">
        <div style="width:40px;height:40px;border-radius:50%;background:#e8eef8;color:#0c2a5c;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
          {{ $myInit }}
        </div>
        <div style="flex:1;">
          <div style="font-weight:700;font-size:14px;color:var(--text);">{{ $myName }}</div>
          <div style="font-size:12px;color:var(--text-muted);">{{ $myNim }}</div>
        </div>
        <span style="background:#1e3a6e;color:#fff;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">Solo</span>
      </div>

      {{-- Warning --}}
      <div style="background:#fff8ed;border:1px solid #fcd34d;border-radius:10px;padding:12px 14px;margin-bottom:20px;">
        <div style="font-size:12px;color:#92400e;line-height:1.6;">
          Kamu akan mendaftar sebagai peserta magang individu.<br>
          Tidak bisa tambah anggota setelah konfirmasi.
        </div>
      </div>

      {{-- Actions --}}
      <form id="form-solo" method="POST" action="">
        @csrf
        <input type="hidden" name="type" value="solo">
        <div style="display:flex;gap:10px;">
          <button type="button" onclick="goToStep('mode')"
            style="flex:1;padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;background:#fff;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);">
            ← Kembali
          </button>
          <button type="submit"
            style="flex:1;padding:12px;background:#1e3a6e;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">
            ✓ Konfirmasi
          </button>
        </div>
      </form>
    </div>

    {{-- ── STEP 3: KELOMPOK MAGANG ── --}}
    <div id="step-group" style="display:none;padding:20px;">
      <div class="modal-header" style="margin-bottom:18px;">
        <div class="modal-title">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          Kelompok Magang
        </div>
        <button class="modal-close" onclick="closeApplyModal()">✕</button>
      </div>

      {{-- Leader card (saya sebagai ketua) --}}
      <div style="display:flex;align-items:center;gap:12px;border:1.5px solid #e5e7eb;border-radius:12px;padding:12px 14px;margin-bottom:14px;">
        <div style="width:40px;height:40px;border-radius:50%;background:#e8eef8;color:#0c2a5c;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
          {{ $myInit }}
        </div>
        <div style="flex:1;">
          <div style="font-weight:700;font-size:14px;color:var(--text);">{{ $myName }}</div>
          <div style="font-size:12px;color:var(--text-muted);">{{ $myNim }}</div>
        </div>
        <span style="background:#1e3a6e;color:#fff;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">Ketua</span>
      </div>

      {{-- Daftar anggota yang diundang --}}
      <div id="invited-members-list" style="margin-bottom:12px;"></div>

      {{-- Input NIM --}}
      <div style="display:flex;gap:8px;margin-bottom:20px;">
        <input id="nim-invite-input" type="text" placeholder="Masukkan NIM anggota..."
          style="flex:1;padding:11px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;font-family:inherit;"
          onfocus="this.style.borderColor='#1e3a6e'" onblur="this.style.borderColor='#e5e7eb'"
          onkeydown="if(event.key==='Enter'){event.preventDefault();addInvitedMember();}">
        <button type="button" onclick="addInvitedMember()"
          style="padding:11px 18px;background:#1e3a6e;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;">
          Invite
        </button>
      </div>

      {{-- Actions --}}
      <form id="form-group" method="POST" action="">
        @csrf
        <input type="hidden" name="type" value="group">
        <div id="hidden-invited-nims"></div>
        <div style="display:flex;gap:10px;">
          <button type="button" onclick="goToStep('mode')"
            style="flex:1;padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;background:#fff;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);">
            ← Kembali
          </button>
          <button type="submit"
            style="flex:1;padding:12px;background:#1e3a6e;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;">
            ✓ Konfirmasi
          </button>
        </div>
      </form>
    </div>

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

var invitedNims = [];

function openApplyModal(jobId, title, company) {
  document.getElementById('modal-job-title').textContent    = title;
  document.getElementById('modal-company-name').textContent = company;

  var logo = document.getElementById('modal-job-logo');
  var words = company.split(' ').slice(0,2).map(w => w[0] ? w[0].toUpperCase() : '');
  logo.textContent       = words.join('');
  logo.style.background  = '#e8eef8';
  logo.style.color       = '#0c2a5c';

  var url = '/mahasiswa/lowongan/' + jobId + '/apply';
  document.getElementById('form-solo').action  = url;
  document.getElementById('form-group').action = url;

  invitedNims = [];
  renderInvitedList();
  document.getElementById('nim-invite-input').value = '';

  goToStep('mode');
  document.getElementById('modal-apply').classList.add('open');
}

function closeApplyModal() {
  document.getElementById('modal-apply').classList.remove('open');
}

function goToStep(step) {
  ['step-mode','step-solo','step-group'].forEach(function(id) {
    document.getElementById(id).style.display = 'none';
  });
  document.getElementById('step-' + step).style.display = 'block';
}

function addInvitedMember() {
  var input = document.getElementById('nim-invite-input');
  var nim   = input.value.trim();
  if (!nim) return;
  if (invitedNims.includes(nim)) {
    input.style.borderColor = '#dc2626';
    setTimeout(function(){ input.style.borderColor = '#e5e7eb'; }, 1500);
    return;
  }
  invitedNims.push(nim);
  input.value = '';
  input.focus();
  renderInvitedList();
}

function removeInvited(nim) {
  invitedNims = invitedNims.filter(function(n){ return n !== nim; });
  renderInvitedList();
}

function renderInvitedList() {
  var list   = document.getElementById('invited-members-list');
  var hidden = document.getElementById('hidden-invited-nims');
  list.innerHTML   = '';
  hidden.innerHTML = '';

  invitedNims.forEach(function(nim) {
    var words = nim.split(' ').slice(0,2).map(function(w){ return w[0] ? w[0].toUpperCase() : ''; });

    var item = document.createElement('div');
    item.style.cssText = 'display:flex;align-items:center;gap:10px;border:1.5px solid #e5e7eb;border-radius:10px;padding:10px 12px;margin-bottom:8px;';
    item.innerHTML =
      '<div style="width:36px;height:36px;border-radius:50%;background:#f3e8ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;">'
        + words.join('') +
      '</div>'
      + '<div style="flex:1;font-size:13px;font-weight:600;color:var(--text);">' + nim + '</div>'
      + '<button type="button" onclick="removeInvited(\'' + nim.replace(/'/g, "\\'") + '\')" '
        + 'style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:2px 4px;">✕</button>';
    list.appendChild(item);

    var inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'invited_nims[]';
    inp.value = nim;
    hidden.appendChild(inp);
  });
}
</script>
@endpush

@endsection
