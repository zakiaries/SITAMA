{{--
  Tombol mata show/hide untuk field password.

  Pakai: bungkus input dengan .pw-wrap lalu taruh komponen ini SETELAH input.
    <div class="pw-wrap">
      <input class="form-input" type="password" name="password" required>
      <x-password-toggle />
    </div>

  Sengaja TANPA id: mencari input di dalam .pw-wrap terdekat, jadi boleh
  dipakai berkali-kali di satu halaman (password + konfirmasi). Logikanya
  inline supaya halaman publik yang tak memuat file JS aplikasi tetap jalan.
--}}
<button type="button" class="pw-eye" title="Tampilkan / sembunyikan kata sandi"
        aria-label="Tampilkan atau sembunyikan kata sandi"
        onclick="var w=this.closest('.pw-wrap'),i=w.querySelector('input'),s=w.querySelector('.pw-eye-show'),h=w.querySelector('.pw-eye-hide'),p=i.type==='password';i.type=p?'text':'password';s.style.display=p?'none':'';h.style.display=p?'':'none';">
  <svg class="pw-eye-show" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
  </svg>
  <svg class="pw-eye-hide" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
    <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
    <line x1="1" y1="1" x2="23" y2="23"/>
  </svg>
</button>
