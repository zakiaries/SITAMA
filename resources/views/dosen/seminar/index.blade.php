@extends('layouts.dosen')
@section('title', 'Seminar Magang')
@php $title = 'Seminar Magang'; @endphp
@section('content')

<x-form-errors/>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('error') }}</div>
@endif

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
  <div>
    <div class="page-title">Seminar Magang</div>
    <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
      Buat sesi seminar untuk mahasiswa bimbingan yang sudah selesai magang. Mereka maju bergantian dalam satu sesi.
    </div>
  </div>
  @if($eligibleStudents->isNotEmpty())
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-buat').classList.add('open')">+ Buat Sesi Seminar</button>
  @endif
</div>

@if($eligibleStudents->isEmpty() && $seminars->isEmpty())
  <div class="card" style="text-align:center;padding:40px 20px;color:var(--text-muted);">
    <p style="margin:0;">Belum ada mahasiswa bimbingan yang selesai magang. Sesi seminar bisa dibuat setelah ada mahasiswa yang magangnya ditandai selesai.</p>
  </div>
@endif

@foreach($seminars as $s)
@php
  $stMap = [
    'draft'     => ['Menunggu Ketersediaan', 'var(--warn-bg)', 'var(--warn-text)'],
    'scheduled' => ['Terjadwal', 'var(--blue-tint)', 'var(--primary)'],
    'completed' => ['Selesai (Disahkan)', 'var(--success-bg)', 'var(--success-text)'],
    'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
  ];
  $st = $stMap[$s->status] ?? [$s->status, 'var(--warm-2)', 'var(--text-secondary)'];
  $guests = $s->attendances->count();
  // Lewat hari-H: sesi sudah berlangsung, jadi judul/deskripsi/jam/ruang dikunci.
  $terkunci = $s->jadwalSudahLewat();
@endphp
<div class="card" style="margin-bottom:16px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text);">{{ $s->title }}</div>
      @if($s->date)
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
          {{ $s->date->format('d M Y') }}{{ $s->time ? ' · '.$s->time : '' }}{{ $s->location ? ' · '.$s->location : '' }}
        </div>
      @endif
      @if($s->description)
        <div style="font-size:12px;color:var(--text-secondary);margin-top:4px;">{{ $s->description }}</div>
      @endif
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
      <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
      @if(in_array($s->status, ['draft','scheduled']) && ! $terkunci)
        <button type="button" class="btn btn-outline btn-sm" onclick="openEditSeminar({{ $s->id }}, @js($s->title), @js($s->description))"><x-icon name="pencil" :size="13"/> Ubah Detail</button>
      @elseif($terkunci && $s->status !== 'completed')
        <span style="font-size:11px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;">
          <x-icon name="lock" :size="12"/> Terkunci (tanggal sudah lewat)
        </span>
      @endif
    </div>
  </div>

  {{-- Penyaji + ketersediaan --}}
  <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Penyaji ({{ $s->presenters->count() }})</div>
  <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
    @foreach($s->presenters as $p)
    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:8px 10px;background:var(--warm);border-radius:8px;">
      <div style="font-size:13px;font-weight:600;color:var(--text);">{{ $p->student->user->name ?? '-' }}</div>
      <div style="font-size:12px;color:{{ $p->responded_at ? 'var(--text-secondary)' : 'var(--text-muted)' }};">
        @if($p->responded_at)
          Bisa: {{ $p->available_dates }}
        @else
          <em>belum mengisi ketersediaan</em>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  {{-- Aksi per status --}}
  @if($s->status === 'draft')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      <div style="font-size:12px;font-weight:700;color:var(--text);margin-bottom:8px;">Tetapkan Jadwal Final</div>
      <form method="POST" action="{{ route('dosen.seminar.finalize', $s) }}">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:10px;">
          <input type="date" name="date" min="{{ now()->toDateString() }}" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <input type="text" name="time" placeholder="Waktu (mis. 09:00-11:00)" style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          <input type="text" name="location" placeholder="Ruang/tempat" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Tetapkan &amp; Jadwalkan</button>
      </form>
      <form method="POST" action="{{ route('dosen.seminar.destroy', $s) }}" style="margin-top:8px;"
            data-confirm="Batalkan sesi seminar ini? Data penyaji &amp; daftar hadirnya ikut terhapus." data-confirm-danger>
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;">Batalkan Sesi</button>
      </form>
    </div>

  @elseif($s->status === 'scheduled')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <div style="font-size:12px;color:var(--text-secondary);">
          Daftar hadir audiens:
          <strong style="color:{{ $guests >= \App\Models\Seminar::MIN_GUESTS ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ \App\Models\Seminar::MIN_GUESTS }}</strong>
        </div>
        @if($s->access_token && ! $terkunci)
          <a href="{{ route('dosen.seminar.qr', $s) }}" target="_blank" style="font-size:12px;color:var(--primary);font-weight:600;">Tampilkan QR Daftar Hadir (layar) →</a>
        @elseif($terkunci)
          <span style="font-size:12px;color:var(--text-muted);">Daftar hadir ditutup</span>
        @endif
      </div>
      <form method="POST" action="{{ route('dosen.seminar.sahkan', $s) }}"
            data-confirm="Sahkan bahwa seminar ini telah berlangsung dan selesai?">
        @csrf
        @php $cukupAudiens = $guests >= \App\Models\Seminar::MIN_GUESTS; @endphp
        {{-- Warna tombol menandakan keadaannya: biru saat bisa ditekan, abu-abu
             saat belum bisa. Gayanya diurus .btn:disabled di simama.css. --}}
        <button type="submit" class="btn btn-primary btn-sm" @disabled(! $cukupAudiens)
          title="{{ $cukupAudiens ? 'Sahkan sesi ini' : 'Audiens baru ' . $guests . ' dari ' . \App\Models\Seminar::MIN_GUESTS . ' — belum bisa disahkan' }}">
          <x-icon name="check" :size="14"/> Sahkan Seminar (Saksi)
        </button>
      </form>
      @if($terkunci)
        <div style="margin-top:10px;display:flex;align-items:flex-start;gap:8px;background:var(--warm);border-radius:8px;padding:10px 12px;font-size:11.5px;color:var(--text-secondary);line-height:1.55;">
          <span style="color:var(--text-muted);flex-shrink:0;margin-top:1px;"><x-icon name="lock" :size="13"/></span>
          <span>
            Tanggal seminar <strong style="color:var(--text);">{{ $s->date?->format('d M Y') }}</strong> sudah lewat.
            Bila seminarnya berlangsung, tinggal disahkan. Bila berhalangan,
            <strong style="color:var(--text);">jadwalkan ulang</strong> lewat kotak di bawah.
          </span>
        </div>
      @endif

      <details style="margin-top:10px;" {{ $terkunci ? 'open' : '' }}>
        <summary style="font-size:12px;color:var(--primary);cursor:pointer;">
          {{ $terkunci ? 'Jadwalkan ulang seminar' : 'Ubah jadwal / jam / lokasi' }}
        </summary>
        <form method="POST" action="{{ route('dosen.seminar.finalize', $s) }}" style="margin-top:8px;">
          @csrf
          {{-- Tanggal kini ikut bisa diubah. Dulu dikunci sekali tetapkan, dan
               itu menjebak dosen yang berhalangan: sesinya tertinggal di
               tanggal yang sudah lewat, dan satu-satunya jalan keluar adalah
               membatalkan sesi lalu membuat ulang dari nol — membuang
               ketersediaan tanggal yang sudah diisi para penyaji. --}}
          <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:8px;">
            Memindahkan tanggal akan memberi tahu semua mahasiswa penyaji beserta jadwal lamanya.
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-bottom:8px;">
            <input type="date" name="date" value="{{ $s->date?->toDateString() }}" min="{{ now()->toDateString() }}" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
            <input type="text" name="time" value="{{ $s->time }}" placeholder="Waktu (mis. 09:00-11:00)" style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
            <input type="text" name="location" value="{{ $s->location }}" placeholder="Ruang/tempat" required style="padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
          </div>
          <button type="submit" class="btn btn-outline btn-sm">Simpan Perubahan</button>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Mahasiswa penyaji akan diberi tahu perubahannya.</div>
        </form>
      </details>
    </div>

  @elseif($s->status === 'completed')
    <div style="border-top:1px solid var(--border-subtle);padding-top:12px;font-size:12px;color:var(--success-text);">
      <x-icon name="check" :size="13"/> Disahkan {{ $s->witnessed_at?->format('d M Y H:i') }} · Audiens hadir: {{ $guests }}
    </div>
  @endif
