document.addEventListener('DOMContentLoaded', () => {
    // Search bar focus effect
    const searchInput = document.getElementById('game-search');
    const searchIcon = document.querySelector('.search-input i');
    const searchResults = document.getElementById('search-results-container');

    if (searchInput && searchIcon) {
        let debounceTimer;

        searchInput.addEventListener('focus', () => {
            searchIcon.style.color = '#fff';
            if (searchResults.innerHTML.trim() !== '') {
                searchResults.classList.add('active');
            }
        });

        searchInput.addEventListener('blur', () => {
            searchIcon.style.color = '#555';
            // Delay closing to allow clicking on results
            setTimeout(() => {
                searchResults.classList.remove('active');
            }, 200);
        });

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.trim();

            clearTimeout(debounceTimer);

            if (term.length < 3) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('active');
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const body = new URLSearchParams();
                    body.set('action', 'gamestore_ajax_search');
                    body.set('nonce', window.gamestoreAjax.nonce);
                    body.set('term', term);

                    const res = await fetch(window.gamestoreAjax.ajaxUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        },
                        body: body.toString()
                    });

                    const data = await res.json();
                    if (data.success && data.data.html) {
                        searchResults.innerHTML = data.data.html;
                        searchResults.classList.add('active');
                    } else if (data.success) {
                        searchResults.innerHTML = '<div class="no-results">Ничего не найдено</div>';
                        searchResults.classList.add('active');
                    }
                } catch (error) {
                    console.error('Search error:', error);
                }
            }, 300);
        });
    }

    // Login Modal
    const loginModal = document.getElementById('login-modal');
    const openLoginBtns = document.querySelectorAll('.open-login-modal');
    const closeLoginBtn = document.querySelector('.close-modal');

    if (loginModal && openLoginBtns.length > 0) {
        openLoginBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                loginModal.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
        });

        if (closeLoginBtn) {
            closeLoginBtn.addEventListener('click', () => {
                loginModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        }

        window.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                loginModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Smooth scroll for links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile Menu Toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const menuContainer = document.querySelector('.main-menu-container');

    if (menuToggle && menuContainer) {
        menuToggle.addEventListener('click', () => {
            const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !expanded);
            menuToggle.classList.toggle('active');
            menuContainer.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });

        // Close menu on link click
        menuContainer.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.classList.remove('active');
                menuContainer.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });
    }

    // Hero slider
    const slides = document.querySelectorAll('.hero-slider .slide');
    const dots = document.querySelectorAll('.hero-slider .dot');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');

    if (slides.length > 0 && dots.length > 0) {
        let currentSlide = 0;

        const goToSlide = (index) => {
            // Handle wrap around
            if (index >= slides.length) index = 0;
            if (index < 0) index = slides.length - 1;
            
            currentSlide = index;

            // Remove active class from all slides and dots
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));

            // Add active class to current slide and dot
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        };

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                goToSlide(currentSlide - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                goToSlide(currentSlide + 1);
            });
        }

        // Optional: Auto-slide
        const autoSlide = () => {
            goToSlide(currentSlide + 1);
        };

        let slideInterval = setInterval(autoSlide, 5000);

        // Pause on hover
        const slider = document.querySelector('.hero-slider');
        slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
        slider.addEventListener('mouseleave', () => slideInterval = setInterval(autoSlide, 5000));
    }
});
