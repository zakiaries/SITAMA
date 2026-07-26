<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
    .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .header { background:#2d3e6e; padding:28px 32px; }
    .header h1 { color:#fff; margin:0; font-size:20px; }
    .header p  { color:rgba(255,255,255,.75); margin:4px 0 0; font-size:13px; }
    .body { padding:28px 32px; color:#374151; }
    .body p { font-size:14px; line-height:1.6; margin:0 0 14px; }
    .btn { display:inline-block; background:#2d3e6e; color:#fff !important; text-decoration:none; padding:12px 28px; border-radius:8px; font-size:14px; font-weight:600; margin:8px 0 16px; }
    .url-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; font-size:12px; color:#64748b; word-break:break-all; margin:12px 0; }
    .footer { padding:16px 32px; font-size:11px; color:#94a3b8; border-top:1px solid #f1f5f9; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>SIMAMA Polines</h1>
    <p>Sistem Informasi Magang</p>
  </div>
  <div class="body">
    <p>Halo, <strong>{{ $userName }}</strong>,</p>
    <p>
      Kami menerima permintaan untuk mengatur ulang (reset) password akun SIMAMA Anda.
      Klik tombol di bawah untuk membuat password baru.
    </p>
    <a href="{{ $resetUrl }}" class="btn">Reset Password Saya</a>
    <p style="font-size:13px;color:#64748b;">
      Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:
    </p>
    <div class="url-box">{{ $resetUrl }}</div>
    <p style="font-size:12px;color:#94a3b8;">
      Link ini berlaku selama <strong>{{ $expireMinutes }} menit</strong>. Jika Anda tidak meminta
      reset password, abaikan email ini — password Anda tidak akan berubah.
    </p>
  </div>
  <div class="footer">
    Email ini dikirim otomatis oleh sistem SIMAMA Polines. Jangan membalas email ini.
  </div>
</div>
</body>
</html>
