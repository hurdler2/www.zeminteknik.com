/* Zemin Teknik — ortak arayüz davranışları (menü çekmecesi sayfa içi eski betikte kalır) */
(function () {
  'use strict';

  /* Header: kaydırınca katı zemin */
  var header = document.getElementById('mainHeader');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 40) header.classList.add('is-scrolled');
      else header.classList.remove('is-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Aktif menü öğesi */
  try {
    var path = location.pathname.replace(/index\.html$/, '');
    var links = document.querySelectorAll('.zt-nav a[href], .zt-drawer a[href]');
    Array.prototype.forEach.call(links, function (a) {
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || /^https?:/.test(href)) return;
      var target = new URL(href, location.href).pathname.replace(/index\.html$/, '');
      if (target === path && target !== '/') {
        a.classList.add('is-active');
        var item = a.closest('.zt-nav__item');
        if (item) item.classList.add('is-active');
      }
    });
  } catch (e) {}

  /* Görünüme girince ortaya çıkma */
  var revealEls = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    Array.prototype.forEach.call(revealEls, function (el) { io.observe(el); });
  } else {
    Array.prototype.forEach.call(revealEls, function (el) { el.classList.add('is-visible'); });
  }

  /* Referans logoları kayan şerit */
  var marquee = document.querySelector('.marquee-inner');
  if (marquee && !marquee.dataset.ztBound) {
    marquee.dataset.ztBound = '1';
    marquee.innerHTML += marquee.innerHTML;
    var x = 0, speed = 0.6, paused = false;
    marquee.addEventListener('mouseenter', function () { paused = true; });
    marquee.addEventListener('mouseleave', function () { paused = false; });
    (function tick() {
      if (!paused) {
        x += speed;
        if (x >= marquee.scrollWidth / 2) x = 0;
        marquee.style.transform = 'translateX(-' + x + 'px)';
      }
      requestAnimationFrame(tick);
    })();
  }

  /* Yıl */
  Array.prototype.forEach.call(document.querySelectorAll('[data-year]'), function (el) {
    el.textContent = new Date().getFullYear();
  });
})();

/* Form POST yardımcısı — HostGator bot koruması POST isteklerinde "humans_XXXX" çerezi
   yoksa 409 + <script>document.cookie=...;reload()</script> döndürür. fetch bu betiği
   çalıştıramaz; çerezi biz kurup isteği bir kez yineleriz. JSON dışı yanıtlar okunur hataya çevrilir. */
window.ztPostForm = function (url, formData) {
  function send() {
    return fetch(url, { method: 'POST', body: formData, credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
  }
  function parse(response, retried) {
    return response.text().then(function (text) {
      var m = /document\.cookie\s*=\s*"([^"]+)"/.exec(text);
      if (response.status === 409 && m && !retried) {
        document.cookie = m[1] + '; path=/';
        return send().then(function (r) { return parse(r, true); });
      }
      try { return JSON.parse(text); }
      catch (e) { throw new Error('Sunucu beklenmedik bir yanıt döndürdü (HTTP ' + response.status + '). Lütfen sayfayı yenileyip tekrar deneyin.'); }
    });
  }
  return send().then(function (r) { return parse(r, false); });
};
