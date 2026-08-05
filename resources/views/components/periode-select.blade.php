@props(['periode', 'list', 'tanpa' => 0])

{{--
  Dropdown periode magang, dipakai bersama dashboard & Data Mahasiswa.

  Urutannya sengaja meniru Simadu Polines — terbaru di atas, "2026/2027 Gasal" —
  supaya Kaprodi membaca bentuk yang sama dengan yang ia lihat sehari-hari.

  "Tanpa periode" hanya muncul bila memang ada isinya. Ia penting: mahasiswa yang
  lahir dari jalur yang belum menetapkan periode akan berkumpul di situ, dan
  tanpa pilihan ini mereka lenyap dari pandangan Kaprodi tanpa jejak.
--}}
<select name="periode" onchange="this.form.submit()">
  @foreach($list as $p)
    <option value="{{ $p->id }}" {{ (string) $periode === (string) $p->id ? 'selected' : '' }}>
      {{ $p->label }}{{ $p->is_active ? ' — berjalan' : '' }}
    </option>
  @endforeach

  @if($tanpa > 0)
    <option value="tanpa" {{ $periode === 'tanpa' ? 'selected' : '' }}>
      Tanpa periode ({{ $tanpa }})
    </option>
  @endif

  <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua periode</option>
</select>

@if($list->isEmpty())
  <span style="font-size:12px;color:var(--muted);">Belum ada periode magang.</span>
@endif
