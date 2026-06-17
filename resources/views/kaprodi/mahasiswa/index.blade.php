@extends('layouts.kaprodi')
@section('title', 'Data Mahasiswa')
@php $title = 'Data Mahasiswa'; @endphp

@push('styles')
<style>
.filter-tabs { display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap; }
.filter-tab {
  padding:8px 16px;border:1.5px solid var(--border);border-radius:20px;
  font-size:12px;font-weight:600;color:var(--text-muted);background:#fff;
  cursor:pointer;text-decoration:none;transition:all .15s;
}
.filter-tab.active { background:var(--primary);color:#fff;border-color:var(--primary); }

.mhs-card {
  background:#fff;border:1.5px solid var(--border);border-radius:12px;
  padding:14px 16px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
}
.mhs-card.is-pending { border-color:#fdba74;background:#fffbf5;border-left:4px solid #ea580c; }
.pending-actions { display:flex;gap:8px;flex-shrink:0; }
.btn-setujui { background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-setujui:hover { background:#15803d; }
.btn-tolak { background:#fff;color:#dc2626;border:1.5px solid #dc2626;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit; }
.btn-tolak:hover { background:#fef2f2; }
.info-note { background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:12.5px;color:#1e40af;display:flex;gap:10px;align-items:flex-start; }
.mhs-av   { width:46px;height:46px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px; }
.mhs-info { flex:1;min-width:0; }
.mhs-name { font-size:14px;font-weight:700;color:var(--text);margin-bottom:2px; }
.mhs-meta { font-size:12px;color:var(--text-muted); }
.mhs-dosen { font-size:11px;color:var(--text-muted);margin-top:4px; }
.mhs-dosen strong { color:var(--primary); }
.st-badge { font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;flex-shrink:0; }
.st-aktif   { background:#eff6ff;color:#2563eb; }
.st-selesai { background:#dcfce7;color:#16a34a; }
.st-belum   { background:#fef9c3;color:#92400e; }
.st-pending { background:#fff7ed;color:#c2410c; }
</style>
@endpush

@section('content')

@if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- Search --}}
<form method="GET" action="{{ route('kaprodi.mahasiswa.index') }}">
  <input type="hidden" name="status" value="{{ $status }}">
  <div class="search-bar">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
    <input name="search" placeholder="Cari nama atau NIM..." value="{{ request('search') }}">
  </div>
</form>

{{-- Filter Tabs --}}
<div class="filter-tabs">
  @php
    $tabs = ['pending'=>'Menunggu','semua'=>'Semua','aktif'=>'Aktif','selesai'=>'Selesai','belum_magang'=>'Belum Magang'];
  @endphp
  @foreach($tabs as $key => $label)
  <a href="{{ route('kaprodi.mahasiswa.index', ['status' => $key, 'search' => request('search')]) }}"
     class="filter-tab {{ $status === $key ? 'active' : '' }}">
    {{ $label }} ({{ $counts[$key] }})
  </a>
  @endforeach
</div>

{{-- Info penjelasan saat di tab Menunggu --}}
@if($status === 'pending')
<div class="info-note">
  <span style="font-size:16px;">ℹ️</span>
  <span>Mahasiswa berikut baru mendaftar dan <strong>belum bisa masuk ke sistem</strong> sampai Anda menyetujui akunnya. Klik <strong>Setujui</strong> untuk mengaktifkan, atau <strong>Tolak</strong> jika data tidak valid.</span>
</div>
@endif

{{-- Student List --}}
@forelse($students as $student)
@php
  $internship      = $student->internships->first();
  // Dospem dari students.lecturer_id; fallback ke dospem yang menempel di internship.
  $assignedLecturer = $student->lecturer ?? $internship?->lecturer;
  $colors = [
    ['bg'=>'#e8eef8','text'=>'#0c2a5c'],['bg'=>'#e1f5ee','text'=>'#085041'],
    ['bg'=>'#faeeda','text'=>'#633806'],['bg'=>'#eeedfe','text'=>'#3c3489'],
    ['bg'=>'#fcebeb','text'=>'#791f1f'],
  ];
  $color    = $colors[$student->id % count($colors)];
  $initials = collect(explode(' ', $student->user->name ?? ''))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
@endphp
<div class="mhs-card {{ $student->status === 'pending' ? 'is-pending' : '' }}">
  <div class="mhs-av" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $initials }}</div>
  <div class="mhs-info">
    <div class="mhs-name">{{ $student->user->name }}</div>
    <div class="mhs-meta">{{ $student->user->username }} · {{ $student->the_class }}@if($internship?->company) · {{ $internship->company->name }}@endif</div>
    @if($student->status === 'active')
    <div class="mhs-dosen">
      Dospem: <strong>{{ $assignedLecturer?->user?->name ?? 'Belum di-plot' }}</strong>
    </div>
    @endif
  </div>

  @if($student->status === 'pending')
    {{-- Pendaftar baru: tunggu persetujuan --}}
    <span class="st-badge st-pending">⏳ Menunggu</span>
    <div class="pending-actions">
      <form method="POST" action="{{ route('kaprodi.mahasiswa.approve', $student) }}"
        data-confirm="Setujui akun {{ $student->user->name }}? Mahasiswa akan bisa login.">
        @csrf
        <button type="submit" class="btn-setujui">✓ Setujui</button>
      </form>
      <form method="POST" action="{{ route('kaprodi.mahasiswa.reject', $student) }}"
        data-confirm="Tolak pendaftaran {{ $student->user->name }}?" data-confirm-danger>
        @csrf
        <button type="submit" class="btn-tolak">✕ Tolak</button>
      </form>
    </div>
  @elseif(!$internship)
    <span class="st-badge st-belum">Belum Magang</span>
    <a href="{{ route('kaprodi.mahasiswa.detail', $student) }}" class="btn btn-outline btn-sm">Detail</a>
    <button type="button" class="btn btn-outline btn-sm"
      onclick="openAssign({{ $student->id }}, '{{ addslashes($student->user->name) }}', {{ $assignedLecturer?->id ?? 'null' }})">
      {{ $assignedLecturer ? 'Ganti Dosen' : '+ Plot Dosen' }}
    </button>
  @else
    <span class="st-badge {{ $internship->is_finished ? 'st-selesai' : 'st-aktif' }}">
      {{ $internship->is_finished ? 'Selesai' : 'Aktif' }}
    </span>
    <a href="{{ route('kaprodi.mahasiswa.detail', $student) }}" class="btn btn-outline btn-sm">Detail</a>
    <button type="button" class="btn btn-outline btn-sm"
      onclick="openAssign({{ $student->id }}, '{{ addslashes($student->user->name) }}', {{ $assignedLecturer?->id ?? 'null' }})">
      {{ $assignedLecturer ? 'Ganti Dosen' : '+ Plot Dosen' }}
    </button>
  @endif
</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  @if($status === 'pending')
    <div style="font-size:40px;margin-bottom:12px;">✅</div>
    <p style="font-weight:600;color:var(--text);">Tidak ada pendaftar yang menunggu</p>
    <p style="font-size:13px;">Semua pendaftaran mahasiswa sudah ditinjau.</p>
  @else
    <div style="font-size:40px;margin-bottom:12px;">👨‍🎓</div>
    <p>Tidak ada mahasiswa pada filter ini.</p>
  @endif
</div>
@endforelse

{{-- Modal Tugaskan Dosen --}}
<div class="modal-overlay" id="modal-assign" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Tugaskan Dosen Pembimbing</div>
      <button class="modal-close" onclick="document.getElementById('modal-assign').classList.remove('open')">✕</button>
    </div>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
      Pilih dosen pembimbing untuk <strong id="assign-name" style="color:var(--text);"></strong>
    </p>
    <form id="form-assign" method="POST" action="">
      @csrf
      <div class="form-group">
        <label style="text-transform:none;font-size:13px;font-weight:600;color:var(--text);">Dosen Pembimbing</label>
        <select name="lecturer_id" id="assign-select" required
          style="width:100%;border:1.5px solid var(--border);border-radius:8px;padding:10px 12px;font-size:13px;font-family:inherit;color:var(--text);outline:none;">
          <option value="">-- Pilih Dosen --</option>
          @foreach($lecturers as $lec)
            <option value="{{ $lec->id }}">{{ $lec->user->name ?? '-' }} ({{ $lec->user->username ?? '-' }})</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-assign').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Tugaskan</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openAssign(studentId, name, currentLecturerId) {
  document.getElementById('assign-name').textContent = name;
  document.getElementById('form-assign').action = '/kaprodi/mahasiswa/' + studentId + '/assign-lecturer';
  var select = document.getElementById('assign-select');
  select.value = currentLecturerId ? String(currentLecturerId) : '';
  document.getElementById('modal-assign').classList.add('open');
}
</script>
@endpush
