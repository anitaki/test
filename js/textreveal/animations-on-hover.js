document.addEventListener('DOMContentLoaded', () => {
  const triggerElement = document.querySelector('.about-us-first-row');
  const targetElement = document.querySelector('.reveal-clip');
  const circleElement = document.querySelector('.reveal-circle');
  
  if (triggerElement && (targetElement || circleElement)) {
    // Flag to track if the animation has been triggered by hover
    let animationTriggeredByHover = false;
    
    // --- Hover event listeners ---
    triggerElement.addEventListener('mouseenter', () => {
      // Handle main target element animation if it exists
      if (targetElement) {
        // 1. Ensure animation resets by removing the class
        targetElement.classList.remove('animate-on-hover');
        void targetElement.offsetWidth; // Force reflow
        
        // 2. Add the class to start the animation
        targetElement.classList.add('animate-on-hover');
      }
      
      // Handle circle animation if element exists
      if (circleElement) {
        circleElement.classList.remove('animate-circle-hover');
        void circleElement.offsetWidth; // Force reflow
        circleElement.classList.add('animate-circle-hover');
      }
      
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
        if (animationTriggeredByHover && !entry.isIntersecting && 
            (entry.boundingClientRect.top > window.innerHeight || entry.boundingClientRect.bottom < 0)) {
          
          // Reset main target element animation if it exists
          if (targetElement) {
            targetElement.classList.remove('animate-on-hover');
          }
          
          // Reset circle animation if element exists
          if (circleElement) {
            circleElement.classList.remove('animate-circle-hover');
          }
          
          animationTriggeredByHover = false; // Reset the flag
        }
      });
    }, observerOptions);
    
    // Observe whichever element exists (prioritize targetElement, fallback to circleElement)
    const elementToObserve = targetElement || circleElement;
    observer.observe(elementToObserve);
  }
});