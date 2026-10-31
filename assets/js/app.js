(() => {
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

  const toast = (msg) => {
    const el = document.createElement('div');
    el.className = 'toast';
    el.textContent = msg;
    document.body.appendChild(el);
    requestAnimationFrame(() => {
      el.classList.add('show');
    });
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }, 2300);
  };

  const updateBadgeCount = (role, value) => {
    const count = String(Math.max(0, Number(value) || 0));
    document.querySelectorAll(`[data-count-role="${role}"]`).forEach((badge) => {
      badge.textContent = count;
    });
  };

  // Simple search filter for homepage cards
  const searchInput = document.querySelector('[data-search]');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      const cards = document.querySelectorAll('[data-product-card]');
      cards.forEach((card) => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        const cat = (card.getAttribute('data-category') || '').toLowerCase();
        card.style.display = (!q || name.includes(q) || cat.includes(q)) ? '' : 'none';
      });
    });
  }

  // Search suggestions (AI-like quick suggestions using local product API)
  if (searchInput) {
    const searchBox = searchInput.closest('.search') || searchInput.parentNode;
    let productsCache = null;
    let visibleSuggestions = [];
    const wrapper = document.createElement('div');
    wrapper.className = 'search-suggestions';
    wrapper.style.display = 'none';
    searchBox.appendChild(wrapper);

    const debounce = (fn, t=180) => {
      let id = null; return (...args)=>{ clearTimeout(id); id = setTimeout(()=>fn(...args), t); };
    };

    const render = (items)=>{
      visibleSuggestions = items || [];
      wrapper.innerHTML = '';
      if (!items || items.length===0) { wrapper.style.display='none'; return }
      items.forEach((p, i)=>{
        const el = document.createElement('div');
        el.className = 'search-suggestion';
        el.setAttribute('role','option');
        el.dataset.index = i;
        el.innerHTML = `<div class="s-title">${escapeHtml(p.name)}</div><div class="s-meta">${escapeHtml(p.category)} • ₹${Number(p.price).toLocaleString()}</div>`;
        el.addEventListener('click', ()=>{ window.location.href = 'pages/product.php?id='+encodeURIComponent(p.id); });
        wrapper.appendChild(el);
      });
      wrapper.style.display = '';
      activeIndex = -1;
    };

    function escapeHtml(s){ return String(s).replace(/[&<>\"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }

    const fetchProducts = async ()=>{
      if (productsCache) return productsCache;
      try{
        const res = await fetch(apiUrl('api/products.php'));
        const data = await res.json();
        productsCache = (data && data.products) ? data.products : [];
        return productsCache;
      }catch(e){ productsCache = []; return []; }
    };

    const match = (p, q)=>{
      const name = p.name.toLowerCase(); const cat = p.category.toLowerCase(); const desc = (p.description||'').toLowerCase();
      return name.includes(q) || cat.includes(q) || desc.includes(q);
    };

    let activeIndex = -1;
    function setActive(i){
      const items = wrapper.querySelectorAll('.search-suggestion');
      items.forEach((it, n)=> it.classList.toggle('active', n===i));
      activeIndex = i;
      if (i>=0 && items[i]) items[i].scrollIntoView({block:'nearest'});
    }

    searchInput.addEventListener('keydown', (e)=>{
      const items = wrapper.querySelectorAll('.search-suggestion');
      if (!items.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(activeIndex+1, items.length-1)); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(activeIndex-1, 0)); }
      else if (e.key === 'Enter') {
        if (activeIndex>=0 && items[activeIndex]) {
          e.preventDefault();
          const idx = Number(items[activeIndex].dataset.index);
          const prod = visibleSuggestions[idx];
          if (prod) window.location.href = 'pages/product.php?id='+encodeURIComponent(prod.id);
        }
      } else if (e.key === 'Escape') { wrapper.style.display='none'; }
    });

    const onInput = debounce(async ()=>{
      const q = searchInput.value.trim().toLowerCase();
      if (!q) { visibleSuggestions = []; wrapper.style.display='none'; return }
      const prods = await fetchProducts();
      const matches = prods.filter(p=>match(p,q)).slice(0,6);
      render(matches.map((p)=>({ id:p.id, name:p.name, category:p.category, price:p.price })));
    }, 160);

    searchInput.addEventListener('input', onInput);
    document.addEventListener('click', (e)=>{
      if (!searchBox.contains(e.target)) {
        wrapper.style.display='none';
      }
    });
  }

  // Note: AJAX cart/wishlist additions are handled by cart-ui.js to avoid duplicate submissions and conflict.
})();

