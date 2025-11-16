/* ============================================
   ODYSSEY BEYOND THE SPACE - JAVASCRIPT
   Modern Interactive Features & Sound Management
   ============================================ */

// Global audio object for persistent music across pages
window.ambianceAudio = null;

// Initialize persistent ambiance audio
function initializeAmbianceAudio() {
  if (!window.ambianceAudio) {
    try {
      window.ambianceAudio = new Audio('song/ambiance.mp3');
      window.ambianceAudio.loop = true;
      window.ambianceAudio.volume = 0.3;
    } catch (e) {
      window.ambianceAudio = null;
    }
  }
  return window.ambianceAudio;
}

// Sound files with error handling
const sounds = {
  ambiance: initializeAmbianceAudio(),
  click: (() => {
    try {
      const audio = new Audio('song/clic.mp3');
      audio.volume = 0.01;
      return audio;
    } catch (e) {
      return null;
    }
  })(),
  rocket: (() => {
    try {
      const audio = new Audio('song/fusee.mp3');
      audio.volume = 0.7;
      return audio;
    } catch (e) {
      return null;
    }
  })()
};

// Save music state before navigation
window.addEventListener('beforeunload', () => {
  if (window.ambianceAudio && !window.ambianceAudio.paused) {
    sessionStorage.setItem('musicPlaying', 'true');
    sessionStorage.setItem('musicTime', window.ambianceAudio.currentTime);
  }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  initializeNavigation();
  resumeAmbianceMusic();
  setupEventListeners();
  setActiveNavLink();
  addScrollAnimations();
});

// ============ NAVIGATION ============
function initializeNavigation() {
  const links = document.querySelectorAll('nav a');
  links.forEach(link => {
    link.addEventListener('click', (e) => {
      playClickSound();
    });
  });
}

function setActiveNavLink() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('nav a');
  
  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

// ============ SOUND MANAGEMENT ============
function playAmbiance() {
  if (sounds.ambiance && sounds.ambiance.paused) {
    sounds.ambiance.play().catch(e => {
      // Autoplay might be prevented - that's okay
    });
    sessionStorage.setItem('musicPlaying', 'true');
  }
}

function resumeAmbianceMusic() {
  // Resume music if it was playing on previous page
  const wasMusicPlaying = sessionStorage.getItem('musicPlaying') === 'true';
  const lastTime = parseFloat(sessionStorage.getItem('musicTime')) || 0;
  
  if (wasMusicPlaying && sounds.ambiance) {
    try {
      sounds.ambiance.currentTime = lastTime;
      sounds.ambiance.play().catch(e => {
        // Autoplay might be prevented on browser tab switch
      });
      sessionStorage.setItem('musicPlaying', 'true');
    } catch (e) {
      console.log('Could not resume music:', e);
    }
  }
}

function stopAmbiance() {
  if (sounds.ambiance && !sounds.ambiance.paused) {
    sounds.ambiance.pause();
    sessionStorage.setItem('musicPlaying', 'false');
  }
}

function playClickSound() {
  if (sounds.click) {
    sounds.click.currentTime = 0;
    sounds.click.play().catch(e => {});
  }
}

function playRocketSound() {
  if (sounds.rocket) {
    sounds.rocket.currentTime = 0;
    sounds.rocket.play().catch(e => {});
  }
}

// ============ EVENT LISTENERS ============
function setupEventListeners() {
  // Launch button
  const launchBtn = document.getElementById('launch-btn');
  if (launchBtn) {
    launchBtn.addEventListener('click', () => {
      playRocketSound();
      launchAnimation();
    });
  }

  // Submit button
  const submitBtn = document.getElementById('submit-btn');
  if (submitBtn) {
    submitBtn.addEventListener('click', (e) => {
      e.preventDefault();
      handleFormSubmit();
    });
  }

  // All buttons for click sound
  const buttons = document.querySelectorAll('.btn, .launch-btn, .submit-btn');
  buttons.forEach(btn => {
    btn.addEventListener('click', playClickSound);
  });

  // Gallery items
  const galleryItems = document.querySelectorAll('.gallery-item');
  galleryItems.forEach(item => {
    item.addEventListener('click', playClickSound);
  });
}

