@extends('layouts.kaprodi')
@section('title', 'Riwayat Chatbot')
@php $title = 'Riwayat & Statistik Chatbot'; @endphp
@section('content')

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
  <div>
    <div class="page-title">Riwayat &amp; Statistik Chatbot</div>
    <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
      Pantau pertanyaan mahasiswa. Pertanyaan yang belum terjawab bisa jadi bahan menambah FAQ.
    </div>
  </div>
  <a href="{{ route('kaprodi.chatbot.index') }}" class="btn btn-outline btn-sm"><x-icon name="arrow-left" :size="14"/> Kelola FAQ</a>
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px;">
  @php
    $cards = [
      ['Total Pertanyaan', $stats['total'], 'var(--primary)'],
      ['Terjawab', $stats['answered'].' ('.$stats['rate'].'%)', 'var(--success-text)'],
      ['Belum Terjawab', $stats['unansweredNum'], 'var(--warn-text)'],
    ];
  @endphp
  @foreach($cards as [$label, $value, $color])
  <div class="card" style="padding:16px 18px;">
    <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;">{{ $label }}</div>
    <div style="font-size:24px;font-weight:800;color:{{ $color }};margin-top:6px;">{{ $value }}</div>
  </div>
  @endforeach
</div>

{{-- Pertanyaan belum terjawab (paling sering) --}}
@if($topUnanswered->isNotEmpty())
<div class="card" style="margin-bottom:22px;">
  <div class="card-title" style="margin-bottom:12px;">Pertanyaan Belum Terjawab (Tersering)</div>
  <div style="display:flex;flex-direction:column;gap:8px;">
    @foreach($topUnanswered as $row)
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;background:var(--warn-bg);border-radius:8px;">
      <div style="font-size:13px;color:var(--text);flex:1;">{{ $row->message }}</div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
        <span style="font-size:11px;color:var(--warn-text);font-weight:700;">{{ $row->cnt }}x</span>
        <a href="{{ route('kaprodi.chatbot.create') }}" class="btn btn-outline btn-sm" style="padding:4px 10px;">+ FAQ</a>
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- Filter --}}
<div style="display:flex;gap:8px;margin-bottom:14px;border-bottom:1.5px solid var(--border);">
  @foreach(['all' => 'Semua', 'answered' => 'Terjawab', 'unanswered' => 'Belum Terjawab'] as $key => $label)
  <a href="{{ route('kaprodi.chatbot.logs', ['filter' => $key]) }}"
     style="padding:10px 4px;margin-bottom:-1.5px;border-bottom:2.5px solid {{ $filter === $key ? 'var(--primary)' : 'transparent' }};
            font-size:13px;font-weight:700;color:{{ $filter === $key ? 'var(--primary)' : 'var(--text-muted)' }};text-decoration:none;">
    {{ $label }}
  </a>
  @endforeach
</div>

{{-- Tabel riwayat --}}
<div class="card" style="padding:0;overflow-x:auto;">
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="background:var(--warm);text-align:left;">
        <th style="padding:10px 14px;font-weight:700;color:var(--text-secondary);">Waktu</th>
        <th style="padding:10px 14px;font-weight:700;color:var(--text-secondary);">Penanya</th>
        <th style="padding:10px 14px;font-weight:700;color:var(--text-secondary);">Pertanyaan</th>
        <th style="padding:10px 14px;font-weight:700;color:var(--text-secondary);">Status</th>
        <th style="padding:10px 14px;font-weight:700;color:var(--text-secondary);text-align:right;">Skor</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
      <tr style="border-top:1px solid var(--border-subtle);">
        <td style="padding:10px 14px;color:var(--text-muted);white-space:nowrap;">{{ $log->created_at->format('d M H:i') }}</td>
        <td style="padding:10px 14px;color:var(--text);white-space:nowrap;">{{ $log->user->name ?? '—' }}</td>
        <td style="padding:10px 14px;color:var(--text);">{{ $log->message }}</td>
        <td style="padding:10px 14px;">
          @if($log->is_answered)
            <span class="badge baru" style="font-size:11px;">Terjawab</span>
          @else
            <span class="badge" style="background:var(--warn-bg);color:var(--warn-text);font-size:11px;">Belum</span>
          @endif
        </td>
        <td style="padding:10px 14px;text-align:right;color:var(--text-secondary);">{{ number_format($log->score, 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="5" style="padding:32px;text-align:center;color:var(--text-muted);">Belum ada percakapan.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($logs->hasPages())
  <div style="margin-top:16px;">{{ $logs->links() }}</div>
@endif

@endsection
