@extends('layouts.mahasiswa')
@section('title', 'Chatbot')
@php $title = 'Chatbot'; @endphp

@push('styles')
<style>
  .cb-card { display:flex; flex-direction:column; height:calc(100vh - var(--topbar-h) - 96px); min-height:420px; }
  .cb-head { display:flex; align-items:center; gap:12px; padding-bottom:14px; border-bottom:1px solid var(--border-subtle); margin-bottom:14px; }
  .cb-avatar { width:40px; height:40px; border-radius:10px; background:var(--blue-tint); color:var(--primary);
               display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .cb-head .t { font-size:15px; font-weight:700; color:var(--text); }
  .cb-head .s { font-size:12px; color:var(--text-secondary); margin-top:2px; }

  .cb-messages { flex:1; overflow-y:auto; padding:6px 2px; display:flex; flex-direction:column; gap:14px; }
  .cb-msg { display:flex; gap:10px; max-width:82%; }
  .cb-msg.user { align-self:flex-end; flex-direction:row-reverse; }
  .cb-ico { width:30px; height:30px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
  .cb-msg.bot .cb-ico  { background:var(--blue-tint); color:var(--primary); }
  .cb-msg.user .cb-ico { background:var(--warm-2); color:var(--text-secondary); }
  .cb-bubble { padding:11px 14px; border-radius:12px; font-size:13.5px; line-height:1.55; }
  .cb-msg.bot  .cb-bubble { background:var(--warm); color:var(--text); border:1px solid var(--border-subtle); border-top-left-radius:4px; }
  .cb-msg.user .cb-bubble { background:var(--primary); color:#fff; border-top-right-radius:4px; }
  .cb-meta { font-size:11px; color:var(--text-muted); margin-top:6px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
  .cb-score { background:var(--warm-2); color:var(--text-secondary); padding:1px 7px; border-radius:20px; font-weight:600; }

  .cb-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:2px; }
  .cb-chip { background:var(--bg); border:1px solid var(--border); color:var(--text); border-radius:20px; padding:6px 12px;
             font-size:12.5px; font-weight:600; cursor:pointer; transition:border-color .12s, color .12s; }
  .cb-chip:hover { border-color:var(--primary); color:var(--primary); }

  .cb-suggests { margin-top:10px; }
  .cb-suggests .lbl { font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; }

  .cb-input-row { display:flex; gap:10px; margin-top:14px; padding-top:14px; border-top:1px solid var(--border-subtle); }
  .cb-input { flex:1; padding:11px 14px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px; color:var(--text); background:var(--bg); }
  .cb-input:focus { outline:none; border-color:var(--primary); }
  .cb-send { flex-shrink:0; display:flex; align-items:center; gap:7px; }

  .cb-typing { display:inline-flex; gap:4px; align-items:center; }
  .cb-typing span { width:6px; height:6px; border-radius:50%; background:var(--text-muted); animation:cbBlink 1.2s infinite both; }
  .cb-typing span:nth-child(2) { animation-delay:.2s; }
  .cb-typing span:nth-child(3) { animation-delay:.4s; }
  @keyframes cbBlink { 0%,80%,100%{opacity:.25} 40%{opacity:1} }

  .cb-recos { display:flex; flex-direction:column; gap:8px; margin-top:10px; }
  .cb-reco { border:1px solid var(--border); border-radius:10px; padding:11px 13px; background:var(--bg); }
  .cb-reco-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
  .cb-reco-co { font-size:13.5px; font-weight:700; color:var(--text); }
  .cb-reco-score { font-size:11px; font-weight:700; color:var(--primary); background:var(--blue-tint); padding:1px 8px; border-radius:20px; flex-shrink:0; }
  .cb-reco-title { font-size:12.5px; color:var(--text-secondary); margin-top:2px; }
  .cb-reco-meta { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:7px; }
  .cb-tag { font-size:11px; font-weight:600; color:var(--primary); background:var(--blue-tint); padding:2px 9px; border-radius:20px; }
  .cb-reco-loc { font-size:11.5px; color:var(--text-muted); }
  .cb-reco-loc::before { content:"📍 "; }
  .cb-reco-contact { font-size:11.5px; color:var(--text-muted); margin-top:6px; }
</style>
@endpush

@section('content')

  <div class="page-header">
    <div>
      <div class="page-title">Chatbot Rekomendasi</div>
      <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
        Asisten SITAMA berbasis TF-IDF &amp; Cosine Similarity — tanyakan seputar prosedur magang, bimbingan, seminar, dan fitur lainnya.
      </div>
    </div>
  </div>

  <div class="card cb-card">
    <div class="cb-head">
      <div class="cb-avatar">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div>
        <div class="t">Asisten SITAMA</div>
        <div class="s">Menjawab dengan mencari informasi paling relevan dari basis pengetahuan.</div>
      </div>
    </div>

    <div class="cb-messages" id="cbMessages">
      <div class="cb-msg bot">
        <div class="cb-ico">AI</div>
        <div>
          <div class="cb-bubble">
            Halo! 👋 Saya asisten SITAMA. Tanyakan apa saja seputar magang — misalnya cara mengajukan magang,
            syarat seminar, atau mengunggah laporan. Kamu juga bisa memilih pertanyaan populer di bawah.
          </div>
          <div class="cb-suggests">
            <div class="cb-chips">
              @foreach($popularQuestions as $q)
                <button type="button" class="cb-chip" onclick="cbAsk(this.textContent.trim())">{{ $q }}</button>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    <form class="cb-input-row" id="cbForm" onsubmit="return cbSubmit(event)">
      <input type="text" class="cb-input" id="cbInput" name="message" maxlength="500"
             placeholder="Tulis pertanyaanmu di sini…" autocomplete="off">
      <button type="submit" class="btn btn-primary cb-send" id="cbSendBtn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Kirim
      </button>
    </form>
  </div>

@endsection

@push('scripts')
<script>
  const CB_ASK_URL = "{{ route('mahasiswa.chatbot.ask') }}";
  const CB_CSRF = "{{ csrf_token() }}";
  const cbMessages = document.getElementById('cbMessages');
  const cbInput = document.getElementById('cbInput');
  const cbSendBtn = document.getElementById('cbSendBtn');

  function cbEscape(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function cbScrollBottom() {
    cbMessages.scrollTop = cbMessages.scrollHeight;
  }

  function cbAppendUser(text) {
    const el = document.createElement('div');
    el.className = 'cb-msg user';
    el.innerHTML = '<div class="cb-ico">Me</div><div><div class="cb-bubble">' + cbEscape(text) + '</div></div>';
    cbMessages.appendChild(el);
    cbScrollBottom();
  }

  function cbAppendTyping() {
    const el = document.createElement('div');
    el.className = 'cb-msg bot';
    el.id = 'cbTyping';
    el.innerHTML = '<div class="cb-ico">AI</div><div><div class="cb-bubble"><span class="cb-typing"><span></span><span></span><span></span></span></div></div>';
    cbMessages.appendChild(el);
    cbScrollBottom();
  }

  function cbRemoveTyping() {
    const t = document.getElementById('cbTyping');
    if (t) t.remove();
  }

  function cbAppendBot(data) {
    const isReco = data.type === 'recommendation';

    // Meta: untuk rekomendasi cukup label kategori (skor per-kartu lebih bermakna).
    let meta = '';
    if (data.found) {
      const pct = Math.round((data.score || 0) * 100);
      meta = '<div class="cb-meta">'
           + (data.category ? '<span class="cb-score">' + cbEscape(data.category) + '</span>' : '')
           + (isReco ? '' : '<span class="cb-score">Relevansi ' + pct + '%</span>')
           + '</div>';
    }

    // Kartu rekomendasi tempat magang.
    let recos = '';
    if (data.recommendations && data.recommendations.length) {
      let cards = '';
      data.recommendations.forEach(r => {
        const pct = Math.round((r.score || 0) * 100);
        cards += '<div class="cb-reco">'
          + '<div class="cb-reco-top"><span class="cb-reco-co">' + cbEscape(r.company) + '</span>'
          + '<span class="cb-reco-score">' + pct + '%</span></div>'
          + (r.title ? '<div class="cb-reco-title">' + cbEscape(r.title) + '</div>' : '')
          + '<div class="cb-reco-meta">'
          + (r.bidang ? '<span class="cb-tag">' + cbEscape(r.bidang) + '</span>' : '')
          + (r.location ? '<span class="cb-reco-loc">' + cbEscape(r.location) + '</span>' : '')
          + '</div>'
          + (r.contact ? '<div class="cb-reco-contact">Kontak: ' + cbEscape(r.contact) + '</div>' : '')
          + '</div>';
      });
      recos = '<div class="cb-recos">' + cards + '</div>';
    }

    // Chip saran FAQ.
    let suggests = '';
    if (data.suggestions && data.suggestions.length) {
      let chips = '';
      data.suggestions.forEach(s => {
        chips += '<button type="button" class="cb-chip" onclick="cbAsk(this.textContent.trim())">' + cbEscape(s.question) + '</button>';
      });
      suggests = '<div class="cb-suggests"><div class="lbl">Mungkin juga menanyakan</div><div class="cb-chips">' + chips + '</div></div>';
    }

    // Untuk rekomendasi, tampilkan hanya baris pembuka (detail ada di kartu).
    let bubbleText = data.answer || '';
    if (isReco && data.recommendations && data.recommendations.length) {
      bubbleText = bubbleText.split('\n')[0];
    }

    const el = document.createElement('div');
    el.className = 'cb-msg bot';
    el.innerHTML = '<div class="cb-ico">AI</div><div><div class="cb-bubble">' + cbEscape(bubbleText) + '</div>' + meta + recos + suggests + '</div>';
    cbMessages.appendChild(el);
    cbScrollBottom();
  }

  async function cbAsk(text) {
    if (!text) return;
    cbInput.value = '';
    cbAppendUser(text);
    cbAppendTyping();
    cbSendBtn.disabled = true;

    try {
      const res = await fetch(CB_ASK_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CB_CSRF,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ message: text }),
      });
      const data = await res.json();
      cbRemoveTyping();
      cbAppendBot(data);
    } catch (e) {
      cbRemoveTyping();
      cbAppendBot({ found: false, answer: 'Maaf, terjadi kendala saat menghubungi server. Coba lagi sebentar.', suggestions: [] });
    } finally {
      cbSendBtn.disabled = false;
      cbInput.focus();
    }
  }

  function cbSubmit(e) {
    e.preventDefault();
    cbAsk(cbInput.value.trim());
    return false;
  }
</script>
@endpush
