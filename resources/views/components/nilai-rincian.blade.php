{{--
  Butir penilaian di bawah sebuah komponen, beserta nilainya masing-masing.

  Dipakai bersama oleh portal mahasiswa, dosen, dan Kaprodi supaya ketiganya
  memperlihatkan dasar angka yang sama. Mahasiswa dan Kaprodi meminta ini saat
  uji coba: mereka hanya melihat "Proposal 8" dan "Laporan 8" tanpa tahu apa
  saja yang dinilai di dalamnya.

  Sub-butir hanya ditampilkan bila komponennya memang punya LEBIH DARI SATU.
  Rubrik pembimbing industri menyimpan satu butir per komponen yang isinya
  keterangan, bukan kriteria terpisah — menampilkannya akan terbaca sebagai
  nilai kedua yang sebetulnya tak ada. Aturan "lebih dari satu" dipilih supaya
  tetap benar bila rubriknya berubah, tanpa perlu menyebut peran mana pun.
--}}
@props(['komponen'])

@php $butir = $komponen['details'] ?? []; @endphp

@if(count($butir) > 1)
  <div class="nilai-rincian">
    @foreach($butir as $i => $b)
      <div class="nilai-rincian-row">
        <div class="nrr-nama">{{ $i + 1 }}. {{ $b['name'] }}</div>
        <div class="nrr-nilai {{ $b['score'] === null ? 'kosong' : '' }}">{{ $b['score'] ?? '–' }}</div>
      </div>
    @endforeach
  </div>
@endif
