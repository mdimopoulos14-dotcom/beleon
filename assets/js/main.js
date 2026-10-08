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

  /* Tour finder: instant filtering + sorting of the server-rendered cards, URL kept in sync. */
  var fold = function (s) {
    return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ς/g, 'σ').replace(/\s+/g, ' ').trim();
  };
  function finder(scope) {
    $$('[data-bl-finder]', scope).forEach(function (f) {
      if (f.__blf) { return; }
      f.__blf = true;
      var form = f.querySelector('form');
      var grid = f.querySelector('.bl-finder__grid');
      var cards = $$('.bl-tcard', grid);
      var count = f.querySelector('.bl-finder__count');
      var empty = f.querySelector('.bl-finder__empty');
      var reset = f.querySelector('.bl-finder__reset');
      var sort = f.querySelector('select[name="sort"]');
      var chips = $$('[data-region]', f.querySelector('.bl-finder__regions') || f).filter(function (c) { return c.tagName === 'A'; });
      var go = f.querySelector('.bl-finder__go');
      var state = {};
      var budget = function (v) { return parseInt(v, 10) || 0; };
      if (go) { go.hidden = true; }
      var read = function () {
        var p = new URLSearchParams(location.search);
        ['q', 'region', 'dest', 'month', 'days', 'from', 'price', 'sort'].forEach(function (k) { state[k] = p.get(k) || ''; });
      };
      var ranges = { short: [1, 5], medium: [6, 9], long: [10, 999] };
      var match = function (c) {
        var ds = c.dataset;
        var has = function (list, v) { return (' ' + (list || '') + ' ').indexOf(' ' + v + ' ') > -1; };
        if (state.region && !has(ds.region, state.region)) { return false; }
        if (state.dest && !has(ds.dest, state.dest)) { return false; }
        if (state.month && !has(ds.months, state.month)) { return false; }
        if (state.from && !has(ds.from, state.from)) { return false; }
        if (state.days && ranges[state.days]) {
          var n = parseInt(ds.days, 10) || 0;
          if (n < ranges[state.days][0] || n > ranges[state.days][1]) { return false; }
        }
        if (state.price) {
          var pr = parseInt(ds.price, 10) || 0;
          if (!pr || pr > budget(state.price)) { return false; }
        }
        if (state.q) {
          var words = fold(state.q).split(' ');
          for (var i = 0; i < words.length; i++) { if (words[i] && ds.search.indexOf(words[i]) === -1) { return false; } }
        }
        return true;
      };
      var key = function (c) {
        var ds = c.dataset, big = 1e15;
        switch (state.sort) {
          case 'date': return parseInt(ds.next, 10) || big;
          case 'price': return parseInt(ds.price, 10) || big;
          case 'price-desc': return -(parseInt(ds.price, 10) || 0);
          case 'days': return parseInt(ds.days, 10) || big;
          default: return 0;
        }
      };
      var apply = function (push) {
        var shown = 0;
        var ordered = cards.slice().sort(function (a, b) {
          return (key(a) - key(b)) || ((parseInt(a.dataset.order, 10) || 0) - (parseInt(b.dataset.order, 10) || 0));
        });
        ordered.forEach(function (c) {
          var ok = match(c);
          c.hidden = !ok;
          if (ok) { shown++; c.classList.add('is-in'); }
          grid.appendChild(c);
        });
        if (count) { count.textContent = (shown === 1 ? count.dataset.one : count.dataset.many).replace('%d', shown); }
        if (empty) { empty.hidden = shown > 0; }
        var active = ['q', 'region', 'dest', 'month', 'days', 'from', 'price'].some(function (k) { return state[k]; });
        if (reset) { reset.hidden = !active; }
        chips.forEach(function (c) { c.classList.toggle('is-active', c.dataset.region === state.region); });
        if (push !== false && window.history && history.replaceState) {
          var p = new URLSearchParams();
          Object.keys(state).forEach(function (k) { if (state[k]) { p.set(k, state[k]); } });
          var qs = p.toString();
          history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
        }
      };
      var fromForm = function () {
        $$('input[name], select[name]', f).forEach(function (el) {
          if (el.type !== 'hidden' && state.hasOwnProperty(el.name)) { state[el.name] = el.value.trim(); }
        });
      };
      read();
      var t;
      f.addEventListener('input', function (e) {
        if (!e.target.name) { return; }
        fromForm();
        clearTimeout(t);
        t = setTimeout(apply, e.target.type === 'search' ? 160 : 0);
      });
      f.addEventListener('change', function () { fromForm(); apply(); });
      if (form) { form.addEventListener('submit', function (e) { e.preventDefault(); fromForm(); apply(); }); }
      chips.forEach(function (c) {
        c.addEventListener('click', function (e) {
          e.preventDefault();
          state.region = c.dataset.region;
          var hid = form && form.querySelector('input[name="region"]');
          if (hid) { hid.value = state.region; }
          apply();
        });
      });
      if (reset) {
        reset.addEventListener('click', function (e) {
          e.preventDefault();
          Object.keys(state).forEach(function (k) { if (k !== 'sort') { state[k] = ''; } });
          $$('input[name], select[name]', f).forEach(function (el) { if (el.name !== 'sort' && el.type !== 'hidden') { el.value = ''; } });
          apply();
        });
      }
      if (sort) { sort.value = state.sort; }
      apply(false);
    });
  }

  /* Forms: send with fetch, show the result in place (normal POST without JS). */
  /* Departure calendar: marks the tour's departures; any later day can be requested. */
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function isoDate(dt) { return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()); }
  function parseIso(s) { var p = s.split('-'); return new Date(+p[0], p[1] - 1, +p[2]); }

  function calendars(scope) {
    $$('[data-bl-cal]', scope).forEach(function (box) {
      if (box.__blcal) { return; }
      var conf;
      try { conf = JSON.parse(box.getAttribute('data-bl-cal')); } catch (e) { return; }
      var field = box.parentNode;
      var select = field.querySelector('select');
      if (!select || !window.Intl) { return; }
      box.__blcal = true;
      var L = conf.labels || {};
      var input = d.createElement('input');
      input.type = 'hidden';
      input.name = select.name;
      if (select.required) { input.setAttribute('data-bl-required', ''); }
      var label = field.querySelector('label');
      var gridId = select.id;
      select.parentNode.removeChild(select);
      field.insertBefore(input, box);
      box.hidden = false;

      var deps = {};
      (conf.deps || []).forEach(function (x) { deps[x.d] = x; });
      var today = new Date(); today.setHours(0, 0, 0, 0);
      var loc = conf.locale || d.documentElement.lang || 'en';
      var fMonth = new Intl.DateTimeFormat(loc, { month: 'long', year: 'numeric' });
      var fDay = new Intl.DateTimeFormat(loc, { day: 'numeric', month: 'long', year: 'numeric' });
      var fWeek = new Intl.DateTimeFormat(loc, { weekday: 'short' });
      var start = +conf.start || 0;
      var first = conf.deps && conf.deps[0] ? parseIso(conf.deps[0].d) : today;
      var view = new Date(first.getFullYear(), first.getMonth(), 1);
      var minView = new Date(today.getFullYear(), today.getMonth(), 1);
      var sel = null;

      var arrow = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>';
      box.innerHTML = '<div class="bl-cal__head"><button type="button" class="bl-cal__nav" data-dir="-1" aria-label="' + (L.prev || '') + '">' + arrow + '</button>'
        + '<p class="bl-cal__month" aria-live="polite"></p>'
        + '<button type="button" class="bl-cal__nav bl-cal__nav--next" data-dir="1" aria-label="' + (L.next || '') + '">' + arrow + '</button></div>'
        + '<div class="bl-cal__grid" role="group" id="' + gridId + '"></div>'
        + '<p class="bl-cal__legend"><span class="bl-cal__dot" aria-hidden="true"></span>' + (L.legend || '') + '<span class="bl-cal__sep" aria-hidden="true">·</span>' + (L.other || '') + '</p>'
        + (conf.texts && conf.texts.length ? '<p class="bl-cal__texts"><strong>' + (L.dates || '') + ':</strong> ' + conf.texts.map(function (t) { return t.replace(/[<>&]/g, ''); }).join(', ') + '</p>' : '')
        + '<p class="bl-cal__sel" data-empty="' + (L.pick || '') + '">' + (L.pick || '') + '</p>';
      var grid = box.querySelector('.bl-cal__grid');
      var month = box.querySelector('.bl-cal__month');
      var out = box.querySelector('.bl-cal__sel');
      var prev = box.querySelector('[data-dir="-1"]');
      if (label) { label.id = label.id || gridId + '-label'; grid.setAttribute('aria-labelledby', label.id); label.removeAttribute('for'); }

      function render(focusIso) {
        month.textContent = fMonth.format(view);
        prev.disabled = view <= minView;
        var h = '';
        for (var i = 0; i < 7; i++) {
          h += '<span class="bl-cal__wd" aria-hidden="true">' + fWeek.format(new Date(2024, 0, 7 + ((start + i) % 7))).replace('.', '') + '</span>';
        }
        var lead = (view.getDay() - start + 7) % 7;
        for (var j = 0; j < lead; j++) { h += '<span></span>'; }
        var days = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        for (var k = 1; k <= days; k++) {
          var dt = new Date(view.getFullYear(), view.getMonth(), k);
          var id = isoDate(dt);
          var dep = deps[id];
          var past = dt < today;
          var cls = 'bl-cal__day' + (dep ? ' is-dep' : '') + (id === sel ? ' is-sel' : '') + (+dt === +today ? ' is-today' : '');
          h += '<button type="button" class="' + cls + '" data-date="' + id + '"' + (past ? ' disabled' : '') + ' aria-pressed="' + (id === sel) + '" aria-label="' + fDay.format(dt) + (dep ? ' – ' + (L.departure || '') + (dep.n ? ' (' + dep.n + ')' : '') : '') + '"' + ((focusIso ? id === focusIso : (id === sel || (!sel && k === 1))) ? '' : ' tabindex="-1"') + '>' + k + '</button>';
        }
        grid.innerHTML = h;
        if (focusIso) { var f = grid.querySelector('[data-date="' + focusIso + '"]'); if (f) { f.focus(); } }
      }

      function pick(id, quiet) {
        sel = id;
        var dt = parseIso(id);
        var dep = deps[id];
        input.value = dep ? dep.v : (L.request || '') + ': ' + fDay.format(dt);
        out.innerHTML = '<strong>' + fDay.format(dt) + '</strong><span>' + (dep ? (L.departure || '') + (dep.n ? ' · ' + dep.n : '') : (L.request || '')) + '</span>';
        out.classList.add('is-set');
        field.classList.remove('is-invalid');
        render(quiet ? null : id);
      }

      box.addEventListener('click', function (e) {
        var nav = e.target.closest('.bl-cal__nav');
        if (nav) { view = new Date(view.getFullYear(), view.getMonth() + (+nav.dataset.dir), 1); render(); return; }
        var day = e.target.closest('.bl-cal__day');
        if (day && !day.disabled) { pick(day.dataset.date); }
      });
      grid.addEventListener('keydown', function (e) {
        var day = e.target.closest('.bl-cal__day');
        var step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
        if (!day || !step) { return; }
        e.preventDefault();
        var dt = parseIso(day.dataset.date);
        dt.setDate(dt.getDate() + step);
        if (dt < today) { return; }
        view = new Date(dt.getFullYear(), dt.getMonth(), 1);
        render(isoDate(dt));
      });

      render();
      if (conf.deps && conf.deps[0]) { pick(conf.deps[0].d, true); }
    });
  }

  /* − / + buttons around number inputs. */
  function steppers(scope) {
    $$('input[data-bl-stepper]', scope).forEach(function (inp) {
      if (inp.__blstep) { return; }
      inp.__blstep = true;
      var wrap = d.createElement('span');
      wrap.className = 'bl-stepper';
      inp.parentNode.insertBefore(wrap, inp);
      var mk = function (txt, dir, aria) {
        var b = d.createElement('button');
        b.type = 'button';
        b.className = 'bl-stepper__btn';
        b.textContent = txt;
        b.setAttribute('aria-label', aria || txt);
        b.addEventListener('click', function () {
          var v = (parseInt(inp.value, 10) || 0) + dir;
          inp.value = Math.max(+inp.min || 1, Math.min(+inp.max || 99, v));
          inp.dispatchEvent(new Event('change', { bubbles: true }));
        });
        return b;
      };
      wrap.appendChild(mk('−', -1, inp.dataset.less));
      wrap.appendChild(inp);
      wrap.appendChild(mk('+', 1, inp.dataset.more));
    });
  }

  function fieldOf(el) { return el.closest('.bl-form__field, .bl-form__consent'); }
  function markInvalid(el) { var f = fieldOf(el); if (f) { f.classList.add('is-invalid'); } el.setAttribute('aria-invalid', 'true'); }
  function missingHidden(scope) { return $$('input[type="hidden"][data-bl-required]', scope).filter(function (el) { return !el.value; }); }

  /* Two-step booking panel: date and travellers, then contact details. */
  function steps(form) {
    if (!form.hasAttribute('data-bl-steps')) { return function () {}; }
    var panes = $$('.bl-form__step', form);
    var summary = form.querySelector('[data-bl-summary]');
    var show = function (n, focus) {
      panes.forEach(function (p) { p.hidden = p.dataset.step !== String(n); });
      form.setAttribute('data-step', n);
      if (focus) {
        var first = form.querySelector('.bl-form__step[data-step="' + n + '"] input:not([type="hidden"]), .bl-form__step[data-step="' + n + '"] button');
        if (first) { first.focus({ preventScroll: true }); }
      }
    };
    form.classList.add('is-steps');
    show(1);
    form.addEventListener('click', function (e) {
      if (e.target.closest('[data-bl-next]')) {
        var pane = form.querySelector('.bl-form__step[data-step="1"]');
        var bad = missingHidden(pane).concat($$(':invalid', pane).filter(function (el) { return el.name; }));
        if (bad.length) {
          bad.forEach(markInvalid);
          return;
        }
        if (summary) {
          var date = form.querySelector('.bl-cal__sel strong');
          var trav = form.querySelector('input[name="travelers"]');
          summary.textContent = (date ? date.textContent : '') + (trav ? ' · ' + (summary.dataset.trav || '%d').replace('%d', trav.value) : '');
        }
        show(2, true);
      } else if (e.target.closest('[data-bl-back]')) {
        show(1, true);
      }
    });
    return show;
  }

  function forms(scope) {
    calendars(scope);
    steppers(scope);
    $$('[data-bl-form]', scope).forEach(function (form) {
      if (form.__blform || !window.fetch || !window.FormData) { return; }
      form.__blform = true;
      var status = form.querySelector('.bl-form__status');
      var btn = form.querySelector('[type="submit"]');
      var show = steps(form);
      var say = function (msg, ok) {
        status.hidden = false;
        status.textContent = msg;
        status.classList.toggle('is-error', !ok);
      };
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        $$('.is-invalid', form).forEach(function (el) { el.classList.remove('is-invalid'); });
        $$('[aria-invalid]', form).forEach(function (el) { el.removeAttribute('aria-invalid'); });
        var hidden = missingHidden(form);
        if (hidden.length) {
          hidden.forEach(markInvalid);
          show(1, true);
          return;
        }
        if (!form.checkValidity()) {
          var bad = $$(':invalid', form).filter(function (el) { return el.name; });
          bad.forEach(markInvalid);
          if (bad[0]) { bad[0].focus(); }
          return;
        }
        var data = new FormData(form);
        data.append('bl_ajax', '1');
        btn.disabled = true;
        form.classList.add('is-sending');
        fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
          .then(function (r) { return r.json().catch(function () { return { success: false, data: {} }; }); })
          .then(function (res) {
            var msg = res && res.data && res.data.message;
            if (res && res.success) {
              form.classList.add('is-sent');
              say(msg || status.dataset.ok, true);
              $$('input:not([type="hidden"]):not([type="checkbox"]), textarea', form).forEach(function (el) { el.value = el.type === 'number' ? '2' : ''; });
            } else {
              ((res && res.data && res.data.fields) || []).forEach(function (n) {
                var el = form.querySelector('[name="' + n + '"]');
                if (el) { markInvalid(el); }
              });
              say(msg || 'Error', false);
            }
          })
          .catch(function () { form.submit(); })
          .then(function () { btn.disabled = false; form.classList.remove('is-sending'); });
      });
    });
  }

  function init(scope) { fx(scope); reveal(scope); counters(scope); carousels(scope); finder(scope); forms(scope); }

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
