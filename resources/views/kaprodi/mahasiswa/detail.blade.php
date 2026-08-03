@extends('layouts.kaprodi')
@section('title', 'Detail Mahasiswa')
@php $title = 'Detail Mahasiswa'; @endphp

@push('styles')
<style>
.student-hero {
  background: var(--warm);
  border-radius: 14px;
  padding: 26px 28px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 20px;
}
.hero-avatar {
  width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
  background: var(--primary); border: none;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 800; color: #fff;
}
.hero-detail { flex: 1; }
.hero-sname  { font-size: 24px; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 6px; }
.hero-nim    { display: inline-block; background: var(--blue-tint); color: var(--primary); font-size: 12px; font-weight: 600; padding: 3px 12px; border-radius: 9999px; margin-bottom: 6px; }
.hero-email  { font-size: 13px; color: var(--text-secondary); }

.nilai-card { background: #fff; border: 1.5px solid var(--border); border-radius: 12px; padding: 18px; }
.nilai-row  { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
.nilai-row:last-child { border-bottom: none; }
.nilai-row.total { border-top: 2px solid var(--border); margin-top: 4px; padding-top: 12px; font-weight: 700; }
.nilai-badge {
  background: var(--warm); color: var(--primary); font-weight: 700;
  font-size: 13px; padding: 4px 14px; border-radius: 20px; min-width: 48px; text-align: center;
}
.nilai-badge.empty { background: var(--warm); color: var(--text-muted); font-weight: 600; }

.btn-finish { background: var(--success-text); color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; width: 100%; }
.btn-finish:hover { background: var(--success-text); }
.btn-reopen { background: #fff; color: var(--text-secondary); border: 1.5px solid var(--border); border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; width: 100%; }
.btn-reopen:hover { background: var(--warm); }
</style>
@endpush

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
@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    {{ $errors->first() }}
  </div>
@endif

{{-- Back Button --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
  <a href="{{ route('kaprodi.mahasiswa.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kembali</a>
  <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('modal-reset-pw').style.display='flex'">
    <x-icon name="key" :size="14"/> Reset Password
  </button>
</div>

{{-- Hero --}}
<div class="student-hero">
  <x-avatar :user="$student->user" class="hero-avatar" />
  <div class="hero-detail">
    <div class="hero-sname">{{ $student->user->name }}</div>
    <div class="hero-nim">{{ $student->user->username }}</div>
    <div class="hero-email">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
      {{ $student->user->email }}
    </div>
  </div>
</div>

@if(!$internship)

  <div class="card">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Info Mahasiswa</div>
      <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum Magang</span>
    </div>
    <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
    <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
    <div class="info-row"><div class="info-key">Prodi</div><div class="info-val">{{ $student->study_program }}</div></div>
    <div class="info-row"><div class="info-key">T. Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
  </div>
  <p style="color:var(--text-muted);font-size:13px;margin-top:16px;text-align:center;">
    Mahasiswa ini belum memiliki data magang, sehingga belum ada nilai untuk ditampilkan.
  </p>

  {{-- Kredensial pembimbing industri baru --}}
  @if(session('new_pic_credentials'))
    @php $cred = session('new_pic_credentials'); @endphp
    <div style="background:var(--success-bg);border:1px solid #A7E8CF;border-radius:10px;padding:14px 16px;margin-top:16px;">
      <div style="font-weight:700;color:var(--success-text);margin-bottom:8px;">Akun Pembimbing Industri Berhasil Dibuat</div>
      <p style="font-size:12px;color:var(--success-text);margin-bottom:8px;">Simpan dan teruskan kredensial berikut ke pembimbing industri. Ini hanya ditampilkan sekali.</p>
      <div style="font-size:13px;color:var(--success-text);">
        <div><strong>Nama:</strong> {{ $cred['name'] }}</div>
        <div><strong>Username:</strong> {{ $cred['username'] }}</div>
        <div><strong>Password:</strong> <code style="background:var(--success-bg);padding:1px 6px;border-radius:4px;">{{ $cred['password'] }}</code></div>
      </div>
    </div>
  @endif

  {{-- Form Catat Magang --}}
  <div class="card" style="margin-top:16px;">
    <div class="card-title" style="margin-bottom:6px;">Catat Magang</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;">
      Isi data magang mahasiswa. Jika perusahaan atau pembimbing belum ada, isi langsung di bawah.
    </p>

    {{-- autocomplete="off" — alasan sama seperti form Ajukan Magang mahasiswa:
         peramban memulihkan pilihan dropdown sendiri saat halaman dimuat ulang. --}}
    <form method="POST" action="{{ route('kaprodi.mahasiswa.internship.store', $student) }}" autocomplete="off">
      @csrf

      {{-- PERUSAHAAN --}}
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">Perusahaan <span style="color:var(--danger);">*</span></div>
      <div style="display:flex;gap:8px;margin-bottom:6px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="existing" checked onchange="toggleCompanyMode(this.value)"> Pilih yang terdaftar
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="company_mode" value="new" onchange="toggleCompanyMode(this.value)"> Tambah baru
        </label>
      </div>
      <div id="company-existing" style="margin-bottom:12px;">
        <select name="company_id" id="company_id_select" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih perusahaan —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div id="company-new" style="display:none;margin-bottom:12px;">
        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Nama perusahaan baru"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>

      {{-- PEMBIMBING INDUSTRI --}}
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;margin-top:4px;">Pembimbing Industri <span style="color:var(--danger);">*</span></div>
      <div style="display:flex;gap:8px;margin-bottom:6px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="pic_mode" value="existing" checked onchange="togglePicMode(this.value)"> Pilih yang terdaftar
        </label>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
          <input type="radio" name="pic_mode" value="new" onchange="togglePicMode(this.value)"> Buat akun baru
        </label>
      </div>
      <div id="pic-existing" style="margin-bottom:12px;">
        <select name="lecturer_industry_id" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <option value="">— Pilih pembimbing —</option>
          @foreach($industriLecturers as $l)
            <option value="{{ $l->id }}" {{ old('lecturer_industry_id') == $l->id ? 'selected' : '' }}>{{ $l->user->name ?? '-' }}</option>
          @endforeach
        </select>
      </div>
      <div id="pic-new" style="display:none;margin-bottom:12px;">
        <div style="background:var(--warm);border:1px solid var(--border);border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:8px;">
          <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Nama lengkap pembimbing"
            style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <input type="text" name="pic_username" value="{{ old('pic_username') }}" placeholder="Username (untuk login)"
            style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <div class="pw-wrap">
            <input type="password" name="pic_password" placeholder="Password (min. 8 karakter)"
              style="width:100%;padding:8px 42px 8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
            <x-password-toggle />
          </div>
          <input type="tel" name="pic_phone" value="{{ old('pic_phone') }}" placeholder="No. HP (opsional)"
            style="width:100%;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
      </div>

      {{-- POSISI & TANGGAL --}}
      <div class="form-group" style="margin-bottom:12px;">
        <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Posisi / Bidang</label>
        <input type="text" name="position" value="{{ old('position') }}" placeholder="Contoh: Frontend Developer"
          style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
        <div class="form-group" style="margin:0;">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Tanggal Mulai <span style="color:var(--danger);">*</span></label>
          <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
        <div class="form-group" style="margin:0;">
          <label style="display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px;">Tanggal Selesai <span style="color:var(--danger);">*</span></label>
          <input type="date" name="end_date" value="{{ old('end_date', now()->addMonths(3)->toDateString()) }}" required
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">+ Catat Magang</button>
    </form>
  </div>

  <script>
    function toggleCompanyMode(mode) {
      document.getElementById('company-existing').style.display = mode === 'existing' ? 'block' : 'none';
      document.getElementById('company-new').style.display      = mode === 'new'      ? 'block' : 'none';
      document.getElementById('company_id_select').required     = mode === 'existing';
    }
    function togglePicMode(mode) {
      document.getElementById('pic-existing').style.display = mode === 'existing' ? 'block' : 'none';
      document.getElementById('pic-new').style.display      = mode === 'new'      ? 'block' : 'none';
    }
  </script>

@else

<div class="grid-2" style="gap:16px;align-items:start;">

  {{-- LEFT: Info Magang + Aksi --}}
  <div>
    <div class="card" style="margin-bottom:16px;">
      <div class="card-header" style="margin-bottom:12px;">
        <div class="card-title">Info Magang</div>
        @if($internship->is_finished)
          <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Selesai</span>
        @else
          <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
        @endif
      </div>
      <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val" style="color:var(--primary);font-weight:600;">{{ $internship->company->name ?? '-' }}</div></div>
      <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
      <div class="info-row"><div class="info-key">Mulai</div><div class="info-val">{{ $internship->start_date->format('d M Y') }}</div></div>
      <div class="info-row">
        <div class="info-key">Selesai</div>
        <div class="info-val">{{ $internship->end_date ? $internship->end_date->format('d M Y') : 'Belum selesai' }}</div>
      </div>
      <div class="info-row"><div class="info-key">Dosen Pembimbing</div><div class="info-val">{{ $internship->lecturer?->user?->name ?? 'Belum ditugaskan' }}</div></div>
      <div class="info-row"><div class="info-key">Pembimbing Industri</div><div class="info-val">{{ $internship->lecturerIndustry?->user?->name ?? 'Belum ditugaskan' }}</div></div>
      <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px;">
        <div class="info-row"><div class="info-key">Kelas</div><div class="info-val">{{ $student->the_class }}</div></div>
        <div class="info-row"><div class="info-key">Jurusan</div><div class="info-val">{{ $student->major }}</div></div>
        <div class="info-row"><div class="info-key">Prodi</div><div class="info-val">{{ $student->study_program }}</div></div>
        <div class="info-row"><div class="info-key">T. Akademik</div><div class="info-val">{{ $student->academic_year }}</div></div>
      </div>
    </div>

    {{-- Aksi: ACC Selesai Magang --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Status Magang</div>
      @if($internship->is_finished)
        <div style="background:var(--success-bg);border:1px solid #A7E8CF;border-radius:8px;padding:10px 14px;font-size:13px;color:var(--success-text);">
          <x-icon name="check" :size="13"/> Magang ini sudah selesai.
        </div>
        <p style="font-size:12.5px;color:var(--text-muted);margin:12px 0 10px;">
          Nilai dari dosen pembimbing dan pembimbing industri terkunci selama status ini aktif.
          Buka kembali hanya bila ada nilai atau data yang perlu dikoreksi.
        </p>
        <form method="POST" action="{{ route('kaprodi.mahasiswa.buka-finish', $student) }}"
              data-confirm="Buka kembali status selesai magang {{ $student->user->name }}? Nilai akan bisa diubah lagi oleh pembimbing.">
          @csrf
          <button type="submit" class="btn btn-outline btn-sm">
            <x-icon name="refresh" :size="14"/> Buka Kembali Status Selesai
          </button>
        </form>
      @elseif($internship->finish_requested)
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
          Mahasiswa mengajukan selesai magang. Periksa kelengkapan data, lalu ACC jika sudah sesuai.
        </p>
        <form method="POST" action="{{ route('kaprodi.mahasiswa.approve-finish', $student) }}"
              data-confirm="ACC selesai magang {{ $student->user->name }}?">
          @csrf
          <button type="submit" class="btn-finish" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;"><x-icon name="check" :size="15"/> ACC Selesai Magang</button>
        </form>
      @else
        <div style="background:var(--warm);border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--text-muted);">
          Menunggu mahasiswa mengajukan selesai magang.
        </div>
      @endif
    </div>
  </div>

  {{-- RIGHT: Nilai --}}
  <div>
    <div class="nilai-card">
      <div class="card-header" style="margin-bottom:14px;">
        <div class="card-title">Nilai</div>
      </div>
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px;">Dosen Pembimbing</div>
      @foreach($nilai['lecturer']['components'] as $item)
      <div class="nilai-row">
        <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}@if($item['weight']) <span style="color:var(--text-muted);font-size:11px;">({{ intval($item['weight']) }}%)</span>@endif</div>
        <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? '-' }}</div>
      </div>
      @endforeach
      <div class="nilai-row"><div style="font-size:12.5px;font-weight:600;">Rata Dosen</div><div class="nilai-badge">{{ $nilai['lecturer']['average'] ?? '-' }}</div></div>

      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:12px 0 2px;">Pembimbing Industri</div>
      @foreach($nilai['industry']['components'] as $item)
      <div class="nilai-row">
        <div style="font-size:13px;color:var(--text);">{{ $item['name'] }}</div>
        <div class="nilai-badge {{ $item['avg'] === null ? 'empty' : '' }}">{{ $item['avg'] ?? '-' }}</div>
      </div>
      @endforeach
      <div class="nilai-row"><div style="font-size:12.5px;font-weight:600;">Rata Industri</div><div class="nilai-badge">{{ $nilai['industry']['average'] ?? '-' }}</div></div>

      <div class="nilai-row total">
        <div style="font-size:13px;font-weight:700;">Nilai Akhir (Dosen + Industri)</div>
        <div class="nilai-badge" style="background:var(--primary);color:#fff;">{{ $nilai['final'] ?? '-' }}</div>
      </div>
    </div>
  </div>

</div>

@endif

{{-- Modal Reset Password --}}
<div id="modal-reset-pw" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:400px;margin:16px;">
    <div style="font-weight:700;font-size:15px;margin-bottom:4px;">Reset Password</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;">
      Reset password akun <strong>{{ $student->user->name }}</strong>
      <span style="font-size:11.5px;color:var(--text-muted);">({{ $student->user->username }})</span>
    </p>
    <form method="POST" action="{{ route('kaprodi.mahasiswa.reset-password', $student) }}">
      @csrf
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Password Baru <span style="color:var(--danger);">*</span></label>
          <div class="pw-wrap">
            <input type="password" name="new_password" required minlength="8"
              style="width:100%;padding:9px 42px 9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;"
              placeholder="Minimal 8 karakter">
            <x-password-toggle />
          </div>
        </div>
        <div>
          <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:4px;">Konfirmasi Password <span style="color:var(--danger);">*</span></label>
          <div class="pw-wrap">
            <input type="password" name="new_password_confirmation" required minlength="8"
              style="width:100%;padding:9px 42px 9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
            <x-password-toggle />
          </div>
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

@if($errors->any())
<script>document.getElementById('modal-reset-pw').style.display='flex';</script>
@endif
