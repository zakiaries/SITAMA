@extends('layouts.mahasiswa')
@section('title', 'Seminar')
@php $title = 'Seminar'; @endphp
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

  @if(session('error'))
    <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
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
    <h2><x-icon name="cap" :size="20"/> Seminar Hasil Magang</h2>
    <p>Ajukan jadwal seminar Anda. Setelah disetujui Kaprodi, Anda akan mendapat QR daftar hadir untuk tamu.</p>
  </div>

  {{-- Syarat kelayakan pengajuan seminar --}}
  <div class="card" style="margin-bottom:16px;border-left:4px solid {{ $canSubmit ? 'var(--success-text)' : 'var(--warning)' }};">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
      <div style="font-size:13px;font-weight:700;color:var(--text);">Syarat Pengajuan Seminar</div>
      @if($canSubmit)
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Semua syarat terpenuhi</span>
      @else
        <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum lengkap</span>
      @endif
    </div>
    @foreach($requirements as $req)
    <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border);' : '' }}">
      <div style="flex-shrink:0;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
        background:{{ $req['met'] ? 'var(--success-bg)' : 'var(--danger-bg)' }};color:{{ $req['met'] ? 'var(--success-text)' : 'var(--danger)' }};">
        @if($req['met'])<x-icon name="check" :size="12"/>@else<x-icon name="x" :size="12"/>@endif
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
  @php
    $stMap = [
      'pending'   => ['Menunggu ACC Kaprodi', 'var(--warn-bg)', 'var(--warn-text)'],
      'scheduled' => ['Disetujui', 'var(--success-bg)', 'var(--success-text)'],
      'rejected'  => ['Ditolak', 'var(--danger-bg)', 'var(--danger)'],
      'completed' => ['Selesai', 'var(--success-bg)', 'var(--success-text)'],
      'cancelled' => ['Dibatalkan', 'var(--danger-bg)', 'var(--danger)'],
    ];
    $st = $stMap[$seminar->status] ?? [ucfirst($seminar->status), 'var(--blue-tint)', 'var(--primary)'];
    $canEdit = in_array($seminar->status, ['pending', 'rejected'], true);
  @endphp
  <div class="seminar-card">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">{{ $seminar->program }}</div>
      </div>
      <span class="badge" style="background:{{ $st[1] }};color:{{ $st[2] }};">{{ $st[0] }}</span>
    </div>
    <div class="sem-meta">
      @if($seminar->date)<div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>@endif
      @if($seminar->time)<div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>@endif
      @if($seminar->location)<div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>@endif
    </div>

    @if($seminar->status === 'rejected' && $seminar->rejection_reason)
      <div style="margin-top:10px;background:var(--danger-bg);border:1px solid #F0C4BE;border-radius:8px;padding:10px 12px;font-size:12px;color:var(--danger);">
        <strong>Alasan penolakan:</strong> {{ $seminar->rejection_reason }}<br>
        <span style="color:var(--text-muted);">Silakan ubah jadwal lalu ajukan ulang.</span>
      </div>
    @endif

    @if($seminar->status === 'scheduled')
      @php $guests = $seminar->attendances->count(); $min = \App\Models\Seminar::MIN_GUESTS; $met = $guests >= $min; @endphp
      <div style="margin-top:10px;">
        <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;">
          <span style="color:var(--text-muted);">Tamu mengisi berita acara</span>
          <span style="font-weight:700;color:{{ $met ? 'var(--success-text)' : 'var(--warn-text)' }};">{{ $guests }}/{{ $min }} @if($met)<x-icon name="check" :size="11"/>@endif</span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:20px;overflow:hidden;">
          <div style="height:100%;width:{{ min(100, $guests / $min * 100) }}%;background:{{ $met ? 'var(--success-text)' : 'var(--warning)' }};"></div>
        </div>
      </div>
    @endif

    <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
      @if($seminar->status === 'scheduled')
        <a href="{{ route('mahasiswa.seminar.detail', $seminar->id) }}" class="btn btn-primary btn-sm"><x-icon name="qr" :size="14"/> QR & Berita Acara</a>
      @endif
      @if($canEdit)
        <button type="button" class="btn btn-outline btn-sm"
          onclick="openEditSeminar({{ $seminar->id }}, @js($seminar->title), '{{ $seminar->date?->format('Y-m-d') }}', @js($seminar->time), @js($seminar->location), @js($seminar->description))"><x-icon name="pencil" :size="14"/> {{ $seminar->status === 'rejected' ? 'Ubah & Ajukan Ulang' : 'Edit' }}</button>
        <form method="POST" action="{{ route('mahasiswa.seminar.destroy', $seminar->id) }}"
              data-confirm="Batalkan pengajuan seminar ini?" data-confirm-danger>
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"><x-icon name="trash" :size="14"/> Batalkan</button>
        </form>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px;">
    <p>Anda belum mengajukan jadwal seminar. Klik "Ajukan Jadwal Seminar" untuk menentukan tanggalnya.</p>
  </div>
  @endforelse

  {{-- Seminar umum / wajib yang tersedia --}}
  @if($seminars->count())
  <div style="font-size:13px;font-weight:700;color:var(--primary);margin:20px 0 12px;">
    Seminar Tersedia ({{ $seminars->count() }})
  </div>
  @foreach($seminars as $seminar)
  <div class="seminar-card" onclick="window.location='{{ route('mahasiswa.seminar.detail', $seminar->id) }}'">
    <div class="sem-row">
      <div>
        <div class="sem-title">{{ $seminar->title }}</div>
        <div class="sem-prog">{{ $seminar->program }}</div>
      </div>
      <span class="badge jadwal">Terjadwal</span>
    </div>
    <div class="sem-meta">
      @if($seminar->date)<div class="sem-mi"><label>Tanggal</label><span>{{ $seminar->date->format('d M Y') }}</span></div>@endif
      @if($seminar->time)<div class="sem-mi"><label>Waktu</label><span>{{ $seminar->time }}</span></div>@endif
      @if($seminar->location)<div class="sem-mi"><label>Ruang</label><span>{{ $seminar->location }}</span></div>@endif
    </div>
    <div class="sem-tap">Tap untuk detail »</div>
  </div>
  @endforeach
  @endif

  {{-- Modal Ajukan Jadwal Seminar --}}
  <div class="modal-overlay" id="modal-seminar" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">Ajukan Jadwal Seminar</div>
        <button class="modal-close" onclick="document.getElementById('modal-seminar').classList.remove('open')"><x-icon name="x" :size="14"/></button>
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
        <div class="modal-title">Ubah Jadwal Seminar</div>
        <button class="modal-close" onclick="document.getElementById('modal-edit-seminar').classList.remove('open')"><x-icon name="x" :size="14"/></button>
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
          <button type="submit" class="btn btn-primary">Simpan & Ajukan Ulang</button>
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
