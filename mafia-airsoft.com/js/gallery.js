// Galerie - Carrousel automatique
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.gallery-carousel');
    if (!carousel) return;
    
    const track = carousel.querySelector('.carousel-track');
    const slides = Array.from(track.children);
    const nextButton = carousel.querySelector('.carousel-btn-next');
    const prevButton = carousel.querySelector('.carousel-btn-prev');
    const dotsNav = carousel.querySelector('.carousel-nav');
    const dots = Array.from(dotsNav.children);
    
    let currentIndex = 0;
    let autoplayInterval;
    const autoplayDelay = 30000; // 30 secondes
    
    // Fonction pour déplacer le carrousel
    const moveToSlide = (targetIndex) => {
        const slideWidth = slides[0].getBoundingClientRect().width;
        track.style.transform = 'translateX(-' + slideWidth * targetIndex + 'px)';
        currentIndex = targetIndex;
        
        // Mettre à jour les dots
        dots.forEach(dot => dot.classList.remove('active'));
        dots[targetIndex].classList.add('active');
    };
    
    // Bouton suivant
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
            resetAutoplay();
        });
    }
    
    // Bouton précédent
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
            moveToSlide(prevIndex);
            resetAutoplay();
        });
    }
    
    // Navigation par dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            moveToSlide(index);
            resetAutoplay();
        });
    });
    
    // Autoplay
    const startAutoplay = () => {
        autoplayInterval = setInterval(() => {
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
        }, autoplayDelay);
    };
    
    const stopAutoplay = () => {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
        }
    };
    
    const resetAutoplay = () => {
        stopAutoplay();
        startAutoplay();
    };
    
    // Pause au survol
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    
    // Touch/swipe support pour mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    const handleSwipe = () => {
        if (touchEndX < touchStartX - 50) {
            // Swipe left
            const nextIndex = (currentIndex + 1) % slides.length;
            moveToSlide(nextIndex);
            resetAutoplay();
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right
            const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
            moveToSlide(prevIndex);
            resetAutoplay();
        }
    };
    
    // Responsive : recalculer les positions au resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            moveToSlide(currentIndex);
        }, 250);
    });
    
    // Démarrer l'autoplay
    if (slides.length > 1) {
        startAutoplay();
    }
});
