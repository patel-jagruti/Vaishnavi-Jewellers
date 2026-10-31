/**
 * Vaishnavi Jewellers - Premium Header JavaScript
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

var apiUrl = function(path) {
  var base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
  return base + String(path).replace(/^\/+/, '');
};

document.addEventListener('DOMContentLoaded', function() {
  // Wait for DOM to be fully loaded
  initHeader();
  initMobileNav();
  initSearch();
  initStickyHeader();
  initCategoryHover();
});

/**
 * Initialize Header Components
 */
function initHeader() {
  // Initialize dropdowns
  initDropdowns();

  // Initialize wishlist
  initWishlist();

  // Initialize cart
  initCart();

  // Initialize account
  initAccount();
}

/**
 * Initialize Dropdowns
 */
function initDropdowns() {
  const dropdowns = document.querySelectorAll('.dropdown-wrapper');

  dropdowns.forEach(dropdown => {
    const menu = dropdown.querySelector('.dropdown-menu');

    if (!menu) return;

    dropdown.addEventListener('mouseenter', () => {
      menu.classList.add('show');
    });

    dropdown.addEventListener('mouseleave', () => {
      menu.classList.remove('show');
    });
  });
}

/**
 * Initialize Wishlist
 */
function initWishlist() {
  const wishlistBtn = document.querySelector('[data-wishlist]');
  if (wishlistBtn) {
    wishlistBtn.addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = BASE_URL + 'pages/wishlist.php';
    });
  }
}

/**
 * Initialize Cart
 */
function initCart() {
  const cartBtns = document.querySelectorAll('[data-cart]');
  cartBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = BASE_URL + 'pages/cart.php';
    });
  });
}

/**
 * Initialize Account
 */
function initAccount() {
  const accountLink = document.querySelector('[data-account]');
  if (accountLink) {
    accountLink.addEventListener('click', function(e) {
      // Check if logged in
      const isLoggedIn = document.body.classList.contains('logged-in');
      if (!isLoggedIn) {
        e.preventDefault();
        window.location.href = BASE_URL + 'pages/login.php';
      }
    });
  }
}

/**
 * Initialize Mobile Navigation
 */
function initMobileNav() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavClose = document.getElementById('mobileNavClose');
  const mobileNavGroupBtns = document.querySelectorAll('.mobile-nav-group-btn');
  const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');

  if (mobileMenuBtn && mobileNav) {
    mobileMenuBtn.addEventListener('click', function() {
      mobileNav.classList.add('active');
      if (mobileNavOverlay) mobileNavOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  }

  if (mobileNavClose && mobileNav) {
    mobileNavClose.addEventListener('click', function() {
      mobileNav.classList.remove('active');
      if (mobileNavOverlay) mobileNavOverlay.classList.remove('active');
      document.body.style.overflow = '';
    });
  }

  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', function() {
      mobileNav.classList.remove('active');
      mobileNavOverlay.classList.remove('active');
      document.body.style.overflow = '';
    });
  }

  // Mobile nav group toggle
  mobileNavGroupBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const target = this.dataset.group;
      const content = document.getElementById('group-' + target);

      if (content) {
        this.classList.toggle('active');
        content.classList.toggle('show');
      }
    });
  });
}

/**
 * Initialize Search Functionality
 */
function initSearch() {
  const searchInput = document.getElementById('searchInput');
  const searchWrapper = document.querySelector('.search-wrapper');
  const searchDropdown = document.getElementById('searchDropdown');

  if (!searchInput || !searchWrapper) return;

  // Show dropdown on focus
  searchInput.addEventListener('focus', function() {
    searchWrapper.classList.add('focused');
    if (searchDropdown) {
      searchDropdown.classList.add('show');
    }
  });

  // Hide dropdown on blur (with delay for click events)
  searchInput.addEventListener('blur', function() {
    setTimeout(() => {
      searchWrapper.classList.remove('focused');
      if (searchDropdown) {
        searchDropdown.classList.remove('show');
      }
    }, 200);
  });

  // Search on Enter
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
      performSearch(this.value.trim());
    }
  });

  // Clear search
  searchInput.addEventListener('input', function() {
    if (this.value.length > 0) {
      performLiveSearch(this.value);
    }
  });
}

/**
 * Perform Search
 */
function performSearch(query) {
  // Redirect to shop page with search query
  window.location.href = BASE_URL + 'pages/shop.php?q=' + encodeURIComponent(query);
}

/**
 * Perform Live Search (AJAX)
 */
