<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — SIMAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
  :root{
    --primary:#0061FF;--primary-dark:#0048BD;
    --text:#1E1919;--text-secondary:#637282;--text-muted:#9EA9B2;
    --bg:#FFFFFF;--warm:#F7F5F2;--warm-2:#F0EDE8;
    --border:#D8D6D3;--border-subtle:#EDECEA;--blue-tint:#EAF1FF;
    --ease:cubic-bezier(.22,.61,.36,1);
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:48px 20px;
    font-family:'Archivo','Segoe UI',sans-serif;color:var(--text);
    background:radial-gradient(1200px 600px at 50% -10%,#EAF1FF,#F2F4FF 45%,#EEF1F8);-webkit-font-smoothing:antialiased;}
  a{text-decoration:none;}
  @keyframes rise{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:none;}}
  @keyframes bgfade{from{opacity:0;}to{opacity:1;}}
  .an{opacity:0;animation:rise .55s var(--ease) forwards;}

  .shell{position:relative;z-index:1;width:100%;max-width:1000px;display:flex;border-radius:28px;background:var(--bg);box-shadow:0 30px 80px rgba(30,40,90,.18);}
  .shell::before{content:'';position:absolute;width:120px;height:120px;border-radius:50%;background:var(--primary);top:-34px;left:-34px;z-index:0;opacity:.9;}
  .shell::after{content:'';position:absolute;width:120px;height:120px;border-radius:50%;background:#E7ECFF;bottom:-34px;right:-34px;z-index:0;}

  .pane-left{position:relative;z-index:2;flex:1.25;background:var(--bg);border-radius:28px 0 0 28px;padding:42px 44px;}
  .brand{display:flex;align-items:center;gap:10px;margin-bottom:18px;}
  .brand .logo{width:38px;height:38px;border-radius:50%;object-fit:contain;}
  .brand .bn{font-size:15px;font-weight:800;letter-spacing:1px;}
  .f-title{font-size:26px;font-weight:800;letter-spacing:-0.01em;}
  .f-sub{font-size:13px;color:var(--text-secondary);margin-top:5px;margin-bottom:20px;}
  .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;font-weight:500;background:#FBEAE8;border:1px solid #F0C4BE;color:#C0392B;}
  .seclabel{font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin:18px 0 12px;display:flex;align-items:center;gap:8px;}
  .seclabel::after{content:'';flex:1;height:1px;background:var(--border-subtle);}
  .row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  .field{margin-bottom:13px;}
  .field label{display:block;font-size:12.5px;font-weight:600;margin-bottom:6px;}
  .iwrap{position:relative;}
  .eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:5px;display:flex;border-radius:8px;}
  .eye:hover{color:var(--primary);}
  .field input{width:100%;height:46px;padding:0 14px;border:1.5px solid transparent;border-radius:11px;font-size:13.5px;font-family:inherit;color:var(--text);background:var(--warm);outline:none;transition:border-color .18s var(--ease),box-shadow .18s var(--ease),background .18s;}
  .field .iwrap input{padding-right:40px;}
  .field input::placeholder{color:var(--text-muted);}
  .field input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 4px rgba(0,97,255,.12);}
  /* Hide browser's built-in password reveal/clear (Edge/IE) so it doesn't duplicate our eye. */
  .field input::-ms-reveal,.field input::-ms-clear{display:none;}
  .btn{width:100%;height:50px;margin-top:14px;background:var(--primary);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;letter-spacing:-0.01em;box-shadow:0 12px 24px rgba(0,97,255,.28);transition:background .15s,transform .1s var(--ease),box-shadow .15s;}
  .btn:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 16px 30px rgba(0,97,255,.34);}
  .btn:active{transform:scale(.99);}
  .login-link{text-align:center;margin-top:16px;font-size:13px;color:var(--text-secondary);}
  .login-link a{color:var(--primary);font-weight:700;}
  .login-link a:hover{text-decoration:underline;}

  .pane-right{position:relative;z-index:2;flex:1;border-radius:0 28px 28px 0;overflow:hidden;
    background:linear-gradient(150deg,#0061FF 0%,#2A78FF 55%,#0048BD 100%);
    display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 36px;gap:26px;}
  .waves{position:absolute;inset:0;opacity:.5;}
  .r-copy{position:relative;z-index:2;text-align:center;color:#fff;}
  .r-copy h3{font-size:22px;font-weight:800;letter-spacing:-0.02em;}
  .r-copy p{font-size:13px;color:rgba(255,255,255,.82);margin-top:8px;line-height:1.55;max-width:280px;}
  .glass{position:relative;z-index:2;width:280px;max-width:90%;border-radius:22px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);box-shadow:0 20px 50px rgba(0,0,0,.2);padding:24px 20px;}
  .glass img{display:block;width:100%;height:auto;}
  .badge{position:absolute;z-index:3;left:30px;top:calc(50% + 60px);width:60px;height:60px;border-radius:18px;background:#fff;box-shadow:0 14px 30px rgba(0,0,0,.18);display:flex;align-items:center;justify-content:center;color:var(--primary);}
  .badge2{position:absolute;z-index:3;right:30px;top:calc(50% - 120px);width:52px;height:52px;border-radius:16px;background:#fff;box-shadow:0 14px 30px rgba(0,0,0,.18);display:flex;align-items:center;justify-content:center;color:#0AC27D;}

  .bg{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;opacity:0;animation:bgfade .9s ease .1s forwards;}
  .bg .orb{position:absolute;border-radius:50%;filter:blur(46px);opacity:.5;}
  .bg .o1{width:360px;height:360px;background:#9DB8FF;left:-90px;top:-70px;}
  .bg .o2{width:320px;height:320px;background:#CBD8FF;right:-80px;bottom:-60px;}
  .bg .o3{width:200px;height:200px;background:#BFE0FF;right:14%;top:5%;}
  .bg .ring{position:absolute;border-radius:50%;border:2px solid rgba(0,97,255,.12);}
  .bg .r1{width:120px;height:120px;left:7%;bottom:14%;}
  .bg .r2{width:60px;height:60px;right:10%;top:20%;}
  .bg .dots{position:absolute;inset:0;background-image:radial-gradient(rgba(0,97,255,.12) 1.4px,transparent 1.4px);background-size:26px 26px;opacity:.55;
    -webkit-mask-image:radial-gradient(circle at 50% 46%,transparent 380px,#000 640px);mask-image:radial-gradient(circle at 50% 46%,transparent 380px,#000 640px);}
  .topmini{position:fixed;top:22px;left:0;right:0;z-index:1;display:flex;align-items:center;justify-content:space-between;padding:0 32px;pointer-events:none;}
  .topmini .tm-brand{font-weight:800;letter-spacing:1.5px;font-size:15px;pointer-events:auto;}
  .topmini .tm-help{font-size:13px;font-weight:600;color:var(--text-secondary);pointer-events:auto;}
  .topmini .tm-help:hover{color:var(--primary);}

  @media (max-width:880px){
    .pane-right{display:none;}
    .pane-left{border-radius:28px;padding:34px 26px;}
    .topmini .tm-help{display:none;}
  }
  @media (max-width:520px){.row-2{grid-template-columns:1fr;}}
  @media (prefers-reduced-motion: reduce){.an{animation:none!important;opacity:1!important;}.bg{animation:none!important;opacity:1!important;}}
  </style>
</head>
<body>

  <div class="bg">
    <span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span>
    <span class="ring r1"></span><span class="ring r2"></span><span class="dots"></span>
  </div>
  <div class="topmini">
    <div class="tm-brand an" style="animation-delay:.08s">SIMAMA</div>
    <a class="tm-help an" style="animation-delay:.12s" href="#">Butuh bantuan?</a>
  </div>

  <div class="shell">
    {{-- LEFT FORM --}}
    <div class="pane-left">
      <div class="brand an" style="animation-delay:.05s">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="SIMAMA logo">
        <span class="bn">SIMAMA</span>
      </div>
      <div class="f-title an" style="animation-delay:.1s">Pendaftaran Mahasiswa</div>
      <div class="f-sub an" style="animation-delay:.14s">Isi data dengan benar. Akun diverifikasi Kaprodi sebelum aktif.</div>

      @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="an" style="animation-delay:.2s">
          <div class="seclabel">Data Akun</div>
          <div class="field">
            <label>Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Sesuai KTM" required>
          </div>
          <div class="row-2">
            <div class="field"><label>NIM</label><input type="text" name="username" value="{{ old('username') }}" placeholder="Contoh: 3.34.23.2.12" pattern="\d+\.\d+\.\d+\.\d+\.\d+" title="Format NIM: 5 kelompok angka dipisah titik, mis. 3.34.23.2.12" required></div>
            <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="email@kampus.ac.id" required></div>
          </div>
          <div class="row-2">
            <div class="field"><label>Password</label>
              <div class="iwrap"><input id="p1" type="password" name="password" placeholder="Min. 8 karakter" required>
                <button type="button" class="eye" onclick="tg('p1','e1s','e1h')"><svg id="e1s" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><svg id="e1h" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>
              </div>
            </div>
            <div class="field"><label>Konfirmasi Password</label>
              <div class="iwrap"><input id="p2" type="password" name="password_confirmation" placeholder="Ulangi password" required>
                <button type="button" class="eye" onclick="tg('p2','e2s','e2h')"><svg id="e2s" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><svg id="e2h" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>
              </div>
            </div>
          </div>
        </div>

        <div class="an" style="animation-delay:.28s">
          <div class="seclabel">Data Akademik</div>
          <div class="row-2">
            <div class="field"><label>Program Studi</label><input type="text" name="study_program" value="{{ old('study_program') }}" placeholder="cth: Teknik Informatika" required></div>
            <div class="field"><label>Jurusan</label><input type="text" name="major" value="{{ old('major') }}" placeholder="cth: TI" required></div>
          </div>
          <div class="row-2">
            <div class="field"><label>Kelas</label><input type="text" name="the_class" value="{{ old('the_class') }}" placeholder="cth: TI-6A" required></div>
            <div class="field"><label>Tahun Akademik</label><input type="text" name="academic_year" value="{{ old('academic_year') }}" placeholder="cth: 2024/2025" required></div>
          </div>
        </div>

        {{-- Perangkap bot (honeypot). Disembunyikan dari layar, dilewati keyboard
             (tabindex -1), dan diabaikan pembaca layar (aria-hidden), jadi manusia
             tak pernah mengisinya. Pengisi otomatis juga tak mengenali namanya.
             Kalau terisi, pendaftaran ditolak diam-diam. Lebih murah daripada
             CAPTCHA: tanpa pihak ketiga, tanpa kunci API, dan tanpa gesekan. --}}
        <div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;"
             aria-hidden="true">
          <label for="catatan_tambahan">Jangan diisi</label>
          <input type="text" id="catatan_tambahan" name="catatan_tambahan"
                 tabindex="-1" autocomplete="off">
        </div>

        <button type="submit" class="btn an" style="animation-delay:.36s">Daftar Sekarang</button>
      </form>

      <div class="login-link an" style="animation-delay:.42s">Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></div>
    </div>

    {{-- RIGHT VISUAL --}}
    <div class="pane-right">
      <svg class="waves" viewBox="0 0 400 640" preserveAspectRatio="xMidYMid slice" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="2">
        <path d="M-40 140 C80 80 140 230 260 180 S440 100 470 200"/>
        <path d="M-40 280 C80 220 140 370 260 320 S440 240 470 340"/>
        <path d="M-40 460 C80 400 140 550 260 500 S440 420 470 520"/>
      </svg>

      <div class="badge an" style="animation-delay:.5s"><svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div>
      <div class="badge2 an" style="animation-delay:.58s"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg></div>

      <div class="r-copy an" style="animation-delay:.3s">
        <h3>Gabung ke SIMAMA</h3>
        <p>Satu akun untuk logbook, bimbingan, seminar, dan nilai magangmu.</p>
      </div>
      <div class="glass an" style="animation-delay:.36s">
        <img src="{{ asset('images/login_vektor.png') }}" alt="Ilustrasi daftar">
      </div>
    </div>
  </div>

  <script>
    function tg(inp,s,h){
      var i=document.getElementById(inp),a=document.getElementById(s),b=document.getElementById(h);
      if(i.type==='password'){i.type='text';a.style.display='none';b.style.display='block';}
      else{i.type='password';a.style.display='block';b.style.display='none';}
    }
  </script>
</body>
</html>
