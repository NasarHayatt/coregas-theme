/* Core Gas front-end behaviour. Options come from Theme Settings via `coregasConfig`.
   Scrolling is the browser's own by default; nothing is animated by scroll position. */
(function () {
  var cfg = window.coregasConfig || {};
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- optional smooth scrolling (Lenis), loaded on demand ----------
     Default comes from Theme Settings. Any page opened with ?smooth=1 or ?smooth=0
     overrides it for this browser (remembered), so each screen can be compared. */
  var lenis = null;
  var pref = null;
  try {
    var q = new URLSearchParams(location.search).get('smooth');
    if (q === '1' || q === '0') localStorage.setItem('cgSmooth', q);
    pref = localStorage.getItem('cgSmooth');
  } catch (e) {}
  var wantSmooth = pref !== null ? pref === '1' : !!cfg.smoothScroll;
  if (wantSmooth && !reduce) {
    var sc = document.createElement('script');
    sc.src = 'https://unpkg.com/lenis@1.1.13/dist/lenis.min.js';
    sc.async = true;
    sc.onload = function () {
      if (!window.Lenis) return;
      lenis = new Lenis({ lerp: 0.12, wheelMultiplier: 0.9, smoothWheel: true });
      (function raf(t) { lenis.raf(t); requestAnimationFrame(raf); })(performance.now());
    };
    document.head.appendChild(sc);
  }

  /* ---------- in-page anchor links ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href');
      if (id.length < 2) return;
      var el;
      try { el = document.querySelector(id); } catch (err) { return; }
      if (!el) return;
      e.preventDefault();
      closeMenu();
      if (lenis) lenis.scrollTo(el, { offset: -10 }); else el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' });
    });
  });

  /* ---------- header: solid after the top, optionally hides while scrolling down ---------- */
  var hdr = document.getElementById('hdr'), lastY = 0, menuOpen = false, ticking = false, solid = null, hidden = null;
  function updateHeader() {
    ticking = false;
    if (!hdr) return;
    var y = window.scrollY;
    var s = y > 40;
    if (s !== solid) { solid = s; hdr.classList.toggle('is-solid', s); }
    if (cfg.hideOnScroll) {
      var h = y > lastY && y > window.innerHeight * .8 && !menuOpen;
      if (Math.abs(y - lastY) > 2 && h !== hidden) { hidden = h; hdr.classList.toggle('is-hidden', h); }
    }
    lastY = y;
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(updateHeader); } }, { passive: true });
  updateHeader();

  /* ---------- mobile menu ---------- */
  var nav = document.getElementById('nav'), btn = document.getElementById('menuBtn');
  function setMenu(open) {
    if (!nav || !btn) return;
    menuOpen = open;
    nav.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open);
    if (lenis) { if (open) lenis.stop(); else lenis.start(); }
    document.body.style.overflow = open ? 'hidden' : '';
  }
  function closeMenu() { if (menuOpen) setMenu(false); }
  if (btn) btn.addEventListener('click', function () { setMenu(!menuOpen); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

  /* ---------- enquiry form: gas supply fields follow the enquiry type ---------- */
  document.querySelectorAll('.cg-form[data-supply]').forEach(function (form) {
    var idx = form.getAttribute('data-supply');
    var sel = form.querySelector('select[name="cg_type"]');
    var box = form.querySelector('.cg-form__supply');
    if (!sel || !box || idx === '-1') return;
    var sync = function () { box.hidden = sel.value !== idx; };
    sel.addEventListener('change', sync);
    sync();
  });

  /* ---------- hero photo slideshow: cross-fade, paused while the tab is hidden ---------- */
  document.querySelectorAll('.hero__photo[data-interval]').forEach(function (box) {
    var slides = box.querySelectorAll('.hero__slide');
    if (slides.length < 2 || reduce) return;
    var i = 0;
    var ms = Math.min(20, Math.max(3, parseFloat(box.getAttribute('data-interval')) || 7)) * 1000;
    setInterval(function () {
      if (document.hidden) return;
      slides[i].classList.remove('is-active');
      i = (i + 1) % slides.length;
      slides[i].classList.add('is-active');
    }, ms);
  });

  /* ---------- hero entrance: one CSS animation on load (see .cg-intro in main.css) ---------- */
  if (cfg.heroIntro && !reduce && document.querySelector('.hero__title')) {
    document.documentElement.classList.add('cg-intro');
  }
})();