// Luxury loading screen
window.addEventListener('load', () => {
  const lux = document.getElementById('lux-loading');
  if (!lux) return;
  lux.style.transition = 'opacity .45s ease, transform .45s ease';
  lux.style.opacity = '0';
  lux.style.transform = 'scale(1.02)';
  setTimeout(() => { lux.remove(); }, 520);
});


// Hero slider
(function(){
  const slider = document.querySelector('[data-slider]');
  if (!slider) return;
  const slides = Array.from(slider.querySelectorAll('.slide'));
  const next = slider.querySelector('.slide-next');
  const prev = slider.querySelector('.slide-prev');
  const dots = Array.from(slider.querySelectorAll('.dot'));
  let idx = slides.findIndex(s=>s.classList.contains('active')) || 0;
  let timer = null;

  function show(i){
    slides.forEach((s, n)=>{
      const active = n===i;
      s.classList.toggle('active', active);
      s.setAttribute('aria-hidden', (!active).toString());
    });
    dots.forEach((d,n)=> d.classList.toggle('active', n===i));
    idx = i;
  }
  function nextSlide(){ show((idx+1) % slides.length) }
  function prevSlide(){ show((idx-1+slides.length) % slides.length) }

  next && next.addEventListener('click', ()=>{ nextSlide(); resetTimer(); });
  prev && prev.addEventListener('click', ()=>{ prevSlide(); resetTimer(); });
  dots.forEach(d => d.addEventListener('click', (e)=>{ show(Number(d.dataset.index)); resetTimer(); }));

  function startTimer(){ timer = setInterval(nextSlide, 4200); }
  function resetTimer(){ clearInterval(timer); startTimer(); }
  slider.addEventListener('mouseenter', ()=> clearInterval(timer));
  slider.addEventListener('mouseleave', ()=> startTimer());

  startTimer();
})();

// Product gallery: thumbnails + hover zoom
(function(){
  const gallery = document.querySelector('[data-gallery][data-main-image]') || document.querySelector('[data-gallery]');
  const mainImg = document.querySelector('[data-main-image]');
  if (!gallery || !mainImg) return;

  const thumbs = gallery.querySelectorAll('[data-thumb-index]');
  const zoomLens = document.querySelector('.zoom-lens');
  const zoomResult = document.querySelector('.zoom-result');

  const setMainImage = (index) => {
    const btn = Array.from(thumbs).find(t => Number(t.getAttribute('data-thumb-index')) === Number(index));
    if (!btn) return;
    thumbs.forEach((t) => t.classList.toggle('is-active', t === btn));
    const bg = getComputedStyle(btn).backgroundImage;
    mainImg.style.backgroundImage = bg;
    if (zoomResult) zoomResult.style.backgroundImage = bg;
  };

  thumbs.forEach((btn) => {
    btn.addEventListener('click', () => {
      const idx = Number(btn.getAttribute('data-thumb-index') || '0');
      setMainImage(idx);
    });
  });

  if (!zoomLens || !zoomResult) return;
  const mainZoom = mainImg.closest('.main-zoom');
  if (!mainZoom) return;

  const isTouch = window.matchMedia('(pointer: coarse)').matches;
  if (isTouch) return;

  const showZoom = () => {
    zoomLens.style.display = '';
    zoomResult.style.display = '';
  };
  const hideZoom = () => {
    zoomLens.style.display = 'none';
    zoomResult.style.display = 'none';
  };

  mainZoom.addEventListener('mouseenter', showZoom);
  mainZoom.addEventListener('mouseleave', hideZoom);

  mainZoom.addEventListener('mousemove', (e) => {
    const rect = mainImg.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const lensSize = zoomLens.offsetWidth || 140;
    let lx = x - lensSize / 2;
    let ly = y - lensSize / 2;
    lx = Math.max(0, Math.min(lx, rect.width - lensSize));
    ly = Math.max(0, Math.min(ly, rect.height - lensSize));

    zoomLens.style.left = `${lx}px`;
    zoomLens.style.top = `${ly}px`;

    // Map lens position to background position in zoom window.
    const bgSize = 520; // matches CSS default; kept simple for production feel
    const scale = 3; // approximate zoom factor
    const bgX = (x / rect.width) * (bgSize * scale) - (zoomResult.offsetWidth / 2);
    const bgY = (y / rect.height) * (bgSize * scale) - (zoomResult.offsetHeight / 2);

    zoomResult.style.backgroundSize = `${bgSize * scale}px ${bgSize * scale}px`;
    zoomResult.style.backgroundPosition = `-${bgX}px -${bgY}px`;
  });
})();

