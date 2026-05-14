(function () {
  const slider = document.querySelector('.stats-slider');
  if (!slider) return;

  // iOS Safari cannot reliably render preserve-3d / backface-visibility when
  // overflow:hidden exists in the ancestor chain. Flag it so CSS uses a fade instead.
  const isIOS = /iP(ad|hone|od)/.test(navigator.userAgent) ||
    (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  if (isIOS) slider.classList.add('is-ios');

  const track = slider.querySelector('.stats-track');
  const cards = Array.from(slider.querySelectorAll('.stat-card'));
  if (!cards.length) return;

  const RING_CIRCUMFERENCE = 283;
  const ROTATE_MS = 4500;
  const COUNT_DURATION = 1800;

  let activeIdx = cards.findIndex(c => c.classList.contains('is-active'));
  if (activeIdx < 0) activeIdx = 0;

  // Set per-card ring offset based on data-progress (% of circle to draw).
  cards.forEach(card => {
    const progress = Math.max(0, Math.min(100, +card.dataset.progress || 0));
    const offset = RING_CIRCUMFERENCE * (1 - progress / 100);
    card.style.setProperty('--ring-offset', offset);
  });

  // ---- Counter formatting ---------------------------------------------------
  const formatNumber = (n) => {
    if (n >= 1000) {
      // 1200 -> "1.200" (period as thousand separator, matching the design)
      return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    return n.toString();
  };

  // ---- Counter animation ----------------------------------------------------
  const animateCounter = (card) => {
    const target = +card.dataset.target || 0;
    const suffix = card.dataset.suffix || '';
    const counters = card.querySelectorAll('.counter');
    const start = performance.now();

    const tick = (now) => {
      const elapsed = now - start;
      const t = Math.min(elapsed / COUNT_DURATION, 1);
      const eased = 1 - Math.pow(1 - t, 3);
      const value = Math.floor(target * eased);
      const display = formatNumber(value) + (t === 1 ? suffix : '');
      counters.forEach(el => { el.textContent = display; });
      if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };

  // ---- Reveal on scroll (counter + ring draw) -------------------------------
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const card = entry.target;
      card.classList.add('is-revealed');
      animateCounter(card);
      revealObserver.unobserve(card);
    });
  }, { threshold: 0.25 });

  cards.forEach(c => revealObserver.observe(c));

  // ---- Active card switching ------------------------------------------------
  const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

  const setActive = (idx, opts = {}) => {
    if (idx === activeIdx) return;
    cards.forEach((c, i) => c.classList.toggle('is-active', i === idx));
    activeIdx = idx;

    if (isMobile() && !opts.skipScroll && track) {
      const card = cards[idx];
      const offset = card.offsetLeft - (track.clientWidth - card.clientWidth) / 2;
      track.scrollTo({ left: offset, behavior: 'smooth' });
    }
  };

  // ---- Auto-rotate ----------------------------------------------------------
  let rotateTimer = null;
  const startRotate = () => {
    stopRotate();
    rotateTimer = setInterval(() => {
      setActive((activeIdx + 1) % cards.length);
    }, ROTATE_MS);
  };
  const stopRotate = () => {
    if (rotateTimer) { clearInterval(rotateTimer); rotateTimer = null; }
  };

  startRotate();

  // Pause on hover (desktop)
  slider.addEventListener('mouseenter', stopRotate);
  slider.addEventListener('mouseleave', startRotate);

  // Click to activate + restart rotation
  cards.forEach((card, i) => {
    card.addEventListener('click', () => {
      setActive(i);
      startRotate();
    });
  });

  // Pause auto-rotate while user is touching/swiping the carousel
  let touchPauseTimer = null;
  ['touchstart', 'pointerdown'].forEach(evt => {
    track && track.addEventListener(evt, () => {
      stopRotate();
      clearTimeout(touchPauseTimer);
    }, { passive: true });
  });
  ['touchend', 'pointerup', 'pointercancel'].forEach(evt => {
    track && track.addEventListener(evt, () => {
      clearTimeout(touchPauseTimer);
      touchPauseTimer = setTimeout(startRotate, 2500);
    }, { passive: true });
  });

  // Pause when tab is hidden
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopRotate(); else startRotate();
  });
})();