function performLiveSearch(query) {
  // Only search if query is at least 2 characters
  if (query.length < 2) return;

  // In production, this would be an AJAX call
  // For now, we'll just simulate it
  console.log('Live search for:', query);
}

/**
 * Initialize Sticky Header
 */
function initStickyHeader() {
  const header = document.getElementById('mainHeader');
  const categoryNav = document.getElementById('categoryNav');

  if (!header) return;

  let lastScroll = 0;
  const scrollThreshold = 100;

  window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;

    // Add scrolled class when past threshold
    if (currentScroll > scrollThreshold) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }

    // Hide on scroll down, show on scroll up
    if (currentScroll > lastScroll && currentScroll > 200) {
      header.style.transform = 'translateY(-100%)';
    } else {
      header.style.transform = 'translateY(0)';
    }

    lastScroll = currentScroll;
  });
}

/**
 * Initialize Category Hover Effect
 */
function initCategoryHover() {
  const categoryItems = document.querySelectorAll('.category-item');

  categoryItems.forEach(item => {
    const link = item.querySelector('a');
    const megaMenu = item.querySelector('.mega-menu');

    if (!link || !megaMenu) return;

    item.addEventListener('mouseenter', function() {
      // Close other mega menus
      categoryItems.forEach(otherItem => {
        const otherMenu = otherItem.querySelector('.mega-menu');
        if (otherMenu && otherMenu !== megaMenu) {
          otherMenu.classList.remove('show');
        }
      });

      // Show this mega menu
      megaMenu.classList.add('show');
    });

    item.addEventListener('mouseleave', function() {
      megaMenu.classList.remove('show');
    });
  });
}

/**
 * Update Cart Count (for AJAX updates)
 */
function updateCartCount(count) {
  const cartCountEl = document.querySelector('.cart-count');
  if (cartCountEl) {
    if (count > 0) {
      cartCountEl.textContent = count;
      cartCountEl.style.display = 'flex';
    } else {
      cartCountEl.style.display = 'none';
    }
  }
}

/**
 * Update Wishlist Count (for AJAX updates)
 */
function updateWishlistCount(count) {
  const wishCountEl = document.querySelector('.wishlist-count');
  if (wishCountEl) {
    if (count > 0) {
      wishCountEl.textContent = count;
      wishCountEl.style.display = 'flex';
    } else {
      wishCountEl.style.display = 'none';
    }
  }
}

/**
 * Show Notification Toast
 */
function showToast(message, type = 'success') {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = 'toast-notification toast-' + type;
  toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i><span>' + message + '</span>';

  document.body.appendChild(toast);

  // Show toast
  setTimeout(() => {
    toast.classList.add('show');
  }, 100);

  // Hide and remove toast
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 3000);
}

/**
 * Add to Cart (AJAX)
 */
function addToCart(productId, quantity = 1) {
  fetch(apiUrl('api/cart-add.php'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'product_id=' + productId + '&quantity=' + quantity
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateCartCount(data.cart_count);
      showToast('Added to cart!', 'success');
    } else {
      showToast(data.message || 'Failed to add to cart', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Something went wrong', 'error');
  });
}

/**
 * Add to Wishlist (AJAX)
 */
function addToWishlist(productId) {
  fetch(apiUrl('api/wishlist-add.php'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'product_id=' + productId
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateWishlistCount(data.wishlist_count);
      showToast('Added to wishlist!', 'success');
    } else {
      showToast(data.message || 'Failed to add to wishlist', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Something went wrong', 'error');
  });
}

/**
 * Remove from Cart (AJAX)
 */
function removeFromCart(productId) {
  fetch(apiUrl('api/cart-remove.php'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'product_id=' + productId
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateCartCount(data.cart_count);
      showToast('Removed from cart', 'success');
      // Reload to update cart display
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showToast(data.message || 'Failed to remove', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Something went wrong', 'error');
  });
}

/**
 * Remove from Wishlist (AJAX)
 */
function removeFromWishlist(productId) {
  fetch(apiUrl('api/wishlist-remove.php'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'product_id=' + productId
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateWishlistCount(data.wishlist_count);
      showToast('Removed from wishlist', 'success');
      // Reload to update wishlist display
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showToast(data.message || 'Failed to remove', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Something went wrong', 'error');
  });
}

// Export functions for global use
window.VaishnaviHeader = {
  updateCartCount,
  updateWishlistCount,
  showToast,
  addToCart,
  addToWishlist,
  removeFromCart,
  removeFromWishlist
};