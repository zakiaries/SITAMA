/* ════════════════════════════════════════════════════════════
   SITAMA — micro-interactions (ripple + stat count-up)
   Purely additive; no markup or app logic depends on this.
   Skips entirely when the user prefers reduced motion.
   ════════════════════════════════════════════════════════════ */
(function () {
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  /* Ripple on primary buttons */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.btn-primary');
    if (!btn) return;
    var rect = btn.getBoundingClientRect();
    var d = Math.max(rect.width, rect.height);
    var r = document.createElement('span');
    r.className = 'ripple';
    r.style.width = r.style.height = d + 'px';
    r.style.left = (e.clientX - rect.left - d / 2) + 'px';
    r.style.top = (e.clientY - rect.top - d / 2) + 'px';
    btn.appendChild(r);
    setTimeout(function () { r.remove(); }, 560);
  });

  /* Count-up for integer .stat-value numbers */
  function countUp(el) {
    var raw = (el.textContent || '').trim();
    if (!/^\d{1,9}$/.test(raw)) return;          // only plain integers
    var target = parseInt(raw, 10);
    if (target <= 0) return;                       // nothing to animate
    var dur = 900, t0 = performance.now();
    el.textContent = '0';
    function step(now) {
      var p = Math.min((now - t0) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased);
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = target;
    }
    requestAnimationFrame(step);
  }

  function init() {
    document.querySelectorAll('.stat-value').forEach(countUp);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
