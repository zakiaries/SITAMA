@extends('layouts.mahasiswa')
@section('title', 'Lowongan Magang')
@php $title = 'Lowongan Magang'; @endphp
@section('content')
        <div class="page-header"><div class="page-title">Lowongan Magang</div></div>
        <div class="search-bar">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input placeholder="Cari posisi atau perusahaan...">
        </div>
        <div class="chips">
          <div class="chip active">Semua</div>
          <div class="chip">IT</div>
          <div class="chip">Desain</div>
          <div class="chip">Data</div>
          <div class="chip">Marketing</div>
        </div>
        <div class="ai-banner">
          <div class="ai-icon"><svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
          <div class="ai-text"><h4>Minta Rekomendasi AI</h4><p>Dapatkan rekomendasi posisi yang sesuai dengan profil Anda</p></div>
          <div class="ai-arrow">›</div>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:13px;">Lowongan Tersedia — <strong style="color:var(--text);">6 posisi tersedia untuk Anda</strong></div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#e8eef8;color:#0c2a5c;">TI</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">Frontend Developer Intern</div><div class="job-company">PT. Telkom Indonesia</div></div><span class="badge baru">Baru</span></div>
              <div class="job-tags"><span class="tag">Flutter</span><span class="tag">Dart</span><span class="tag">UI/UX</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#e1f5ee;color:#085041;">BN</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">Backend Engineer Intern</div><div class="job-company">Bank Negara Indonesia</div></div></div>
              <div class="job-tags"><span class="tag">Laravel</span><span class="tag">PHP</span><span class="tag">MySQL</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#faeeda;color:#633806;">GO</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">Data Analyst Intern</div><div class="job-company">Gojek Indonesia</div></div></div>
              <div class="job-tags"><span class="tag">Python</span><span class="tag">SQL</span><span class="tag">Tableau</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#fcebeb;color:#791f1f;">GR</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">UI/UX Designer Intern</div><div class="job-company">Grab Indonesia</div></div></div>
              <div class="job-tags"><span class="tag">Figma</span><span class="tag">Prototyping</span><span class="tag">Research</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#eeedfe;color:#3c3489;">TO</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">Mobile Developer Intern</div><div class="job-company">Tokopedia</div></div><span class="badge baru">Baru</span></div>
              <div class="job-tags"><span class="tag">React Native</span><span class="tag">JavaScript</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>

        <div class="job-card">
          <div class="job-top">
            <div class="job-logo" style="background:#e1f5ee;color:#085041;">SH</div>
            <div class="job-info">
              <div class="job-head"><div><div class="job-title">Cloud Engineer Intern</div><div class="job-company">Shopee Indonesia</div></div></div>
              <div class="job-tags"><span class="tag">AWS</span><span class="tag">Docker</span><span class="tag">Kubernetes</span></div>
            </div>
          </div>
          <div class="job-footer">
            <div class="job-loc"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Semarang</div>
            <button class="btn btn-primary btn-sm" onclick="daftar(this)">+ Daftar</button>
          </div>
        </div>
@endsection
