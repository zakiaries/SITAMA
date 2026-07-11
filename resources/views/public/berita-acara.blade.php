<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Berita Acara Seminar — SITAMA</title>
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#eef2f9;color:#1e2430;
       min-height:100vh;padding:18px 14px 40px;-webkit-font-smoothing:antialiased;}
  .wrap{max-width:460px;margin:0 auto;}
  .head{background:linear-gradient(150deg,#0061FF,#0048BD);color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px;box-shadow:0 8px 24px rgba(0,72,189,.22);}
  .head .eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.82;}
  .head h1{font-size:19px;font-weight:800;margin-top:6px;line-height:1.25;}
  .head .meta{font-size:12.5px;opacity:.9;margin-top:10px;line-height:1.7;}
  .card{background:#fff;border-radius:16px;padding:20px 18px;box-shadow:0 2px 10px rgba(20,30,60,.06);}
  .card h2{font-size:15px;font-weight:800;margin-bottom:4px;}
  .card .sub{font-size:12.5px;color:#6b7480;margin-bottom:16px;}
  .field{margin-bottom:14px;}
  .field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:6px;color:#37414f;}
  .field input{width:100%;border:1.5px solid #d3dae4;border-radius:11px;padding:12px 14px;font-size:15px;font-family:inherit;background:#f7f9fc;outline:none;transition:.15s;}
  .field input:focus{border-color:#0061FF;background:#fff;box-shadow:0 0 0 4px rgba(0,97,255,.12);}
  .row{display:flex;gap:10px;}.row .field{flex:1;}
  .sigbox{border:1.5px dashed #b9c2ce;border-radius:12px;background:#fff;overflow:hidden;position:relative;}
  #sigpad{display:block;width:100%;height:190px;touch-action:none;cursor:crosshair;}
  .sighint{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#aab3bf;font-size:13px;pointer-events:none;}
  .sigbar{display:flex;justify-content:space-between;align-items:center;margin-top:6px;}
  .sigbar span{font-size:11.5px;color:#8a93a0;}
  .link-btn{background:none;border:none;color:#0061FF;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;}
  .submit{width:100%;margin-top:8px;background:#0061FF;color:#fff;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;transition:.15s;}
  .submit:hover{background:#0048BD;}
  .err{background:#fdeceb;border:1px solid #f4c4bf;color:#c0392b;border-radius:10px;padding:10px 13px;font-size:12.5px;margin-bottom:14px;}
  .err ul{margin:0;padding-left:16px;}
  .foot{text-align:center;font-size:11.5px;color:#9aa3af;margin-top:18px;}
</style>
</head>
<body>
<div class="wrap">

  <div class="head">
    <div class="eyebrow">Daftar Hadir Seminar</div>
    <h1>{{ $seminar->title }}</h1>
    <div class="meta">
      @if($seminar->date){{ $seminar->date->format('d M Y') }}@endif
      @if($seminar->time) · {{ $seminar->time }}@endif
      @if($seminar->location)<br>{{ $seminar->location }}@endif
      @if($seminar->student)<br>Penyaji: {{ $seminar->student->user->name ?? '-' }}@endif
    </div>
  </div>

  <div class="card">
    <h2>Isi Berita Acara</h2>
    <div class="sub">Silakan isi identitas dan tanda tangan Anda sebagai bukti kehadiran.</div>

    @if($errors->any())
      <div class="err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('berita-acara.store', $seminar->access_token) }}" id="ba-form">
      @csrf
      <div class="field">
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required>
      </div>
      <div class="field">
        <label>NIM</label>
        <input type="text" name="nim" value="{{ old('nim') }}" placeholder="Nomor Induk Mahasiswa" required>
      </div>
      <div class="row">
        <div class="field">
          <label>Kelas</label>
          <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="mis. IK-3A">
        </div>
        <div class="field">
          <label>Jurusan / Prodi</label>
          <input type="text" name="prodi" value="{{ old('prodi') }}" placeholder="mis. Teknik Informatika">
        </div>
      </div>

      <div class="field">
        <label>Tanda Tangan</label>
        <div class="sigbox">
          <canvas id="sigpad"></canvas>
          <div class="sighint" id="sighint">Tanda tangan di sini</div>
        </div>
        <div class="sigbar">
          <span>Gunakan jari / mouse untuk menandatangani</span>
          <button type="button" class="link-btn" id="sig-clear">Hapus</button>
        </div>
      </div>

      <input type="hidden" name="signature" id="signature">
      <button type="submit" class="submit">Kirim Kehadiran</button>
    </form>
  </div>

  <div class="foot">SITAMA · Sistem Informasi Magang</div>
</div>

<script>
(function(){
  var canvas = document.getElementById('sigpad');
  var hint   = document.getElementById('sighint');
  var ctx = canvas.getContext('2d');
  var drawing = false, hasInk = false;

  function fit(){
    var rect = canvas.getBoundingClientRect();
    canvas.width  = rect.width;
    canvas.height = rect.height;
    ctx.fillStyle = '#fff';
    ctx.fillRect(0,0,canvas.width,canvas.height);
    ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#111827';
  }
  fit();

  function pos(e){
    var rect = canvas.getBoundingClientRect();
    var t = e.touches ? e.touches[0] : e;
    return { x: t.clientX - rect.left, y: t.clientY - rect.top };
  }
  function start(e){ drawing = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); if(hint) hint.style.display='none'; e.preventDefault(); }
  function move(e){ if(!drawing) return; var p = pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); hasInk = true; e.preventDefault(); }
  function end(){ drawing = false; }

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', end);
  canvas.addEventListener('touchstart', start, {passive:false});
  canvas.addEventListener('touchmove', move, {passive:false});
  canvas.addEventListener('touchend', end);

  document.getElementById('sig-clear').addEventListener('click', function(){ fit(); hasInk = false; if(hint) hint.style.display='flex'; });

  document.getElementById('ba-form').addEventListener('submit', function(e){
    if(!hasInk){ e.preventDefault(); alert('Tanda tangan wajib diisi.'); return; }
    document.getElementById('signature').value = canvas.toDataURL('image/png');
  });
})();
</script>
</body>
</html>
