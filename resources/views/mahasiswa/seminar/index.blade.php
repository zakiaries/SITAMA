@extends('layouts.mahasiswa')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
@section('content')

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="page-header">
    <div class="page-title">Seminar Magang</div>
    <button class="btn btn-primary" onclick="document.getElementById('modal-seminar').classList.add('open')">+ Ajukan Jadwal Seminar</button>
  </div>
  <div class="header-banner">
    <h2>🎓 Seminar Wajib Magang</h2>
    <p>Ajukan jadwal seminar Anda sendiri, atau daftar pada seminar yang tersedia.</p>
  </div>

  {{-- Jadwal Seminar Saya --}}
  <div style="font-size:13px;font-weight:700;color:var(--primary);margin:8px 0 12px;">
    Jadwal Seminar Saya ({{ $mySeminars->count() }})
  </div>
  @forelse($mySeminars as $seminar)
  <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', $seminar->id) }}'">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">{{ $seminar->program }}</div>
      </div>
      @php
        $stBg = $seminar->status === 'completed' ? '#dcfce7' : ($seminar->status === 'cancelled' ? '#fee2e2' : '#eff6ff');
        $stCol = $seminar->status === 'completed' ? '#16a34a' : ($seminar->status === 'cancelled' ? '#dc2626' : '#2563eb');
      @endphp
      <span class="badge" style="background:{{ $stBg }};color:{{ $stCol }};">{{ ucfirst($seminar->status) }}</span>
    </div>
    <div class="sem-meta">
      @if($seminar->date)<div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>@endif
      @if($seminar->time)<div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>@endif
      @if($seminar->location)<div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>@endif
    </div>
    @if($seminar->status === 'scheduled')
    <div style="display:flex;gap:8px;margin-top:12px;" onclick="event.stopPropagation()">
      <button type="button" class="btn btn-outline btn-sm"
        onclick="openEditSeminar({{ $seminar->id }}, @js($seminar->title), '{{ $seminar->date?->format('Y-m-d') }}', @js($seminar->time), @js($seminar->location), @js($seminar->description))">✏ Edit</button>
      <form method="POST" action="{{ route('mahasiswa.seminar.destroy', $seminar->id) }}"
            data-confirm="Batalkan pengajuan seminar ini?" data-confirm-danger>
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;">🗑 Batalkan</button>
      </form>
    </div>
    @else
    <div class="sem-tap">Tap untuk detail »</div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">
    <p>Anda belum mengajukan jadwal seminar. Klik "Ajukan Jadwal Seminar" untuk menentukan tanggalnya.</p>
  </div>
  @endforelse

  <div style="font-size:13px;font-weight:700;color:var(--primary);margin:20px 0 12px;">
    Seminar Tersedia ({{ $seminars->count() }})
  </div>

  @forelse($seminars as $seminar)
  <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', $seminar->id) }}'">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">{{ $seminar->program }}</div>
      </div>
      @if(in_array($seminar->id, $registeredIds))
        <span class="badge" style="background:#dcfce7;color:#16a34a;">Terdaftar</span>
      @else
        <span class="badge jadwal">Terjadwal</span>
      @endif
    </div>
    <div class="sem-meta">
      @if($seminar->date)
      <div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>
      @endif
      @if($seminar->time)
      <div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>
      @endif
      @if($seminar->location)
      <div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>
      @endif
    </div>
    <div class="sem-tap">Tap untuk detail »</div>
  </div>
  @empty
  <div style="text-align:center;padding:40px;color:var(--text-muted);">
    <p>Belum ada seminar tersedia.</p>
  </div>
  @endforelse

  {{-- Modal Ajukan Jadwal Seminar --}}
  <div class="modal-overlay" id="modal-seminar" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Ajukan Jadwal Seminar</div>
        <button class="modal-close" onclick="document.getElementById('modal-seminar').classList.remove('open')">✕</button>
      </div>
      <form method="POST" action="{{ route('mahasiswa.seminar.store') }}">
        @csrf
        <div class="form-group">
          <label>Judul Seminar</label>
          <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Seminar Hasil Magang" required>
        </div>
        <div class="form-group">
          <label>Tanggal yang Diinginkan</label>
          <input type="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Waktu (opsional)</label>
          <input type="text" name="time" value="{{ old('time') }}" placeholder="Contoh: 09:00 - 11:00">
        </div>
        <div class="form-group">
          <label>Tempat / Ruang (opsional)</label>
          <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: Ruang Seminar Lt. 2">
        </div>
        <div class="form-group">
          <label>Deskripsi (opsional)</label>
          <textarea name="description" rows="3" placeholder="Keterangan tambahan..." style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;">{{ old('description') }}</textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-seminar').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Ajukan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Edit Jadwal Seminar --}}
  <div class="modal-overlay" id="modal-edit-seminar" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Edit Jadwal Seminar</div>
        <button class="modal-close" onclick="document.getElementById('modal-edit-seminar').classList.remove('open')">✕</button>
      </div>
      <form method="POST" id="form-edit-seminar" action="">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label>Judul Seminar</label>
          <input type="text" name="title" id="edit-title" required>
        </div>
        <div class="form-group">
          <label>Tanggal yang Diinginkan</label>
          <input type="date" name="date" id="edit-date" min="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Waktu (opsional)</label>
          <input type="text" name="time" id="edit-time" placeholder="Contoh: 09:00 - 11:00">
        </div>
        <div class="form-group">
          <label>Tempat / Ruang (opsional)</label>
          <input type="text" name="location" id="edit-location" placeholder="Contoh: Ruang Seminar Lt. 2">
        </div>
        <div class="form-group">
          <label>Deskripsi (opsional)</label>
          <textarea name="description" id="edit-description" rows="3" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
          <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-edit-seminar').classList.remove('open')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  function openEditSeminar(id, title, date, time, location, description) {
    var form = document.getElementById('form-edit-seminar');
    form.action = '{{ url('mahasiswa/seminar') }}/' + id;
    document.getElementById('edit-title').value = title || '';
    document.getElementById('edit-date').value = date || '';
    document.getElementById('edit-time').value = time || '';
    document.getElementById('edit-location').value = location || '';
    document.getElementById('edit-description').value = description || '';
    document.getElementById('modal-edit-seminar').classList.add('open');
  }
</script>
@if($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('modal-seminar').classList.add('open');
  });
</script>
@endif
@endpush
