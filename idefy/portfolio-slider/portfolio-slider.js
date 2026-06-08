(function () {
  const slider = document.querySelector('.reel-slider');
  if (!slider) return;

  const track   = slider.querySelector('.reel-track');
  const wrap    = slider.querySelector('.reel-track-wrap');
  const slides  = Array.from(track.querySelectorAll('.reel-slide'));
  const prevBtn = slider.querySelector('.reel-arrow--prev');
  const nextBtn = slider.querySelector('.reel-arrow--next');
  const GAP     = 16;

  let currentIdx = 0;

  const visibleCount = () => window.innerWidth <= 768 ? 1 : 3;

  const slideWidth = () => {
    const n = visibleCount();
    return (wrap.clientWidth - (n - 1) * GAP) / n;
  };

  const updateWidths = () => {
    const w = slideWidth();
    slides.forEach(s => { s.style.width = w + 'px'; });
  };

  const goTo = (idx) => {
    const max = Math.max(0, slides.length - visibleCount());
    currentIdx = Math.max(0, Math.min(idx, max));
    track.style.transform = `translateX(-${currentIdx * (slideWidth() + GAP)}px)`;
    prevBtn.disabled = currentIdx === 0;
    nextBtn.disabled = currentIdx >= max;
  };

  // ---- Play button: swap cover for Instagram iframe -----------------
  slides.forEach(slide => {
    const btn = slide.querySelector('.reel-play-btn');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const embedUrl = slide.dataset.embed + '?autoplay=1';
      const coverWrap = slide.querySelector('.reel-cover-wrap');
      coverWrap.innerHTML =
        `<iframe src="${embedUrl}"
                 frameborder="0" scrolling="no"
                 allowtransparency="true"
                 allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                 style="width:100%;aspect-ratio:9/16;border:none;display:block;">
         </iframe>`;
    });
  });

  // ---- Arrows -------------------------------------------------------
  prevBtn.addEventListener('click', () => goTo(currentIdx - 1));
  nextBtn.addEventListener('click', () => goTo(currentIdx + 1));

  // ---- Touch / swipe ------------------------------------------------
  let touchStartX = 0;
  track.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });
  track.addEventListener('touchend', e => {
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) goTo(currentIdx + (diff > 0 ? 1 : -1));
  }, { passive: true });

  // ---- Resize -------------------------------------------------------
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      updateWidths();
      goTo(Math.min(currentIdx, slides.length - visibleCount()));
    }, 120);
  });

  // ---- Init ---------------------------------------------------------
  updateWidths();
  goTo(0);
})();
