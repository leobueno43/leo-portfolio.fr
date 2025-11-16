// ============================================
// SMOOTH SCROLLING (Already handled by CSS)
// ============================================

// ============================================
// CONTACT FORM HANDLING
// ============================================
function setupContactForm() {
    const contactForm = document.getElementById('contactForm');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const name = this.querySelector('input[type="text"]').value;
            const email = this.querySelector('input[type="email"]').value;
            const message = this.querySelector('textarea').value;

            // Validate form
            if (!name || !email || !message) {
                alert('Please fill in all fields');
                return;
            }

            // Here you would typically send the form data to a server
            // For now, we'll just show a success message
            showSuccessMessage();

            // Reset form
            this.reset();
        });
    }
}

function showSuccessMessage() {
    const message = document.createElement('div');
    message.textContent = 'Message sent successfully!';
    message.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #48bb78;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(message);

    // Remove message after 3 seconds
    setTimeout(() => {
        message.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => message.remove(), 300);
    }, 3000);
}

// ============================================
// SCROLL ANIMATIONS
// ============================================
function setupScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe project cards and skill categories
    document.querySelectorAll('.project-card, .skill-category, .stat').forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
}

// ============================================
// ACTIVE NAVIGATION LINK
// ============================================
function setupActiveNavLink() {
    const sections = document.querySelectorAll('section');
    const navItems = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href').slice(1) === current) {
                item.classList.add('active');
            }
        });
    });
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Press 'H' to go to home
        if (e.key === 'h' || e.key === 'H') {
            document.getElementById('home').scrollIntoView({ behavior: 'smooth' });
        }
        // Press 'C' to go to contact
        if (e.key === 'c' || e.key === 'C') {
            document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
        }
    });
}

// ============================================
// ADD CSS ANIMATIONS
// ============================================
function setupCSSAnimations() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .nav-link.active {
            color: var(--primary-color);
        }

        .nav-link.active::after {
            width: 100%;
        }
    `;
    document.head.appendChild(style);
}

// ============================================
// MOBILE MENU RESPONSIVE
// ============================================
function setupResponsiveMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const mediaQuery = window.matchMedia('(max-width: 768px)');
    
    function handleMediaChange(e) {
        if (e.matches) {
            // Mobile view
            if (navMenu) navMenu.style.display = 'none';
        } else {
            // Desktop view
            if (navMenu) navMenu.style.display = 'flex';
        }
    }

    mediaQuery.addEventListener('change', handleMediaChange);
    handleMediaChange(mediaQuery);
}

// ============================================
// SKILL PROGRESS INDICATORS
// ============================================
function setupSkillProgress() {
    // Add progress indicators to "Currently Learning" skills
    const learningSkills = {
        'PHP': 45,
        'MySQL': 40,
        'Angular': 30,
        'WordPress': 35,
        'Backend Development': 40,
        'Database Design': 35
    };
    
    // Find the "Currently Learning" category
    const learningCategory = Array.from(document.querySelectorAll('.skill-category h3'))
        .find(h3 => h3.textContent === 'Currently Learning')?.parentElement;
    
    if (learningCategory) {
        const skillItems = learningCategory.querySelectorAll('.skill-list li');
        
        skillItems.forEach(item => {
            const skillName = item.textContent;
            if (learningSkills[skillName]) {
                // Create progress bar
                const progressBar = document.createElement('div');
                progressBar.className = 'skill-progress';
                
                const progressIndicator = document.createElement('div');
                progressIndicator.className = 'progress-indicator';
                progressIndicator.style.width = `${learningSkills[skillName]}%`;
                
                const progressText = document.createElement('span');
                progressText.className = 'progress-text';
                progressText.textContent = `${learningSkills[skillName]}%`;
                
                progressBar.appendChild(progressIndicator);
                progressBar.appendChild(progressText);
                
                item.appendChild(progressBar);
            }
        });
    }
}

// ============================================
// THEME SWITCHER
// ============================================
function setupThemeSwitcher() {
    const themeSwitch = document.getElementById('themeSwitch');
    const themeIcon = themeSwitch.querySelector('.icon');
    
    // Check for saved theme preference or use device preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set initial theme
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
        themeIcon.textContent = '🌙';
    }
    
    // Toggle theme on click
    themeSwitch.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        
        if (currentTheme === 'dark') {
            // Switch to light theme
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            themeIcon.textContent = '☀️';
            
            // Add transition animation
            document.documentElement.style.transition = 'all 0.5s ease';
        } else {
            // Switch to dark theme
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            themeIcon.textContent = '🌙';
            
            // Add transition animation
            document.documentElement.style.transition = 'all 0.5s ease';
        }
        
        // Remove transition after theme change to prevent issues with other transitions
        setTimeout(() => {
            document.documentElement.style.transition = '';
        }, 500);
    });
}

// ============================================
// PAGE LOADING ANIMATION
// ============================================
function setupPageLoadAnimation() {
    // Create loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="loader"></div>
    `;
    document.body.appendChild(loadingOverlay);
    
    // Add CSS for loading animation
    const loadingStyle = document.createElement('style');
    loadingStyle.textContent = `
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--bg-white);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid var(--bg-light);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(loadingStyle);
    
    // Hide loading overlay after page loads
    setTimeout(() => {
        loadingOverlay.style.opacity = '0';
        loadingOverlay.style.visibility = 'hidden';
        
        // Remove overlay after animation completes
        setTimeout(() => {
            loadingOverlay.remove();
        }, 500);
    }, 1000);
}

// ============================================
// INITIALIZE ON LOAD
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('Portfolio loaded successfully!');
    
    // ============================================
    // HAMBURGER MENU FUNCTIONALITY
    // ============================================
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

    // Close mobile menu overlay
    function closeMobileMenu() {
        mobileMenuOverlay.classList.remove('active');
        hamburger.classList.remove('active');
        document.body.style.overflow = 'auto'; // Allow body scroll
    }

    // Open mobile menu overlay
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            mobileMenuOverlay.classList.add('active');
            hamburger.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent body scroll
        });
    }

    if (closeMenuBtn) {
        closeMenuBtn.addEventListener('click', closeMobileMenu);
    }

    // Close menu when overlay background is clicked
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMobileMenu();
            }
        });
    }

    // Close menu when a mobile nav link is clicked
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });

    // Close menu when a desktop nav link is clicked (only on mobile)
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Only hide menu if we're in mobile view (hamburger is visible)
            if (window.innerWidth <= 768) {
                navMenu.style.display = 'none';
                hamburger.classList.remove('active');
            }
        });
    });

    setupPageLoadAnimation();
    setupCSSAnimations();
    setupContactForm();
    setupScrollAnimations();
    setupActiveNavLink();
    setupKeyboardShortcuts();
    setupResponsiveMenu();
    setupSkillProgress();
    setupThemeSwitcher();
    
    // Add image hover animations
    document.querySelectorAll('.project-image').forEach(image => {
        image.addEventListener('mouseenter', function() {
            this.querySelector('img').style.transform = 'scale(1.1) rotate(2deg)';
        });
        
        image.addEventListener('mouseleave', function() {
            this.querySelector('img').style.transform = 'scale(1) rotate(0deg)';
        });
    });
});