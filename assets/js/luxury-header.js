/**
 * Ultra Premium Luxury Header
 * Vaishnavi Jewellers
 */

// Base URL handling for /visnavi jwallers/ deployments
var baseFromDocument = function() {
  var baseEl = document.querySelector('base[href]');
  if (baseEl) {
    var href = baseEl.getAttribute('href') || '';
    if (!href) return '';
    return href.endsWith('/') ? href : href + '/';
  }
  try {
    var u = new URL(document.baseURI);
    var parts = u.pathname.split('/').filter(Boolean);
    if (parts.length > 0) {
      return '/' + parts[0] + '/';
    }
  } catch (e) {}
  return '';
};

var BASE_URL = baseFromDocument() || '/visnavi jwallers/';

document.addEventListener('DOMContentLoaded', function() {
  initLuxuryHeader();
  rotatePlaceholders();
});

function initLuxuryHeader() {
  initScrollEffects();
  initMobileMenu();
  initSearchSuggestions();
  initCategoryHover();
  initStickyHeader();
  initSearchSubmit();
}

/* Scroll Effects */
function initScrollEffects() {
  const header = document.getElementById('mainHeader');
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }

    lastScroll = currentScroll;
  });
}

/* Mobile Menu */
function initMobileMenu() {
  const toggle = document.getElementById('mobileMenuToggle');
  const panel = document.getElementById('mobilePanel');
  const overlay = document.getElementById('mobileOverlay');
  const closeBtn = document.getElementById('closeMobile');

  if (!toggle || !panel) return;

  toggle.addEventListener('click', () => {
    panel.classList.add('active');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  const closeMenu = () => {
    panel.classList.remove('active');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  };

  overlay.addEventListener('click', closeMenu);
  closeBtn?.addEventListener('click', closeMenu);

  // Close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });
}

/* Search Suggestions */
function initSearchSuggestions() {
  const searchInput = document.getElementById('headerSearch');
  const searchSuggestions = document.getElementById('searchSuggestions');

  if (!searchInput || !searchSuggestions) return;

  // Popular searches data
  var popularSearches = [
    { text: 'Gold Rings', icon: 'fa-ring', url: BASE_URL + 'pages/shop.php?category=Gold' },
    { text: 'Necklace Sets', icon: 'fa-circle', url: BASE_URL + 'pages/shop.php?category=Necklaces' },

    { text: 'Wedding Collection', icon: 'fa-certificate', url: BASE_URL + 'pages/shop.php?category=Wedding' },
    { text: 'Gold Bangles', icon: 'fa-circle', url: BASE_URL + 'pages/shop.php?category=Bangles' },
  ];



  searchInput.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase().trim();

    if (query.length > 0) {
      // Filter suggestions based on query
      const filtered = popularSearches.filter(s =>
        s.text.toLowerCase().includes(query)
      );
      renderSuggestions(filtered);
    } else {
      renderSuggestions(popularSearches);
    }
  });

  searchInput.addEventListener('focus', () => {
    searchSuggestions.style.display = 'block';
    searchSuggestions.style.opacity = '1';
    searchSuggestions.style.visibility = 'visible';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.lux-search-wrapper')) {
      searchSuggestions.style.display = '';
      searchSuggestions.style.opacity = '';
      searchSuggestions.style.visibility = '';
    }
  });

  function renderSuggestions(data) {
    if (!data.length) {
      searchSuggestions.innerHTML = '<div class="suggestion-header">No results found</div>';
      return;
    }

    const html = data.map(s => `
      <a href="${s.url}" class="suggestion-item">
        <i class="fas ${s.icon}"></i> ${s.text}
      </a>
    `).join('');

    searchSuggestions.innerHTML = `
      <div class="suggestion-header">${searchInput.value ? 'Suggestions' : 'Popular Searches'}</div>
      ${html}
    `;
  }
}

/* Category Hover Effects */
function initCategoryHover() {
  const categoryItems = document.querySelectorAll('.category-item.has-mega');

  categoryItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
      item.classList.add('mega-open');
    });

    item.addEventListener('mouseleave', () => {
      item.classList.remove('mega-open');
    });
  });
}

/* Sticky Header on Scroll Up */
function initStickyHeader() {
  const header = document.getElementById('mainHeader');
  let lastScroll = 0;
  let ticking = false;

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 200) {
          if (currentScroll < lastScroll) {
            header.style.top = '0';
            header.classList.add('sticky');
          } else {
            header.style.top = '-200px';
          }
        } else {
          header.style.top = '';
          header.classList.remove('sticky');
        }

        lastScroll = currentScroll;
        ticking = false;
      });

      ticking = true;
    }
  });
}

/* Search Submit Handler */
function initSearchSubmit() {
  const searchInput = document.getElementById('headerSearch');
  const searchBox = searchInput?.closest('.lux-search-box');

  if (!searchInput || !searchBox) return;

  searchBox.addEventListener('submit', (e) => {
    e.preventDefault();
    const query = searchInput.value.trim();
    if (query) {
      window.location.href = BASE_URL + 'pages/shop.php?search=' + encodeURIComponent(query);
    }
  });

  // Enter key handler
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const query = searchInput.value.trim();
      if (query) {
        window.location.href = BASE_URL + 'pages/shop.php?search=' + encodeURIComponent(query);
      }
    }
  });
}

/* Smooth Scroll for Internal Links */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

/* Header Badge Animation */
function animateBadge(element) {
  if (!element) return;

  element.style.animation = 'none';
  element.offsetHeight; // Trigger reflow
  element.style.animation = 'badgePop 0.3s ease';
}

/* Add to Cart Animation */
function showCartNotification(productName) {
  const notification = document.createElement('div');
  notification.className = 'cart-notification';
  notification.innerHTML = `
    <i class="fas fa-check-circle"></i>
    <span>${productName} added to cart</span>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

/* Search Placeholder Rotation */
function rotatePlaceholders() {
  const input = document.getElementById('headerSearch');
  const placeholders = [
    'Search for jewellery...',

    'Search gold necklaces...',
    'Search earrings...',
    'Search wedding collection...'
  ];
  let current = 0;

  if (!input) return;

  setInterval(() => {
    current = (current + 1) % placeholders.length;
    input.placeholder = placeholders[current];
  }, 4000);
}

/* Window resize handler */
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    const panel = document.getElementById('mobilePanel');
    const overlay = document.getElementById('mobileOverlay');

    if (window.innerWidth > 1200 && panel.classList.contains('active')) {
      panel.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }, 250);
});