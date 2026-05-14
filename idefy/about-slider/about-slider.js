(function () {
  const slider = document.querySelector('.svc-slider');
  if (!slider) return;

  const track = slider.querySelector('.svc-track');
  const cards = Array.from(slider.querySelectorAll('.svc-card'));
  if (!cards.length) return;

  const ROTATE_MS = 5000;

  let activeIdx = cards.findIndex(c => c.classList.contains('is-active'));
  if (activeIdx < 0) activeIdx = 0;

  const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

  const setActive = (idx) => {
    if (idx === activeIdx) return;
    cards.forEach((c, i) => c.classList.toggle('is-active', i === idx));
    activeIdx = idx;

    if (isMobile() && track) {
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

  slider.addEventListener('mouseenter', stopRotate);
  slider.addEventListener('mouseleave', startRotate);

  // Clicking anywhere on an inactive card (title, "+" or anywhere else) activates it
  cards.forEach((card, i) => {
    card.addEventListener('click', () => {
      if (!card.classList.contains('is-active')) {
        setActive(i);
        startRotate();
      }
    });
  });

  // Pause auto-rotate while touching
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

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopRotate(); else startRotate();
  });
})();
