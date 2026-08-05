@extends('layouts.mahasiswa')
@section('title', 'Log Book')
@php $title = 'Log Book'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ $errors->first() }}
    </div>
  @endif

  @if($terkunci)
    <div style="background:var(--warn-bg);border:1px solid #F0DCA4;color:var(--warn-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ $terkunci }}
    </div>
  @elseif(!$canFill)
    <div style="background:var(--warn-bg);border:1px solid #F0DCA4;color:var(--warn-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      Kamu belum memiliki magang aktif. Log book bisa diisi setelah pengajuan magangmu disetujui Kaprodi.
    </div>
  @elseif($noLecturer)
    <div style="background:var(--warn-bg);border:1px solid #F0DCA4;color:var(--warn-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      Dosen pembimbing belum ditugaskan Kaprodi — log book tetap bisa diisi, tapi belum bisa dipantau dosen.
    </div>
  @endif

  {{-- Judulnya sudah tercetak di header atas; barisnya disisakan untuk tombol. --}}
  <div class="page-header" style="justify-content:flex-end;">
    @if($canFill)
      <button class="btn btn-primary" onclick="document.getElementById('modal-logbook').classList.add('open')">+ Tambah Log Book</button>
    @else
      {{-- Gaya matinya diurus .btn:disabled di simama.css, bukan opacity setempat. --}}
      <button class="btn btn-primary" disabled
              title="{{ $terkunci ?? 'Belum ada magang aktif' }}">+ Tambah Log Book</button>
    @endif
  </div>

  <form method="GET" action="{{ route('mahasiswa.logbook') }}">
    <div class="search-bar">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input name="search" placeholder="Pencarian..." value="{{ request('search') }}">
    </div>
  </form>

  @forelse($logBooks as $lb)
  <div class="exp-item {{ $loop->first ? 'open' : '' }}">
    <div class="exp-header" onclick="toggle(this)">
      <div>
        <div class="exp-title">{{ $lb->title }}</div>
        <div class="exp-date">{{ $lb->date->format('d/m/Y') }}</div>
      </div>
      <div class="chevron">▾</div>
    </div>
    <div class="exp-body">
      <div class="field-label">Aktivitas</div>
      <div class="field-value">{{ $lb->activity }}</div>
      @if($lb->lecturer_note)
      <div class="field-group" style="border-left:3px solid var(--primary);padding-left:10px;margin-top:12px;">
        <div class="field-label" style="color:var(--primary);">Catatan Dosen Pembimbing (Kampus)</div>
        <div class="field-value">{{ $lb->lecturer_note }}</div>
      </div>
      @endif
      @if($lb->industry_note)
      <div class="field-group" style="border-left:3px solid var(--success-text);padding-left:10px;margin-top:12px;">
        <div class="field-label" style="color:var(--success-text);">Catatan Pembimbing Industri</div>
        <div class="field-value">{{ $lb->industry_note }}</div>
      </div>
      @endif
      @unless($terkunci)
      <div style="display:flex;gap:8px;margin-top:12px;">
        <button type="button" class="btn btn-outline btn-sm"
          onclick="openEditLogbook({{ $lb->id }}, @js($lb->title), '{{ $lb->date->format('Y-m-d') }}', @js($lb->activity))">
          <x-icon name="pencil" :size="14"/> Edit
        </button>
        <form method="POST" action="{{ route('mahasiswa.logbook.destroy', $lb->id) }}" data-confirm="Hapus log book ini?" data-confirm-danger>
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm"><x-icon name="trash" :size="14"/> Hapus</button>
        </form>
      </div>
      @endunless
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada data log book.</p>
  </div>
  @endforelse

  {{-- Modal Tambah Log Book --}}
  <div class="modal-overlay" id="modal-logbook" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Tambah Log Book</div>
        <button class="modal-close" onclick="document.getElementById('modal-logbook').classList.remove('open')"><x-icon name="x" :size="14"/></button>
      </div>
      <form method="POST" action="{{ route('mahasiswa.logbook.store') }}">
        @csrf
        <div class="form-group">
          <label>Judul Kegiatan</label>
          <input type="text" name="title" value="{{ old('title') }}" required>
        </div>
        <div class="form-group">
          <label>Tanggal</label>
          <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
        </div>
        <div class="form-group">
          <label>Deskripsi Aktivitas</label>
          <textarea name="activity" rows="4" required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;">{{ old('activity') }}</textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-logbook').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Edit Log Book --}}
  <div class="modal-overlay" id="modal-logbook-edit" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Edit Log Book</div>
        <button class="modal-close" onclick="document.getElementById('modal-logbook-edit').classList.remove('open')"><x-icon name="x" :size="14"/></button>
      </div>
      <form method="POST" id="form-logbook-edit" action="">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label>Judul Kegiatan</label>
          <input type="text" name="title" id="edit-lb-title" required>
        </div>
        <div class="form-group">
          <label>Tanggal</label>
          <input type="date" name="date" id="edit-lb-date" required>
        </div>
        <div class="form-group">
          <label>Deskripsi Aktivitas</label>
          <textarea name="activity" id="edit-lb-activity" rows="4" required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-logbook-edit').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  function openEditLogbook(id, title, date, activity) {
    var form = document.getElementById('form-logbook-edit');
    form.action = '{{ url('mahasiswa/logbook') }}/' + id;
    document.getElementById('edit-lb-title').value = title;
    document.getElementById('edit-lb-date').value = date;
    document.getElementById('edit-lb-activity').value = activity;
    document.getElementById('modal-logbook-edit').classList.add('open');
  }
</script>
@endpush
