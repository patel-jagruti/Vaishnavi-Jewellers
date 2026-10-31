(() => {
  'use strict';

  // =============================================
  // HERO SLIDER - Auto-slide with manual controls
  // =============================================
  const heroSlider = document.querySelector('[data-slider]');
  if (heroSlider) {
    const slides = heroSlider.querySelectorAll('.slide');
    const dots = heroSlider.querySelectorAll('.dot');
    const prevBtn = heroSlider.querySelector('.slide-prev');
    const nextBtn = heroSlider.querySelector('.slide-next');
    let currentSlide = 0;
    let autoSlideInterval = null;
    const SLIDE_DURATION = 6000;
    const TRANSITION_DURATION = 600;

    const showSlide = (index, isAuto = false) => {
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
        slide.setAttribute('aria-hidden', i !== index);
      });
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === index);
        dot.setAttribute('aria-selected', i === index);
      });
      currentSlide = index;
    };

    const nextSlide = () => {
      showSlide((currentSlide + 1) % slides.length);
    };

    const prevSlide = () => {
      showSlide((currentSlide - 1 + slides.length) % slides.length);
    };

    const goToSlide = (index) => {
      showSlide(index);
      resetAutoSlide();
    };

    const startAutoSlide = () => {
      stopAutoSlide();
      autoSlideInterval = setInterval(nextSlide, SLIDE_DURATION);
    };

    const stopAutoSlide = () => {
      if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
        autoSlideInterval = null;
      }
    };

    const resetAutoSlide = () => {
      startAutoSlide();
    };

    // Event listeners
    if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); if (!isAuto) resetAutoSlide(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); if (!isAuto) resetAutoSlide(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => goToSlide(i)));

    // Pause on hover
    heroSlider.addEventListener('mouseenter', stopAutoSlide);
    heroSlider.addEventListener('mouseleave', startAutoSlide);

    // Keyboard navigation
    heroSlider.setAttribute('tabindex', '0');
    heroSlider.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { prevSlide(); resetAutoSlide(); }
      if (e.key === 'ArrowRight') { nextSlide(); resetAutoSlide(); }
    });

    // Start auto-slide
    startAutoSlide();
  }

  // =============================================
  // FADE-IN ANIMATIONS
  // =============================================
  const items = Array.from(document.querySelectorAll('[data-fade-in]'));
  if (items.length) {
    if (!('IntersectionObserver' in window)) {
      items.forEach((el) => el.classList.add('is-visible'));
    } else {
      const io = new IntersectionObserver(
        (entries) => {
          for (const e of entries) {
            if (e.isIntersecting) {
              e.target.classList.add('is-visible');
              io.unobserve(e.target);
            }
          }
        },
        { threshold: 0.12, rootMargin: '0px 0px -50px 0px' }
      );
      items.forEach((el) => io.observe(el));
    }
  }

  // =============================================
  // PRODUCT CARD HOVER EFFECTS
  // =============================================
  const productCards = document.querySelectorAll('.product-card');
  productCards.forEach(card => {
    card.addEventListener('mouseenter', () => card.classList.add('is-hovered'));
    card.addEventListener('mouseleave', () => card.classList.remove('is-hovered'));
  });

  // =============================================
  // SMOOTH SCROLL FOR ANCHOR LINKS
  // =============================================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // =============================================
  // NEWSLETTER FORM
  // =============================================
  const form = document.querySelector('[data-newsletter-form]');
  if (form) {
    const showToast = (msg) => {
      const el = document.createElement('div');
      el.className = 'toast';
      el.textContent = msg;
      document.body.appendChild(el);
      requestAnimationFrame(() => el.classList.add('show'));
      setTimeout(() => {
        el.classList.remove('show');
        setTimeout(() => el.remove(), 250);
      }, 2300);
    };

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const emailInput = form.querySelector('input[type="email"]');
      const email = (emailInput?.value || '').trim();
      if (!email) return;

      const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      if (!ok) {
        showToast('Enter a valid email');
        emailInput?.focus();
        return;
      }

      showToast('Thank you! You are subscribed.');
      emailInput.value = '';
    });
  }

  // =============================================
  // SEARCHBOX SUGGESTIONS (placeholder)
  // =============================================
  const searchInput = document.querySelector('[data-search]');
  if (searchInput) {
    // Add search debounce and suggestions placeholder
    let searchTimeout = null;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      const query = e.target.value.trim();
      if (query.length < 2) return;
      searchTimeout = setTimeout(() => {
        // Placeholder: fetch suggestions from API
        // loadSuggestions(query);
      }, 300);
    });
  }

  // =============================================
  // QUICK VIEW MODAL (placeholder)
  // =============================================
  const quickViewBtns = document.querySelectorAll('[data-quick-view]');
  quickViewBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const productId = btn.dataset.productId;
      // Placeholder: open quick view modal
      // openQuickView(productId);
    });
  });

  // Conflicting mock handlers for wishlist and cart toggles have been removed.
  // Real DB-backed AJAX handlers are implemented in cart-ui.js.

  // =============================================
  // SMOOTH PAGE LOAD TRANSITION
  // =============================================
  window.addEventListener('beforeunload', () => {
    document.body.classList.add('is-exiting');
  });

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init());
  } else {
    init();
  }

  function init() {
    // Add loaded class for entrance animations
    requestAnimationFrame(() => {
      document.body.classList.add('is-loaded');
    });
  }
})();