// Header mega menu for category rail - Tanishq-style
(function(){
  const triggers = Array.from(document.querySelectorAll('[data-mega-trigger]'));
  const menu = document.querySelector('[data-mega-menu]');
  if (!triggers.length || !menu) return;

  let closeTimer = null;
  let activePanel = 'all-jewellery';
  const isDesktop = () => window.matchMedia('(min-width: 901px)').matches;

  // Get all panel elements (sidebar, content, promo)
  const getPanelElements = (key) => {
    return menu.querySelectorAll(`[data-mega-panel="${key}"]`);
  };

  const setActivePanel = (panelKey) => {
    activePanel = panelKey || 'all-jewellery';
    // Hide all panels first
    menu.querySelectorAll('[data-mega-panel]').forEach((el) => {
      el.style.display = 'none';
      el.classList.remove('is-active');
    });
    // Show matching panels
    getPanelElements(activePanel).forEach((el) => {
      el.style.display = '';
      el.classList.add('is-active');
    });
    // Update nav trigger states
    triggers.forEach((item) => {
      const isActive = item.getAttribute('data-mega-panel') === activePanel;
      item.classList.toggle('is-active', isActive);
      item.setAttribute('aria-expanded', isActive && menu.classList.contains('is-open') ? 'true' : 'false');
      if (isActive) item.setAttribute('aria-current', 'page');
      else item.removeAttribute('aria-current');
    });
  };

  const openMenu = (panelKey) => {
    if (!isDesktop()) return;
    clearTimeout(closeTimer);
    setActivePanel(panelKey);
    menu.classList.add('is-open');
    menu.style.visibility = 'visible';
    menu.style.opacity = '1';
  };

  const closeMenu = () => {
    menu.classList.remove('is-open');
    menu.style.visibility = '';
    menu.style.opacity = '';
    triggers.forEach((item) => item.setAttribute('aria-expanded', 'false'));
  };

  const delayedClose = () => {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(closeMenu, 150);
  };

  triggers.forEach((trigger) => {
    const panelKey = trigger.getAttribute('data-mega-panel') || 'all-jewellery';
    trigger.addEventListener('mouseenter', () => openMenu(panelKey));
    trigger.addEventListener('focus', () => openMenu(panelKey));
    trigger.addEventListener('mouseleave', delayedClose);
  });

  menu.addEventListener('mouseenter', () => clearTimeout(closeTimer));
  menu.addEventListener('mouseleave', delayedClose);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  document.addEventListener('click', (e) => {
    if (!triggers.some((trigger) => trigger.contains(e.target)) && !menu.contains(e.target)) {
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (!isDesktop()) closeMenu();
  });

  setActivePanel(activePanel);
})();