</div>
@endforeach

{{-- Modal Buat Sesi --}}
<div class="modal-overlay" id="modal-buat" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Buat Sesi Seminar</div>
      <button class="modal-close" onclick="document.getElementById('modal-buat').classList.remove('open')"><x-icon name="x" :size="14"/></button>
    </div>
    <form method="POST" action="{{ route('dosen.seminar.store') }}">
      @csrf
      <div class="form-group">
        <label>Judul Sesi</label>
        <input type="text" name="title" value="Seminar Hasil Magang" required>
      </div>
      <div class="form-group">
        <label>Pilih Mahasiswa Penyaji (sudah selesai magang)</label>
        <div class="pick-list">
          @forelse($eligibleStudents as $st)
            <label class="pick-row">
              <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" checked>
              <span style="flex:1;min-width:0;">
                <span class="pick-name">{{ $st->user->name ?? '-' }}</span>
                <span class="pick-sub">{{ $st->user->username ?? '' }}</span>
              </span>
            </label>
          @empty
            <div class="pick-empty">Belum ada mahasiswa bimbingan yang selesai magang.</div>
          @endforelse
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-buat').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Buat Sesi</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Ubah Detail Sesi --}}
<div class="modal-overlay" id="modal-edit-seminar" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Ubah Detail Sesi</div>
      <button class="modal-close" onclick="document.getElementById('modal-edit-seminar').classList.remove('open')"><x-icon name="x" :size="14"/></button>
    </div>
    <form method="POST" id="form-edit-seminar" action="">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label>Judul Sesi</label>
        <input type="text" name="title" id="edit-sem-title" required>
      </div>
      <div class="form-group">
        <label>Deskripsi (opsional)</label>
        <textarea name="description" id="edit-sem-desc" rows="3" placeholder="Catatan/keterangan sesi seminar..." style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      </div>
      <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:8px;">Untuk mengubah tanggal/waktu/lokasi, gunakan form jadwal pada kartu sesi.</div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-edit-seminar').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function openEditSeminar(id, title, description) {
    var form = document.getElementById('form-edit-seminar');
    form.action = '{{ url('dosen/seminar') }}/' + id;
    document.getElementById('edit-sem-title').value = title || '';
    document.getElementById('edit-sem-desc').value = description || '';
    document.getElementById('modal-edit-seminar').classList.add('open');
  }
</script>
@endpush
