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

  @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
      {{ session('error') }}
    </div>
  @endif

  <div class="page-header">
    <div class="page-title">Seminar Magang</div>
    @if($canSubmit)
      <button class="btn btn-primary" onclick="document.getElementById('modal-seminar').classList.add('open')">+ Ajukan Jadwal Seminar</button>
    @else
      <button class="btn btn-primary" disabled title="Lengkapi semua syarat terlebih dahulu"
        style="opacity:.55;cursor:not-allowed;">+ Ajukan Jadwal Seminar</button>
    @endif
  </div>
  <div class="header-banner">
    <h2>🎓 Seminar Wajib Magang</h2>
    <p>Ajukan jadwal seminar Anda sendiri, atau daftar pada seminar yang tersedia.</p>
  </div>

  {{-- Syarat kelayakan pengajuan seminar --}}
  <div class="card" style="margin-bottom:16px;border-left:4px solid {{ $canSubmit ? '#16a34a' : '#f59e0b' }};">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
      <div style="font-size:13px;font-weight:700;color:var(--text);">Syarat Pengajuan Seminar</div>
      @if($canSubmit)
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">✓ Semua syarat terpenuhi</span>
      @else
        <span style="background:#fef3c7;color:#854f0b;font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum lengkap</span>
      @endif
    </div>
    @foreach($requirements as $req)
    <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
      <div style="flex-shrink:0;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
        background:{{ $req['met'] ? '#dcfce7' : '#fee2e2' }};color:{{ $req['met'] ? '#16a34a' : '#dc2626' }};">
        {{ $req['met'] ? '✓' : '✕' }}
      </div>
      <div>
        <div style="font-size:13px;color:var(--text);font-weight:{{ $req['met'] ? '400' : '600' }};">{{ $req['label'] }}</div>
        @unless($req['met'])
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $req['hint'] }}</div>
        @endunless
      </div>
    </div>
    @endforeach
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
    @php $aud = $seminar->audienceCount(); $met = $aud >= \App\Models\Seminar::MIN_AUDIENCE; @endphp
    <div style="margin-top:10px;" onclick="event.stopPropagation()">
      <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
        <span style="color:var(--text-muted);">Audiens terdaftar</span>
        <span style="font-weight:700;color:{{ $met ? '#16a34a' : '#854f0b' }};">{{ $aud }}/{{ \App\Models\Seminar::MIN_AUDIENCE }} {{ $met ? '✓' : '' }}</span>
      </div>
      <div style="height:6px;background:#e5e7eb;border-radius:20px;overflow:hidden;">
        <div style="height:100%;width:{{ min(100, $aud / \App\Models\Seminar::MIN_AUDIENCE * 100) }}%;background:{{ $met ? '#16a34a' : '#f59e0b' }};"></div>
      </div>
      @unless($met)
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Butuh minimal {{ \App\Models\Seminar::MIN_AUDIENCE }} audiens sebelum seminar dapat dilaksanakan.</div>
      @endunless
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

  {{-- Seminar Hasil Magang Mahasiswa Lain (daftar sebagai audiens) --}}
  <div style="font-size:13px;font-weight:700;color:var(--primary);margin:20px 0 12px;">
    Seminar Mahasiswa Lain ({{ $peerSeminars->count() }})
  </div>
  <div style="font-size:12px;color:var(--text-muted);margin:-6px 0 12px;">
    Daftar sebagai audiens untuk membantu memenuhi kuota minimal {{ \App\Models\Seminar::MIN_AUDIENCE }} audiens.
  </div>
  @forelse($peerSeminars as $seminar)
  @php
    $aud = $seminar->audienceCount();
    $met = $aud >= \App\Models\Seminar::MIN_AUDIENCE;
    $isReg = in_array($seminar->id, $registeredIds);
  @endphp
  <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', $seminar->id) }}'">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">Penyaji: {{ $seminar->student->user->name ?? '-' }}</div>
      </div>
      @if($isReg)
        <span class="badge" style="background:#dcfce7;color:#16a34a;">Terdaftar</span>
      @else
        <span class="badge jadwal">Terjadwal</span>
      @endif
    </div>
    <div class="sem-meta">
      @if($seminar->date)<div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>@endif
      @if($seminar->time)<div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>@endif
      @if($seminar->location)<div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>@endif
    </div>
    <div style="margin-top:10px;">
      <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
        <span style="color:var(--text-muted);">Audiens terdaftar</span>
        <span style="font-weight:700;color:{{ $met ? '#16a34a' : '#854f0b' }};">{{ $aud }}/{{ \App\Models\Seminar::MIN_AUDIENCE }} {{ $met ? '✓' : '' }}</span>
      </div>
      <div style="height:6px;background:#e5e7eb;border-radius:20px;overflow:hidden;">
        <div style="height:100%;width:{{ min(100, $aud / \App\Models\Seminar::MIN_AUDIENCE * 100) }}%;background:{{ $met ? '#16a34a' : '#f59e0b' }};"></div>
      </div>
    </div>
    @unless($isReg)
    <div style="margin-top:12px;" onclick="event.stopPropagation()">
      <form method="POST" action="{{ route('mahasiswa.seminar.register', $seminar->id) }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">✓ Daftar sebagai Audiens</button>
      </form>
    </div>
    @endunless
  </div>
  @empty
  <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">
    <p>Belum ada seminar mahasiswa lain yang tersedia.</p>
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
