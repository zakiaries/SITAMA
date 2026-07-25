<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIMAMA — @yield('title','Informasi')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --primary:#0061FF;--primary-dark:#0048BD;
  --text:#1E1919;--text-secondary:#637282;--text-muted:#9EA9B2;
  --bg:#FFFFFF;--warm:#F7F5F2;--warm-2:#F0EDE8;
  --border:#D8D6D3;--border-subtle:#EDECEA;--blue-tint:#EAF1FF;
  --success:#0AC27D;--success-bg:#E0F7EF;--warning-text:#B9791A;--warning-bg:#FDF1DD;
  --ease:cubic-bezier(.22,.61,.36,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Archivo','Segoe UI',sans-serif;color:var(--text);
  background:radial-gradient(1200px 600px at 50% -10%,#EAF1FF,#F2F4FF 45%,#EEF1F8);
  min-height:100vh;-webkit-font-smoothing:antialiased;padding:40px 20px 70px;}
a{text-decoration:none;color:inherit;}
@keyframes rise{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:none;}}
@keyframes bgfade{from{opacity:0;}to{opacity:1;}}
.an{opacity:0;animation:rise .5s var(--ease) forwards;}
.bg{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;opacity:0;animation:bgfade .9s ease .1s forwards;}
.bg .orb{position:absolute;border-radius:50%;filter:blur(52px);opacity:.55;}
.bg .o1{width:360px;height:360px;background:#9DB8FF;left:-90px;top:-70px;}
.bg .o2{width:320px;height:320px;background:#CBD8FF;right:-80px;bottom:-60px;}
.bg .o3{width:220px;height:220px;background:#BFE0FF;right:11%;top:3%;}
.bg .o4{width:260px;height:260px;background:#AFC4FF;left:1%;top:36%;}
.bg .o5{width:240px;height:240px;background:#CBD8FF;right:2%;bottom:14%;}
.bg .o6{width:190px;height:190px;background:#D7E6FF;left:14%;bottom:-30px;}
.bg .ring{position:absolute;border-radius:50%;border:2px solid rgba(0,97,255,.12);}
.bg .r1{width:120px;height:120px;left:6%;bottom:12%;}
.bg .r2{width:60px;height:60px;right:9%;top:18%;}
.bg .r3{width:42px;height:42px;left:13%;top:12%;border-color:rgba(0,97,255,.18);}
.bg .r4{width:90px;height:90px;right:15%;bottom:26%;}
.bg .r5{width:30px;height:30px;left:4%;top:54%;border-color:rgba(0,97,255,.2);}
.bg .dots{position:absolute;inset:0;background-image:radial-gradient(rgba(0,97,255,.12) 1.4px,transparent 1.4px);background-size:26px 26px;opacity:.5;
  -webkit-mask-image:radial-gradient(circle at 50% 42%,transparent 250px,#000 470px);mask-image:radial-gradient(circle at 50% 42%,transparent 250px,#000 470px);}
.stage{position:relative;max-width:980px;margin:0 auto;z-index:1;}
.cblob{position:absolute;width:120px;height:120px;border-radius:50%;z-index:0;}
.cblob.tl{background:var(--primary);top:-34px;left:-34px;opacity:.9;}
.cblob.br{background:#E7ECFF;bottom:-34px;right:-34px;}
.shell{position:relative;z-index:1;width:100%;background:var(--bg);border-radius:28px;box-shadow:0 30px 80px rgba(30,40,90,.18);overflow:hidden;}
.topbar{position:relative;z-index:2;display:flex;align-items:center;gap:16px;padding:18px 30px;border-bottom:1px solid var(--border-subtle);}
.topbar .brand{display:flex;align-items:center;gap:10px;margin-right:auto;}
.topbar .brand img{width:32px;height:32px;border-radius:50%;object-fit:contain;}
.topbar .brand b{font-size:16px;font-weight:800;letter-spacing:1px;}
.links{display:flex;gap:4px;}
.links a{font-size:14px;font-weight:600;color:var(--text-secondary);padding:8px 14px;border-radius:8px;transition:.12s;}
.links a:hover{background:var(--warm);color:var(--text);}
.links a.active{color:var(--primary);background:var(--blue-tint);}
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;border:none;font-family:inherit;letter-spacing:-0.01em;transition:.15s;}
.btn-primary{background:var(--primary);color:#fff;}
.btn-primary:hover{background:var(--primary-dark);box-shadow:0 6px 16px rgba(0,97,255,.26);}
.content{position:relative;z-index:2;padding:30px 34px 40px;}
.hero{background:linear-gradient(150deg,#0061FF,#2A78FF 60%,#0048BD);border-radius:18px;padding:38px 34px;color:#fff;position:relative;overflow:hidden;margin-bottom:28px;}
.hero::after{content:'';position:absolute;right:-50px;top:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.1);}
.hero .eyebrow{font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.8);}
.hero h1{font-size:30px;font-weight:800;letter-spacing:-0.02em;margin-top:8px;position:relative;}
.hero p{font-size:14.5px;color:rgba(255,255,255,.85);margin-top:12px;max-width:560px;line-height:1.6;position:relative;}
.sec-title{font-size:19px;font-weight:800;letter-spacing:-0.01em;margin:26px 0 14px;}
.grid{display:grid;gap:14px;}.g2{grid-template-columns:1fr 1fr;}.g3{grid-template-columns:repeat(3,1fr);}
@media(max-width:760px){.g2,.g3{grid-template-columns:1fr;}.links{display:none;}.content{padding:24px 20px 32px;}}
.card{background:var(--bg);border:1px solid var(--border-subtle);border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.card .ic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:13px;background:var(--blue-tint);color:var(--primary);}
.card h3{font-size:15.5px;font-weight:700;}
.card p{font-size:13.5px;color:var(--text-secondary);margin-top:6px;line-height:1.6;}
.ic.green{background:var(--success-bg);color:var(--success);}
.ic.amber{background:var(--warning-bg);color:var(--warning-text);}
.lead-card{background:var(--warm);border:1px solid var(--border-subtle);border-radius:16px;padding:26px;}
.lead-card p{font-size:14.5px;color:var(--text-secondary);line-height:1.75;}
.lead-card p+p{margin-top:12px;}.lead-card b{color:var(--text);font-weight:700;}
.faq{background:var(--bg);border:1px solid var(--border-subtle);border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.faq-item+.faq-item{border-top:1px solid var(--border-subtle);}
.faq-q{padding:17px 20px;display:flex;align-items:center;gap:13px;cursor:pointer;font-weight:600;font-size:14.5px;transition:background .12s;}
.faq-q:hover{background:var(--warm);}
.faq-q .lic{color:var(--primary);display:flex;}
.faq-q .chev{margin-left:auto;color:var(--text-muted);transition:transform .3s var(--ease);}
.faq-item.open .chev{transform:rotate(180deg);}
.faq-wrap{display:grid;grid-template-rows:0fr;transition:grid-template-rows .3s var(--ease);}
.faq-item.open .faq-wrap{grid-template-rows:1fr;}
.faq-inner{overflow:hidden;}
.faq-a{padding:0 20px 18px 54px;font-size:13.5px;color:var(--text-secondary);line-height:1.7;}
.field{margin-bottom:13px;}
.field label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;}
.field input,.field textarea{width:100%;border:1.5px solid var(--border);border-radius:11px;padding:11px 14px;font-size:14px;font-family:inherit;color:var(--text);background:var(--warm);outline:none;transition:.15s;}
.field input:focus,.field textarea:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 4px rgba(0,97,255,.12);}
.field textarea{min-height:110px;resize:vertical;}
.cinfo{display:flex;align-items:flex-start;gap:13px;}
.cinfo .ic{flex-shrink:0;margin-bottom:0;}
.cinfo .lbl{font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;}
.cinfo .val{font-size:14.5px;font-weight:600;margin-top:3px;}
.hours-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-subtle);font-size:14px;}
.hours-row:last-child{border-bottom:none;}
.hours-row span:last-child{color:var(--text-secondary);}
.cta{margin-top:26px;background:var(--warm);border:1px solid var(--border-subtle);border-radius:16px;padding:24px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
.cta h3{font-size:17px;font-weight:800;}.cta p{font-size:13.5px;color:var(--text-secondary);margin-top:4px;}
.foot{position:relative;z-index:1;text-align:center;font-size:12px;color:var(--text-secondary);margin:20px auto 0;}
.foot a{font-weight:600;}.foot a:hover{color:var(--primary);}
@media (prefers-reduced-motion: reduce){.an{animation:none!important;opacity:1!important;}.bg{animation:none!important;opacity:1!important;}}
</style>
</head>
<body>
<div class="bg">
  <span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span><span class="orb o4"></span><span class="orb o5"></span><span class="orb o6"></span>
  <span class="ring r1"></span><span class="ring r2"></span><span class="ring r3"></span><span class="ring r4"></span><span class="ring r5"></span><span class="dots"></span>
</div>

<div class="stage">
  <span class="cblob tl"></span><span class="cblob br"></span>
  <div class="shell">
    <div class="topbar">
      <div class="brand"><img src="{{ asset('images/logo.png') }}" alt="SIMAMA"><b>SIMAMA</b></div>
      <div class="links">
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Tentang</a>
        <a href="{{ route('bantuan') }}" class="{{ request()->routeIs('bantuan') ? 'active' : '' }}">Bantuan</a>
        <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
      </div>
      <a href="{{ route('login') }}" class="btn btn-primary"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Masuk</a>
    </div>
    <div class="content">
      @yield('content')
    </div>
  </div>
</div>

<div class="foot">© {{ date('Y') }} SIMAMA · Sistem Informasi Magang &nbsp;·&nbsp; <a href="{{ route('about') }}">Tentang</a> &nbsp;·&nbsp; <a href="{{ route('bantuan') }}">Bantuan</a> &nbsp;·&nbsp; <a href="{{ route('contact') }}">Kontak</a></div>

<script>
document.querySelectorAll('.faq-q').forEach(function(q){
  q.addEventListener('click',function(){ this.parentElement.classList.toggle('open'); });
});
</script>
</body>
</html>