// ============ LAUNCH ANIMATION ============
function launchAnimation() {
  const body = document.body;
  body.style.animation = 'shake 0.5s ease-in-out';
  
  setTimeout(() => {
    body.style.animation = '';
  }, 500);
}

// Add shake animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }
`;
document.head.appendChild(style);

// ============ FORM SUBMISSION ============
function handleFormSubmit() {
  const form = document.querySelector('form');
  if (!form) return;

  const name = document.querySelector('input[type="text"]').value;
  const email = document.querySelector('input[type="email"]').value;
  const message = document.querySelector('textarea').value;

  if (name && email && message) {
    playClickSound();
    alert(`🚀 Message received, ${name}!\n\nThank you for joining our mission!\nWe'll contact you at: ${email}`);
    form.reset();
  } else {
    alert('Please fill in all fields before sending your message!');
  }
}

// ============ PAGE INTERACTIONS ============
// Add hover effects to cards
document.addEventListener('DOMContentLoaded', () => {
  const cards = document.querySelectorAll('.card, .planet-card, .mission-card');
  cards.forEach(card => {
    card.addEventListener('mouseenter', playClickSound);
  });
});

// Smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
      playClickSound();
    }
  });
});

// ============ SCROLL ANIMATIONS ============
function addScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, observerOptions);

  document.querySelectorAll('.card, .planet-card, .mission-card, .gallery-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
    observer.observe(el);
  });
}

// ============ ACCESSIBILITY ============
// Add keyboard support
document.addEventListener('keydown', (e) => {
  // Mute sound with 'M' key
  if (e.key.toLowerCase() === 'm') {
    if (sounds.ambiance) {
      if (sounds.ambiance.paused) {
        playAmbiance();
        console.log('🔊 Ambiance enabled');
      } else {
        stopAmbiance();
        console.log('🔇 Ambiance muted');
      }
    }
  }
});

// Handle tab visibility - pause/resume music when switching tabs
document.addEventListener('visibilitychange', () => {
  if (sounds.ambiance) {
    if (document.hidden) {
      // Tab is hidden - pause music
      if (!sounds.ambiance.paused) {
        sounds.ambiance.pause();
        sessionStorage.setItem('wasPlayingBeforeFocus', 'true');
      }
    } else {
      // Tab is visible - resume music if it was playing
      if (sessionStorage.getItem('wasPlayingBeforeFocus') === 'true') {
        sounds.ambiance.play().catch(e => {
          // Autoplay might be prevented
        });
        sessionStorage.setItem('wasPlayingBeforeFocus', 'false');
      }
    }
  }
});

// ============ PERFORMANCE OPTIMIZATION ============
// Lazy load images
if ('IntersectionObserver' in window) {
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.style.opacity = '1';
        observer.unobserve(img);
      }
    });
  });

  document.querySelectorAll('img').forEach(img => {
    imageObserver.observe(img);
  });
}

// ============ MOBILE MENU SUPPORT ============
function setupMobileMenu() {
  // Detect if on mobile and add any mobile-specific interactions
  const isMobile = window.innerWidth <= 768;
  if (isMobile) {
    document.querySelectorAll('nav a').forEach(link => {
      link.addEventListener('click', () => {
        playClickSound();
      });
    });
  }
}

setupMobileMenu();

// Handle window resize
window.addEventListener('resize', () => {
  setupMobileMenu();
});

// Log when page is ready
console.log('🚀 Odyssey Beyond the Space - Modern Design Ready!');
console.log('💡 Press M to toggle ambiance music');
console.log('✨ Smooth animations and modern interactions enabled');