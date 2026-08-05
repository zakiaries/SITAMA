@extends('layouts.kaprodi')
@section('title', 'FAQ Chatbot')
@php $title = 'FAQ Chatbot'; @endphp
@section('content')

<x-form-errors/>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid #A7E8CF;color:var(--success-text);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
@endif

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
  {{-- Judulnya sudah tercetak di header atas; keterangannya yang tetap berguna. --}}
  <div>
    <div style="color:var(--text-secondary);font-size:13px;">
      Basis pengetahuan yang dipakai chatbot mahasiswa. Perubahan langsung melatih ulang model TF-IDF.
    </div>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="{{ route('kaprodi.chatbot.logs') }}" class="btn btn-outline btn-sm"><x-icon name="chart" :size="14"/> Riwayat & Statistik</a>
    <a href="{{ route('kaprodi.chatbot.create') }}" class="btn btn-primary btn-sm"><x-icon name="pencil" :size="14"/> Tambah FAQ</a>
  </div>
</div>

{{-- Tab filter --}}
<div style="display:flex;gap:8px;margin-bottom:18px;border-bottom:1.5px solid var(--border);">
  @foreach(['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] as $key => $label)
  <a href="{{ route('kaprodi.chatbot.index', ['status' => $key]) }}"
     style="padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid {{ $status === $key ? 'var(--primary)' : 'transparent' }};
            font-size:13px;font-weight:700;color:{{ $status === $key ? 'var(--primary)' : 'var(--text-muted)' }};
            text-decoration:none;display:flex;align-items:center;gap:8px;">
    {{ $label }}
    <span style="background:{{ $status === $key ? 'var(--primary-light)' : 'var(--warm)' }};color:{{ $status === $key ? 'var(--primary-text)' : 'var(--text-muted)' }};font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">
      {{ $counts[$key] }}
    </span>
  </a>
  @endforeach
</div>

@forelse($items as $item)
<div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:16px 18px;margin-bottom:12px;{{ $item->is_active ? '' : 'opacity:.6;' }}">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <div style="flex:1;min-width:240px;">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
        <span class="badge info" style="font-size:11px;">{{ $item->kategori }}</span>
        @unless($item->is_active)
          <span class="badge" style="background:var(--warm-2);color:var(--text-secondary);font-size:11px;">Nonaktif</span>
        @endunless
      </div>
      <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $item->pertanyaan }}</div>
      <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">{{ $item->jawaban }}</div>
      <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">
        <span style="font-weight:700;">Kata kunci:</span> {{ $item->kata_kunci }}
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0;">
      <a href="{{ route('kaprodi.chatbot.edit', $item) }}" class="btn btn-outline btn-sm"><x-icon name="pencil" :size="13"/> Edit</a>
      <form method="POST" action="{{ route('kaprodi.chatbot.toggle', $item) }}">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
      </form>
      <form method="POST" action="{{ route('kaprodi.chatbot.destroy', $item) }}"
            data-confirm="Hapus FAQ &quot;{{ $item->pertanyaan }}&quot;?">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm" style="background:var(--danger-bg);color:var(--danger);border:none;"><x-icon name="trash" :size="13"/></button>
      </form>
    </div>
  </div>
</div>
@empty
<div style="text-align:center;padding:48px 24px;color:var(--text-muted);">
  <div style="margin-bottom:12px;display:flex;justify-content:center;"><x-icon name="message" :size="40"/></div>
  <p style="font-weight:600;color:var(--text);">Belum ada entri FAQ.</p>
  <a href="{{ route('kaprodi.chatbot.create') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Tambah FAQ pertama</a>
</div>
@endforelse

@endsection
