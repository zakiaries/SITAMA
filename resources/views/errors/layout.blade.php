<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('code') · SIMAMA</title>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#eef2f9;color:#1e2430;
         min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
    .box{background:#fff;border-radius:18px;padding:44px 34px;max-width:440px;width:100%;text-align:center;
         box-shadow:0 8px 30px rgba(20,30,60,.10);}
    .brand{font-size:15px;font-weight:800;color:#0061FF;letter-spacing:.02em;}
    .brand span{color:#94a3b8;font-weight:600;}
    .code{font-size:64px;font-weight:800;color:#0061FF;line-height:1;margin:18px 0 6px;letter-spacing:-.02em;}
    .title{font-size:19px;font-weight:700;margin-bottom:8px;}
    .msg{font-size:13.5px;color:#6b7480;line-height:1.65;margin-bottom:24px;}
    .btn{display:inline-block;background:#0061FF;color:#fff;text-decoration:none;padding:12px 26px;
         border-radius:10px;font-size:14px;font-weight:700;transition:.15s;}
    .btn:hover{background:#0048BD;}
  </style>
</head>
<body>
  <div class="box">
    <div class="brand">SIMAMA <span>· Sistem Informasi Magang</span></div>
    <div class="code">@yield('code')</div>
    <div class="title">@yield('title')</div>
    <div class="msg">@yield('message')</div>
    <a class="btn" href="{{ url('/') }}">← Kembali ke Beranda</a>
  </div>
</body>
</html>
