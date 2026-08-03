@extends('layouts.kaprodi')
@section('title', 'Lowongan Magang')
@php $title = 'Lowongan Magang'; @endphp
@section('content')

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

<div class="card" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
  <div>
    <div class="card-title" style="margin-bottom:2px;">Kelola Lowongan / Tempat Magang</div>
    <p style="font-size:12.5px;color:var(--text-muted);margin:0;">Perusahaan afiliasi Polines yang tampil ke mahasiswa. Menambah lowongan otomatis menandai perusahaannya berafiliasi.</p>
  </div>
  <a href="{{ route('kaprodi.lowongan.create') }}" class="btn btn-primary">+ Tambah Lowongan</a>
</div>

<form method="GET" action="{{ route('kaprodi.lowongan.index') }}" style="display:flex;gap:8px;margin-bottom:16px;max-width:460px;">
  <input type="text" name="q" value="{{ $q }}" placeholder="Cari posisi / perusahaan / lokasi…"
    style="flex:1;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
  <button type="submit" class="btn btn-outline">Cari</button>
</form>

@if($listings->isEmpty())
  <div class="card" style="text-align:center;padding:32px 16px;color:var(--text-muted);font-size:13px;">Belum ada lowongan. Klik "Tambah Lowongan" untuk memulai.</div>
@else
  <div class="card" style="padding:0;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg);text-align:left;">
          <th style="padding:11px 14px;">Posisi</th>
          <th style="padding:11px 14px;">Perusahaan</th>
          <th style="padding:11px 14px;">Lokasi</th>
          <th style="padding:11px 14px;">Kuota</th>
          <th style="padding:11px 14px;">Status</th>
          <th style="padding:11px 14px;text-align:right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($listings as $l)
          <tr style="border-top:1px solid var(--border);">
            <td style="padding:11px 14px;font-weight:600;">{{ $l->title }}</td>
            <td style="padding:11px 14px;">
              {{ $l->company_display_name }}
              @if(optional($l->company)->verification_status === 'verified')
                <span title="Berafiliasi" style="color:var(--success-text);">✔</span>
              @endif
            </td>
            <td style="padding:11px 14px;color:var(--text-muted);">{{ $l->location ?: '-' }}</td>
            {{-- Terisi dihitung dari mahasiswa yang SEDANG magang di perusahaan
                 ini; kuota penanda saja, pengajuan tak pernah ditolak karenanya. --}}
            <td style="padding:11px 14px;white-space:nowrap;">
              @if($l->punyaKuota())
                <span title="{{ $l->penjelasanKuota() }}">
                  {{ $l->jumlahTerisi() }} / {{ $l->quota }}
                  <span style="color:var(--text-muted);font-size:11px;">di perusahaan</span>
                </span>
                @if($l->penuh())
                  <span style="background:var(--warn-bg);color:var(--warn-text);font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:999px;margin-left:4px;">Penuh</span>
                @endif
              @else
                <span style="color:var(--text-muted);" title="Kuota tidak dibatasi">—</span>
              @endif
            </td>
            <td style="padding:11px 14px;">
              @if($l->status === 'active')
                <span style="background:var(--success-bg);color:var(--success-text);font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px;">Aktif</span>
              @else
                <span style="background:var(--bg);color:var(--text-muted);font-size:11px;font-weight:700;padding:3px 9px;border-radius:999px;border:1px solid var(--border);">Nonaktif</span>
              @endif
            </td>
            <td style="padding:11px 14px;text-align:right;white-space:nowrap;">
              <a href="{{ route('kaprodi.lowongan.edit', $l) }}" class="btn btn-outline btn-sm">Edit</a>
              <form method="POST" action="{{ route('kaprodi.lowongan.toggle', $l) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sm">{{ $l->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              </form>
              <form method="POST" action="{{ route('kaprodi.lowongan.destroy', $l) }}" style="display:inline;"
                data-confirm="Hapus lowongan {{ $l->title }}?" data-confirm-danger>
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm" style="color:var(--danger);border-color:var(--danger);">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

@endsection
