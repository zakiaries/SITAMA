@extends('layouts.mahasiswa')
@section('title', 'Magang Saya')
@php $title = 'Magang Saya'; @endphp
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

  {{-- Magang Aktif --}}
  @if($internship)
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Magang Aktif</div>
      @if($internship->is_finished)
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Selesai</span>
      @else
        <span style="background:var(--blue-tint);color:var(--primary);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Aktif</span>
      @endif
    </div>
    <div class="info-row"><div class="info-key">Perusahaan</div><div class="info-val accent">{{ $internship->company->name ?? '-' }}</div></div>
    <div class="info-row"><div class="info-key">Posisi</div><div class="info-val">{{ $internship->position }}</div></div>
    <div class="info-row">
      <div class="info-key">Dosen Pembimbing</div>
      <div class="info-val">{{ $internship->lecturer && $internship->lecturer->user ? $internship->lecturer->user->name : 'Belum ditugaskan' }}</div>
    </div>
    <div class="info-row">
      <div class="info-key">Pembimbing Industri</div>
      <div class="info-val">{{ $internship->lecturerIndustry && $internship->lecturerIndustry->user ? $internship->lecturerIndustry->user->name : 'Belum ditugaskan' }}</div>
    </div>
  </div>

  {{-- Sertifikat Magang --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Sertifikat Magang</div>
      @if($internship->certificate_path)
        <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;"><x-icon name="check" :size="12"/> Sudah diunggah</span>
      @else
        <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Belum ada</span>
      @endif
    </div>

    @if($internship->certificate_path)
      <div class="file-badge" style="margin-bottom:14px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <a href="{{ Storage::url($internship->certificate_path) }}" target="_blank">Lihat Sertifikat</a>
      </div>
    @endif

    @if($internship->terkunciUntukMahasiswa())
      {{-- Pengajuan selesai sudah dikirim: sertifikat yang diperiksa Kaprodi
           tidak boleh berubah lagi. Formulirnya dihilangkan, bukan cuma ditolak. --}}
      <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:flex-start;gap:6px;">
        <span style="margin-top:1px;display:inline-flex;"><x-icon name="lock" :size="13"/></span>
        <span>{{ $internship->alasanTerkunci() }}</span>
      </div>
    @else
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">
        Unggah sertifikat magang dari perusahaan (PDF/JPG/PNG, maks 10 MB).
        {{ $internship->certificate_path ? 'Mengunggah ulang akan menggantikan file lama.' : '' }}
      </div>

      <form method="POST" action="{{ route('mahasiswa.magang-saya.sertifikat') }}" enctype="multipart/form-data"
            style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        @csrf
        <input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required>
        <button type="submit" class="btn btn-primary btn-sm">
          {{ $internship->certificate_path ? 'Ganti Sertifikat' : 'Unggah Sertifikat' }}
        </button>
      </form>
      @error('certificate')
        <div style="color:var(--danger);font-size:12px;margin-top:8px;">{{ $message }}</div>
      @enderror
    @endif
  </div>

  {{-- Ajukan Selesai Magang --}}
  @if(!$internship->is_finished)
  <div class="card" style="margin-bottom:20px;border-left:4px solid {{ $canRequestFinish ? 'var(--success)' : 'var(--text-muted)' }};">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Selesai Magang</div>
      @if($internship->finish_requested)
        <span style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;font-weight:600;padding:2px 10px;border-radius:20px;">Menunggu ACC Kaprodi</span>
      @endif
    </div>

    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
      @foreach($finishChecklist as $item)
      <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;">
        @if($item['met'])
          <span style="color:var(--success-text);margin-top:2px;display:inline-flex;"><x-icon name="check" :size="15"/></span>
          <span style="color:var(--text);">{{ $item['label'] }}</span>
        @else
          <span style="color:var(--danger);margin-top:2px;display:inline-flex;"><x-icon name="x" :size="15"/></span>
          <span style="color:var(--text-muted);">{{ $item['label'] }}
            <span style="font-size:11.5px;display:block;color:var(--danger);">{{ $item['hint'] }}</span>
          </span>
        @endif
      </div>
      @endforeach
    </div>

    @if(!$internship->finish_requested)
      @if($canRequestFinish)
        <form method="POST" action="{{ route('mahasiswa.magang-saya.ajukan-selesai') }}">
          @csrf
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            Ajukan Selesai Magang
          </button>
        </form>
      @else
        <button disabled class="btn" style="width:100%;justify-content:center;background:var(--warm-2);color:var(--text-muted);cursor:not-allowed;">
          Ajukan Selesai Magang
        </button>
        <p style="font-size:12px;color:var(--text-muted);margin-top:8px;text-align:center;">Lengkapi semua syarat di atas terlebih dahulu.</p>
      @endif
    @else
      <div style="background:var(--warn-bg);border:1px solid #F3D9A0;border-radius:8px;padding:10px 14px;font-size:12.5px;color:var(--warn-text);">
        Pengajuan selesai magang sudah dikirim ke Kaprodi. Kaprodi akan memeriksa dan memberikan ACC.
        Selama menunggu, sertifikat, log book, dan bimbingan terkunci agar yang diperiksa
        sama dengan yang kamu ajukan.
      </div>

      {{-- Jalan keluar: tanpa ini mahasiswa yang salah unggah akan terjebak —
           tak bisa memperbaiki apa pun, tak bisa menarik pengajuannya. --}}
      <form method="POST" action="{{ route('mahasiswa.magang-saya.batal-selesai') }}" style="margin-top:10px;">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;"
                onclick="return confirm('Batalkan pengajuan selesai magang? Kamu bisa mengajukannya lagi setelah selesai memperbaiki.')">
          Batalkan Pengajuan
        </button>
      </form>
    @endif
  </div>
  @endif

  {{-- Log Book Terbaru --}}
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header" style="margin-bottom:12px;">
      <div class="card-title">Log Book Terbaru</div>
      <a href="{{ route('mahasiswa.logbook') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
    </div>
    @forelse($logBooks as $lb)
    <div style="padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div style="font-weight:700;font-size:13px;color:var(--text);">{{ $lb->title }}</div>
        <div style="font-size:11px;color:var(--text-muted);">{{ $lb->date->format('d M Y') }}</div>
      </div>
      @if($lb->lecturer_note)
      <div class="field-group note-blue" style="margin-top:8px;">
        <div class="field-label">Catatan Dosen Pembimbing (Kampus)</div>
        <div class="field-value">{{ $lb->lecturer_note }}</div>
      </div>
      @endif
      @if($lb->industry_note)
      <div class="field-group note-green" style="margin-top:8px;">
        <div class="field-label">Catatan Pembimbing Industri</div>
        <div class="field-value">{{ $lb->industry_note }}</div>
      </div>
      @endif
    </div>
    @empty
      <p style="color:var(--text-muted);font-size:13px;padding:8px 0;">Belum ada log book.</p>
    @endforelse
  </div>
  @endif

  {{-- Belum punya magang aktif: arahkan ke Ajukan Magang --}}
  @unless($internship)
  <div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
    <div style="margin-bottom:12px;color:var(--text-muted);display:flex;justify-content:center;"><x-icon name="clipboard" :size="40"/></div>
    <p style="font-size:14px;margin-bottom:16px;">Kamu belum memiliki magang aktif. Ajukan magangmu untuk mulai memantau kegiatan di sini.</p>
    <a href="{{ route('mahasiswa.ajukan-magang') }}" class="btn btn-primary">Ajukan Magang <x-icon name="arrow-right" :size="14"/></a>
  </div>
  @endunless

@endsection
