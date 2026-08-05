{{--
  Daftar pesan validasi di atas formulir.

  Dibuat karena halaman profil keempat peran sama sekali tak menampilkannya:
  sandi yang konfirmasinya tak cocok memang DITOLAK server dan tak pernah
  tersimpan, tapi pengguna dikembalikan ke halaman yang sama tanpa satu pun
  penanda — sehingga tampak seolah diterima. Penolakan yang tak terlihat lebih
  buruk daripada penolakan yang kasar: pengguna pergi dengan yakin sandinya
  sudah berganti, lalu tak bisa masuk.

  Pesan per-field (@error) tetap dipakai di tempat yang sudah punya; komponen ini
  untuk halaman yang belum punya apa-apa.
--}}
@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid #F0C4BE;color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;">
      @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
    </ul>
  </div>
@endif
