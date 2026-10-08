/*!
 * Beleon Tours — front-end interactions. Vanilla JS, deferred, ~4 KB minified.
 * Every feature is progressive: content is fully usable without this file.
 */
(function () {
  'use strict';

  var d = document;
  var root = d.documentElement;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); };

  /* Decorative layers (shapes, grain) for elements that ask for them, incl. Elementor containers. */
  function fx(scope) {
    $$('[class*="bl-shape-"], .bl-grain-yes', scope).forEach(function (el) {
      if (el.__blfx) { return; }
      el.__blfx = true;
      var m = el.className.match(/\bbl-shape-([a-z]+)\b/);
      var add = function (type) {
        var s = d.createElement('span');
        s.className = 'bl-fx bl-fx--' + type;
        s.setAttribute('aria-hidden', 'true');
        el.insertBefore(s, el.firstChild);
      };
      if (m) { add(m[1]); }
      if (/\bbl-grain-yes\b/.test(el.className)) { add('grain'); }
    });
  }

  /* Scroll reveal + stagger. Clip-based reveals (mask, curtain) start with zero
     visible area, which the observer can't see, so their parent is watched instead. */
  var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) { return; }
      if (e.target.__blSelf) { e.target.classList.add('is-in'); }
      (e.target.__blReveal || []).forEach(function (el) { el.classList.add('is-in'); });
      io.unobserve(e.target);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.01 }) : null;

  function reveal(scope) {
    $$('.bl-stagger, .bl-stagger-yes', scope).forEach(function (el) {
      var kids = el.querySelector(':scope > .e-con-inner') ? el.querySelector(':scope > .e-con-inner').children : el.children;
      Array.prototype.forEach.call(kids, function (k, i) { k.style.setProperty('--i', i); });
    });
    $$('[class*="bl-m-"], .bl-stagger, .bl-stagger-yes', scope).forEach(function (el) {
      if (el.classList.contains('is-in')) { return; }
      if (!io || reduce) { el.classList.add('is-in'); return; }
      if (/\bbl-m-(mask-up|curtain)\b/.test(el.className) && el.parentElement) {
        var parent = el.parentElement;
        (parent.__blReveal = parent.__blReveal || []).push(el);
        io.observe(parent);
      } else {
        el.__blSelf = true;
        io.observe(el);
      }
    });
  }

  /* Count-up numbers. */
  function counters(scope) {
    $$('[data-bl-count]', scope).forEach(function (el) {
      if (el.__blc) { return; }
      el.__blc = true;
      var end = parseFloat(el.getAttribute('data-bl-count')) || 0;
      var plain = el.hasAttribute('data-bl-plain');
      var fmt = function (n) { return plain ? String(Math.round(n)) : Math.round(n).toLocaleString(root.lang || undefined); };
      if (reduce || !io) { return; }
      var start = plain ? Math.max(0, end - 40) : 0;
      el.textContent = fmt(start);
      var o = new IntersectionObserver(function (es) {
        if (!es[0].isIntersecting) { return; }
        o.disconnect();
        var t0 = performance.now(), dur = 1800;
        (function step(t) {
          var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 4);
          el.textContent = fmt(start + (end - start) * e);
          if (p < 1) { requestAnimationFrame(step); }
        })(t0);
      }, { threshold: 0.6 });
      o.observe(el);
    });
  }

  /* Carousels: native scroll-snap + arrow buttons. */
  function carousels(scope) {
    $$('[data-bl-carousel]', scope).forEach(function (c) {
      if (c.__blcar) { return; }
      c.__blcar = true;
      var track = c.querySelector('.bl-carousel__track');
      var prev = c.querySelector('[data-bl-prev]'), next = c.querySelector('[data-bl-next]');
      if (!track) { return; }
      var page = function (dir) {
        var item = track.firstElementChild;
        var w = item ? item.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth;
        track.scrollBy({ left: dir * Math.max(w, track.clientWidth * (track.clientWidth > 700 ? 0.5 : 1) - 1), behavior: reduce ? 'auto' : 'smooth' });
      };
      var sync = function () {
        if (prev) { prev.disabled = track.scrollLeft < 4; }
        if (next) { next.disabled = track.scrollLeft + track.clientWidth > track.scrollWidth - 4; }
      };
      if (prev) { prev.addEventListener('click', function () { page(-1); }); }
      if (next) { next.addEventListener('click', function () { page(1); }); }
      track.addEventListener('scroll', sync, { passive: true });
      track.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { e.preventDefault(); page(1); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); page(-1); }
      });
      sync();
    });
  }

  /* Lightbox for [data-bl-gallery] links, using <dialog>. */
  var dlg;
  function lightbox() {
    d.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('[data-bl-lightbox]');
      if (!a || e.metaKey || e.ctrlKey || !window.HTMLDialogElement) { return; }
      e.preventDefault();
      var items = $$('[data-bl-lightbox]', a.closest('[data-bl-gallery]') || d);
      var i = items.indexOf(a);
      if (!dlg) {
        dlg = d.createElement('dialog');
        dlg.className = 'bl-lightbox';
        dlg.innerHTML = '<img alt=""><button type="button" class="bl-arrow" data-close aria-label="Close">✕</button>' +
          '<button type="button" class="bl-arrow bl-arrow--prev" data-prev aria-label="Previous"><svg class="bl-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>' +
          '<button type="button" class="bl-arrow" data-next aria-label="Next"><svg class="bl-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>';
        d.body.appendChild(dlg);
        dlg.addEventListener('click', function (ev) {
          if (ev.target === dlg || ev.target.closest('[data-close]')) { dlg.close(); }
        });
      }
      var img = dlg.querySelector('img');
      var show = function (n) { i = (n + items.length) % items.length; img.src = items[i].href; img.alt = (items[i].querySelector('img') || {}).alt || ''; };
      dlg.querySelector('[data-prev]').onclick = function () { show(i - 1); };
      dlg.querySelector('[data-next]').onclick = function () { show(i + 1); };
      dlg.onkeydown = function (ev) { if (ev.key === 'ArrowRight') { show(i + 1); } if (ev.key === 'ArrowLeft') { show(i - 1); } };
      show(i);
      dlg.showModal();
    });
  }

  /* Header: scrolled state, hide on scroll down, mobile menu. */
  function header() {
    var h = d.getElementById('bl-header');
    var bar = d.querySelector('[data-bl-mbar]');
    var last = 0, ticking = false;
    var onScroll = function () {
      var y = window.scrollY;
      if (h) {
        h.classList.toggle('is-scrolled', y > 30);
        if (!h.classList.contains('is-open')) { h.classList.toggle('is-hidden', y > 600 && y > last + 4); }
        if (y < last - 4) { h.classList.remove('is-hidden'); }
      }
      if (bar) { bar.classList.toggle('is-on', y > window.innerHeight * 0.7); }
      last = y; ticking = false;
    };
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();
    if (!h) { return; }

    var btn = h.querySelector('.bl-burger');
    var nav = d.getElementById('bl-nav');
    if (!btn || !nav) { return; }
    $$('.bl-menu > li', nav).forEach(function (li, i) { li.style.setProperty('--i', i); });
    var toggle = function (open) {
      h.classList.toggle('is-open', open);
      d.body.classList.toggle('bl-menu-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) { var f = nav.querySelector('a'); if (f) { setTimeout(function () { f.focus({ preventScroll: true }); }, 300); } }
    };
    btn.addEventListener('click', function () { toggle(!h.classList.contains('is-open')); });
    d.addEventListener('keydown', function (e) { if (e.key === 'Escape' && h.classList.contains('is-open')) { toggle(false); btn.focus(); } });
    nav.addEventListener('click', function (e) { if (e.target.closest('a[href*="#"]')) { toggle(false); } });
  }

  /* Tour page: highlight the in-page section link. */
  function toc() {
    var links = $$('.bl-toc a[href^="#"]');
    if (!links.length || !io) { return; }
    var map = {};
    var o = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) {
          links.forEach(function (l) { l.classList.remove('is-active'); });
          if (map[e.target.id]) { map[e.target.id].classList.add('is-active'); }
        }
      });
    }, { rootMargin: '-40% 0px -55% 0px' });
    links.forEach(function (l) {
      var t = d.getElementById(l.getAttribute('href').slice(1));
      if (t) { map[t.id] = l; o.observe(t); }
    });
  }

  function init(scope) { fx(scope); reveal(scope); counters(scope); carousels(scope); }

  init(d);
  header();
  lightbox();
  toc();

  /* Elementor editor: re-run on widgets as they render. */
  window.addEventListener('elementor/frontend/init', function () {
    if (!window.elementorFrontend || !window.elementorFrontend.hooks) { return; }
    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($el) {
      var el = $el && $el[0] ? $el[0] : null;
      if (el) { init(el.parentNode || el); }
    });
  });
})();
