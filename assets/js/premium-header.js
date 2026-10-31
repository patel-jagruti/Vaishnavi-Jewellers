/* ======================================================
   VAISHNAVI LUXURY HEADER - JavaScript
   ====================================================== */

(function() {
  'use strict';

  // Base URL handling for /visnavi jwallers/ deployments
  const baseFromDocument = () => {
    const baseEl = document.querySelector('base[href]');
    if (baseEl) {
      const href = baseEl.getAttribute('href') || '';
      if (!href) return '';
      return href.endsWith('/') ? href : href + '/';
    }
    try {
      const u = new URL(document.baseURI);
      const parts = u.pathname.split('/').filter(Boolean);
      if (parts.length > 0) {
        return '/' + parts[0] + '/';
      }
    } catch (e) {}
    return '';
  };

  const BASE_URL = baseFromDocument() || '/visnavi jwallers/';

  const apiUrl = (path) => {
    const base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
    return base + String(path).replace(/^\/+/, '');
  };

  // Cache DOM elements
  const header = document.querySelector('.lux-header');
  const searchInput = document.querySelector('.search-input-wrapper input');
  const searchWrapper = document.querySelector('.search-input-wrapper');
  const micBtn = document.querySelector('.search-btn[data-voice]');
  const imageBtn = document.querySelector('.search-btn[data-image]');

  // Initialize
  function init() {
    initScrollEffects();
    initSearch();
    initVoiceSearch();
    initImageSearch();
    initMobileMenu();
    initStickyCart();
  }

  // Scroll Effects
  function initScrollEffects() {
    if (!header) return;

    let lastScroll = 0;
    const scrollThreshold = 50;

    window.addEventListener('scroll', () => {
      const scrollY = window.scrollY;
      const isScrolled = scrollY > scrollThreshold;

      // Add/remove scrolled class
      if (isScrolled) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }

      // Hide on scroll down, show on scroll up
      if (scrollY > lastScroll && scrollY > 200) {
        header.classList.add('hidden');
      } else {
        header.classList.remove('hidden');
      }

      lastScroll = scrollY;
    });

    // Initial check
    if (window.scrollY > scrollThreshold) {
      header.classList.add('scrolled');
    }
  }

  // Search functionality
  function initSearch() {
    if (!searchInput) return;

    let searchTimeout;

    // Debounced search
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      const query = e.target.value.trim();

      if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
          performSearch(query);
        }, 300);
      }
    });

    // Submit on Enter
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        performSearch(e.target.value.trim());
      }
    });

    // Clear search
    const clearBtn = document.querySelector('.search-btn[data-clear]');
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchInput.focus();
        hideSearchResults();
      });
    }
  }

  // Perform Search
  async function performSearch(query) {
    // Show loading state
    showSearchLoading();

    try {
      const response = await fetch(apiUrl(`api/search.php?q=${encodeURIComponent(query)}`));
      const data = await response.json();

      if (data.success) {
        displaySearchResults(data.products);
      } else {
        displaySearchError(data.message || 'No results found');
      }
    } catch (error) {
      console.error('Search error:', error);
      // Fallback: redirect to search page
      window.location.href = BASE_URL + `pages/shop.php?search=${encodeURIComponent(query)}`;
    }
  }

  // Show results in dropdown
  function displaySearchResults(products) {
    let resultsContainer = document.querySelector('.search-results');

    if (!resultsContainer) {
      resultsContainer = createResultsContainer();
    }

    if (products.length === 0) {
      resultsContainer.innerHTML = `
        <div class="search-empty">
          <i class="fas fa-search"></i>
          <p>No products found</p>
        </div>
      `;
    } else {
      const html = products.slice(0, 6).map(product => `
        <a href="/product.php?id=${product.id}" class="search-result-item">
          <img src="${product.image}" alt="${product.name}">
          <div class="result-info">
            <h4>${product.name}</h4>
            <p class="result-price">${formatPrice(product.price)}</p>
          </div>
        </a>
      `).join('');

      resultsContainer.innerHTML = html;
    }

    resultsContainer.style.display = 'block';
  }

  // Create results container
  function createResultsContainer() {
    const container = document.createElement('div');
    container.className = 'search-results';
    container.style.cssText = `
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      margin-top: 8px;
      overflow: hidden;
      z-index: 1000;
      max-height: 400px;
      overflow-y: auto;
      display: none;
    `;

    if (searchWrapper) {
      searchWrapper.parentElement.appendChild(container);
    }

    return container;
  }

  function showSearchLoading() {
    const resultsContainer = document.querySelector('.search-results') || createResultsContainer();
    resultsContainer.innerHTML = `
      <div class="search-loading">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Searching...</p>
      </div>
    `;
    resultsContainer.style.display = 'block';
  }

  function hideSearchResults() {
    const resultsContainer = document.querySelector('.search-results');
    if (resultsContainer) {
      resultsContainer.style.display = 'none';
    }
  }

  // Voice Search
  function initVoiceSearch() {
    if (!micBtn || !('webkitSpeechRecognition' in window)) return;

    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = () => {
      micBtn.classList.add('listening');
      micBtn.innerHTML = '<i class="fas fa-microphone-slash"></i>';
    };

    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      searchInput.value = transcript;
      performSearch(transcript);
    };

    recognition.onerror = (event) => {
      console.error('Voice search error:', event.error);
      micBtn.classList.remove('listening');
      micBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    };

    recognition.onend = () => {
      micBtn.classList.remove('listening');
      micBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    };

    micBtn.addEventListener('click', () => {
      recognition.start();
    });
  }

  // Image Search
  function initImageSearch() {
    if (!imageBtn) return;

    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';

    fileInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      // Show preview
      const reader = new FileReader();
      reader.onload = async (event) => {
        // Upload and search
        await uploadImageForSearch(file);
      };
      reader.readAsDataURL(file);
    });

    imageBtn.addEventListener('click', () => {
      fileInput.click();
    });
  }

  async function uploadImageForSearch(file) {
    const formData = new FormData();
    formData.append('image', file);

    try {
      const response = await fetch(apiUrl('api/search.php'), {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        displaySearchResults(data.products);
      }
    } catch (error) {
      console.error('Image search error:', error);
    }
  }

  // Mobile Menu
  function initMobileMenu() {
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.lux-nav');

    if (!menuBtn || !nav) return;

    menuBtn.addEventListener('click', () => {
      nav.classList.toggle('active');
      menuBtn.classList.toggle('active');
    });
  }

  // Sticky Cart Animation
  function initStickyCart() {
    const cartBtn = document.querySelector('.header-action[href*="cart"]');

    if (!cartBtn) return;

    // Bounce animation on add to cart
    document.addEventListener('cart:added', () => {
      cartBtn.classList.add('bounce');

      setTimeout(() => {
        cartBtn.classList.remove('bounce');
      }, 500);
    });
  }

  // Format Price
  function formatPrice(price) {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency: 'INR',
      minimumFractionDigits: 0
    }).format(price);
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();