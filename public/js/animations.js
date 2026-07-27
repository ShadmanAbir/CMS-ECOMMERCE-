/**
 * Shared Animations & Parallax Engine
 * Scroll-triggered reveals, parallax backgrounds, particles
 * (Theme-agnostic — brand colors handled via CSS custom properties)
 */
(function() {
    'use strict';

    // === PAGE LOADER ===
    window.addEventListener('load', function() {
        const loader = document.querySelector('.page-loader');
        if (loader) {
            setTimeout(() => loader.classList.add('loaded'), 300);
        }
    });

    // === SCROLL REVEAL (Intersection Observer) ===
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children');
        
        if (!revealElements.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => observer.observe(el));
    }

    // === PARALLAX SCROLL ENGINE ===
    function initParallax() {
        const parallaxBgs = document.querySelectorAll('.parallax-bg, .hero-bg');
        const contentLayers = document.querySelectorAll('[data-parallax-speed]');
        let ticking = false;

        function updateParallax() {
            const scrollY = window.pageYOffset;
            const windowHeight = window.innerHeight;

            parallaxBgs.forEach(bg => {
                const section = bg.closest('.parallax-section, .hero-parallax, .parallax-divider');
                if (!section) return;
                const rect = section.getBoundingClientRect();
                if (rect.bottom < -200 || rect.top > windowHeight + 200) return;
                const sectionTop = rect.top + scrollY;
                const speed = bg.classList.contains('hero-bg') ? 0.5 : 0.35;
                bg.style.transform = `translate3d(0, ${(scrollY - sectionTop) * speed}px, 0)`;
            });

            contentLayers.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.bottom < -100 || rect.top > windowHeight + 100) return;
                const speed = parseFloat(el.dataset.parallaxSpeed) || 0.2;
                const progress = (windowHeight - rect.top) / (windowHeight + rect.height);
                el.style.transform = `translate3d(0, ${(progress - 0.5) * rect.height * speed}px, 0)`;
            });

            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) { requestAnimationFrame(updateParallax); ticking = true; }
        }, { passive: true });
        updateParallax();
    }

    // === SCROLL-BASED SECTION EFFECTS ===
    function initScrollSectionEffects() {
        let ticking = false;

        function updateSectionEffects() {
            const windowHeight = window.innerHeight;
            const scrollY = window.pageYOffset;

            // Parallax divider fade
            document.querySelectorAll('.parallax-divider').forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.bottom < -50 || rect.top > windowHeight + 50) return;
                const distance = Math.abs((rect.top + rect.height / 2) - windowHeight / 2) / windowHeight;
                el.style.opacity = Math.max(0.3, 1 - distance * 0.5);
            });

            // Marquee speed boost on scroll
            const marquees = document.querySelectorAll('.marquee-track');
            if (marquees.length) {
                const scrollSpeed = Math.abs(scrollY - (window._lastScrollY || 0));
                const speedBoost = Math.min(scrollSpeed * 0.5, 3);
                marquees.forEach(m => {
                    const baseDuration = parseFloat(m.dataset.baseDuration) || 20;
                    m.style.animationDuration = (baseDuration * Math.max(0.5, 1 - (speedBoost / 30))) + 's';
                });
                window._lastScrollY = scrollY;
            }

            // Product cards subtle 3D tilt
            document.querySelectorAll('.popular-product-card').forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.bottom < 0 || rect.top > windowHeight) return;
                const progress = (windowHeight - rect.top) / (windowHeight + rect.height);
                card.style.transform = `perspective(800px) rotateX(${(progress - 0.5) * 4}deg)`;
            });

            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) { requestAnimationFrame(updateSectionEffects); ticking = true; }
        }, { passive: true });
        updateSectionEffects();
    }

    // === FLOATING PARTICLES ===
    function initParticles() {
        const containers = document.querySelectorAll('.particles-container');
        
        containers.forEach(container => {
            const particleCount = 15;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                const size = Math.random() * 3 + 1;
                const left = Math.random() * 100;
                const duration = Math.random() * 15 + 10;
                const delay = Math.random() * 10;
                const opacity = Math.random() * 0.5 + 0.2;
                
                particle.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${left}%;
                    animation-duration: ${duration}s;
                    animation-delay: -${delay}s;
                    opacity: ${opacity};
                `;
                
                container.appendChild(particle);
            }
        });
    }

    // === MAGNETIC HOVER EFFECT ===
    function initMagneticButtons() {
        document.querySelectorAll('[data-magnetic]').forEach(btn => {
            btn.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                this.style.transform = `translate(${(e.clientX - rect.left - rect.width / 2) * 0.15}px, ${(e.clientY - rect.top - rect.height / 2) * 0.15}px)`;
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transition = 'transform 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
                this.style.transform = 'translate(0, 0)';
            });
            btn.addEventListener('mouseenter', function() { this.style.transition = 'none'; });
        });
    }

    // === ADD TO CART (Global) ===
    window.addToCart = function(productId) {
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in header
                const cartCountEls = document.querySelectorAll('a[href*="cart"] span');
                cartCountEls.forEach(el => {
                    if (data.cart_count !== undefined) el.textContent = data.cart_count;
                });
                // Brief visual feedback
                const btn = event && event.target ? event.target.closest('button') : null;
                if (btn) {
                    const orig = btn.textContent;
                    btn.textContent = 'Added!';
                    btn.classList.add('bg-green-500');
                    setTimeout(() => { btn.textContent = orig; btn.classList.remove('bg-green-500'); }, 1200);
                }
            } else {
                alert(data.message || 'Could not add item to cart.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
        });
    };

    // === INITIALIZE ALL ===
    function init() {
        initScrollReveal();
        initParallax();
        initScrollSectionEffects();
        initParticles();
        initMagneticButtons();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
