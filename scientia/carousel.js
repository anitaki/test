(function() {
    let startX = 0;
    let startY = 0;
    let prevTranslate = 0;
    let isDragging = false;
    let isScrolling = false;

    const track = document.getElementById('categoriesTrack');
    const container = track.parentElement;

    function getTrackLimits() {
        const trackWidth = track.scrollWidth;
        const containerWidth = container.offsetWidth;
        return trackWidth - containerWidth; // max scroll distance
    }

    function setTranslate(x) {
        track.style.transform = `translateX(-${x}px)`;
    }

    function updateButtons(currentTranslate) {
        const maxTranslate = getTrackLimits();
        const prevBtn = document.querySelector('.carousel-btn.prev');
        const nextBtn = document.querySelector('.carousel-btn.next');

        prevBtn.classList.toggle('hidden', currentTranslate <= 0);
        nextBtn.classList.toggle('hidden', currentTranslate >= maxTranslate);
    }

    // Buttons sliding by one card
    window.slideCategories = function(direction) {
        const card = track.querySelector('.category-card');
        const cardWidth = card.offsetWidth + 16; // card width + gap
        const maxTranslate = getTrackLimits();

        let translate = prevTranslate + direction * cardWidth;
        translate = Math.max(0, Math.min(translate, maxTranslate));

        prevTranslate = translate;
        setTranslate(translate);
        updateButtons(prevTranslate);
    };

    // Touch swipe
    function initTouch() {
        track.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isDragging = true;
            isScrolling = false;
        });

        track.addEventListener('touchmove', e => {
            if (!isDragging) return;

            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const diffX = startX - currentX;
            const diffY = startY - currentY;

            // Detect vertical scrolling
            if (!isScrolling) {
                if (Math.abs(diffY) > Math.abs(diffX)) {
                    isScrolling = true;
                    return; // vertical scroll → let browser handle
                }
            }
            if (isScrolling) return;

            // horizontal swipe → move carousel
            let translate = prevTranslate + diffX;
            translate = Math.max(0, Math.min(translate, getTrackLimits()));
            setTranslate(translate);
        });

        track.addEventListener('touchend', e => {
            if (!isDragging) return;
            isDragging = false;

            if (isScrolling) return; // vertical scroll → do nothing

            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;

            // Optional: snap if swipe distance > 50px
            const card = track.querySelector('.category-card');
            const cardWidth = card.offsetWidth + 16;

            if (Math.abs(diff) > 50) {
                if (diff > 0) { // swipe left
                    let translate = prevTranslate + cardWidth;
                    prevTranslate = Math.min(translate, getTrackLimits());
                } else { // swipe right
                    let translate = prevTranslate - cardWidth;
                    prevTranslate = Math.max(0, translate);
                }
            } else {
                // small swipe → stay at current position
            }

            setTranslate(prevTranslate);
            updateButtons(prevTranslate);
        });
    }

    // Reset carousel on resize
    function resetCarousel() {
        prevTranslate = 0;
        setTranslate(0);
        updateButtons(prevTranslate);
    }

    window.addEventListener('resize', resetCarousel);

    document.addEventListener('DOMContentLoaded', () => {
        initTouch();
        updateButtons(prevTranslate);
    });
})();
