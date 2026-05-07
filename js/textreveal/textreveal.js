document.addEventListener('DOMContentLoaded', () => {
  const triggerElement = document.querySelector('.about-us-first-row');
  const targetElement = document.querySelector('.reveal-clip');

  if (triggerElement && targetElement) {
    // Flag to track if the animation has been triggered by hover
    let animationTriggeredByHover = false;

    // --- Hover event listeners ---
    triggerElement.addEventListener('mouseenter', () => {
      // 1. Ensure animation resets by removing the class
      targetElement.classList.remove('animate-on-hover');
      void targetElement.offsetWidth; // Force reflow
      // 2. Add the class to start the animation and set the flag
      targetElement.classList.add('animate-on-hover');
      animationTriggeredByHover = true;
    });

    // We no longer remove the class on mouseleave, allowing 'forwards' to keep the state
    // If you need the animation to reverse or do something else on mouseleave *after* it has played,
    // you would add more complex logic here, possibly using animationend event listener.

    // --- Intersection Observer to handle scrolling away ---
    const observerOptions = {
      root: null, // Use the viewport as the root
      rootMargin: '0px',
      threshold: 0 // Trigger when any part of the element becomes visible/invisible
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        // If the element was revealed by hover and is now out of view (or significantly out of view)
        if (animationTriggeredByHover && !entry.isIntersecting && entry.boundingClientRect.top > window.innerHeight || entry.boundingClientRect.bottom < 0) {
          // Remove the class to reset the animation to its initial state
          targetElement.classList.remove('animate-on-hover');
          animationTriggeredByHover = false; // Reset the flag
        }
      });
    }, observerOptions);

    observer.observe(targetElement); // Tell the observer to watch the targetElement
  }
});
