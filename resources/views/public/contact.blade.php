@extends('layouts.public')
@section('title','Hubungi Kami')
@section('content')
  <div class="hero an">
    <div class="eyebrow">Kontak</div>
    <h1>Hubungi Kami</h1>
    <p>Ada pertanyaan atau kendala? Hubungi kami lewat kontak resmi Politeknik Negeri Semarang di bawah ini.</p>
  </div>
  <div class="grid g2">
    <div>
      <div class="card an" style="margin-bottom:14px;">
        <h3 style="margin-bottom:18px;">Politeknik Negeri Semarang</h3>

        <div class="cinfo" style="margin-bottom:18px;">
          <div class="ic amber"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
          <div>
            <div class="lbl">Alamat</div>
            <div class="val" style="line-height:1.5;">
              Jl. Prof. H. Soedarto SH,<br>
              Tembalang, Semarang<br>
              Kode Pos 50275
            </div>
          </div>
        </div>

        <div class="cinfo" style="margin-bottom:18px;">
          <div class="ic green"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.8 19.8 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.8 19.8 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg></div>
          <div>
            <div class="lbl">Telepon</div>
            <div class="val" style="line-height:1.6;">
              <a href="tel:+62247473417">+62 24 7473417</a><br>
              <a href="tel:+62247499585">+62 24 7499585</a><br>
              <a href="tel:+62247499586">+62 24 7499586</a>
            </div>
          </div>
        </div>

        <div class="cinfo" style="margin-bottom:18px;">
          <div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></div>
          <div>
            <div class="lbl">Fax</div>
            <div class="val">+62 24 7472396</div>
          </div>
        </div>

        <div class="cinfo" style="margin-bottom:18px;">
          <div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></div>
          <div>
            <div class="lbl">Email</div>
            <div class="val"><a href="mailto:sekretariat@polines.ac.id">sekretariat@polines.ac.id</a></div>
          </div>
        </div>

        <div class="cinfo">
          <div class="ic green"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg></div>
          <div>
            <div class="lbl">Website</div>
            <div class="val"><a href="https://www.polines.ac.id" target="_blank" rel="noopener">www.polines.ac.id</a></div>
          </div>
        </div>
      </div>

      <div class="card an" style="animation-delay:.06s">
        <h3 style="margin-bottom:12px;">Jam Operasional</h3>
        <div class="hours-row"><span>Senin – Jumat</span><span>08.00 – 16.00</span></div>
        <div class="hours-row"><span>Sabtu</span><span>08.00 – 12.00</span></div>
        <div class="hours-row"><span>Minggu &amp; Libur</span><span>Tutup</span></div>
      </div>
    </div>

    {{-- Dulu di kolom ini ada form "Kirim Pesan" yang TIDAK berfungsi (tanpa tag
         form, tanpa action, tombol type=button) — mahasiswa mengisinya lalu tak
         terjadi apa pun. Diganti jalur bantuan yang benar-benar bekerja. --}}
    <div>
      <div class="card an" style="animation-delay:.1s;margin-bottom:14px;">
        <h3 style="margin-bottom:10px;">Kendala di SIMAMA?</h3>
        <p style="font-size:14px;color:var(--text-secondary);line-height:1.65;margin-bottom:16px;">
          Untuk hal yang berkaitan langsung dengan aplikasi ini — akun belum disetujui, dosen
          pembimbing belum ditetapkan, pengajuan magang, logbook, bimbingan, atau nilai — jalur
          tercepat adalah menghubungi <strong>Kaprodi atau admin program studi</strong>, karena
          merekalah yang mengelola data di SIMAMA.
        </p>
        <p style="font-size:14px;color:var(--text-secondary);line-height:1.65;margin-bottom:18px;">
          Sebagian besar pertanyaan yang sering muncul sudah dijawab di halaman Bantuan.
        </p>
        <a class="btn btn-primary" href="{{ route('bantuan') }}" style="justify-content:center;height:46px;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Buka Halaman Bantuan
        </a>
      </div>

      <div class="card an" style="animation-delay:.14s">
        <h3 style="margin-bottom:10px;">Lupa kata sandi?</h3>
        <p style="font-size:14px;color:var(--text-secondary);line-height:1.65;margin-bottom:18px;">
          Kamu bisa mengatur ulang sendiri. Tautan reset dikirim ke email yang terdaftar di akunmu.
        </p>
        <a class="btn btn-outline" href="{{ route('password.request') }}" style="justify-content:center;height:46px;">
          Reset Kata Sandi
        </a>
      </div>
    </div>
  </div>
@endsection
