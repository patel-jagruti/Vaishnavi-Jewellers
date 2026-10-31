(function(){
  const tickerSelector = '.lux-gold-ticker';
  const apiUrl = 'pages/ajax-gold-rate.php';

  function fmt(n){
    const x = Number(n);
    if(!isFinite(x)) return '--';
    return '₹' + Math.round(x).toLocaleString('en-IN');
  }

  function arrow(curr, prev){
    const c = Number(curr);
    const p = Number(prev);
    if(!isFinite(c) || !isFinite(p) || p === 0) return {html:'', cls:''};
    const diff = c - p;
    if (diff > 0) return {html:'▲', cls:'up'};
    if (diff < 0) return {html:'▼', cls:'down'};
    return {html:'•', cls:''};
  }

  function setLoading(){
    const root = document.querySelector(tickerSelector);
    if(!root) return;

    root.classList.add('is-loading');
    const pairs = root.querySelectorAll('[data-rate-key]');
    pairs.forEach(el => {
      el.textContent = '--';
    });

    const meta = root.querySelector('[data-fetched-at]');
    if(meta) meta.textContent = 'Updating...';
  }

  async function fetchRates(){
    const root = document.querySelector(tickerSelector);
    if(!root) return;

    setLoading();

    // Simple cache-busting per request; server already caches.
    const url = apiUrl + '?t=' + Date.now();

    try{
      const res = await fetch(url, { method:'GET', headers:{'Accept':'application/json'} });
      const json = await res.json();

      if(!json || json.success === false){
        if(json && json.data){
          // Use stale data if provided
        } else {
          throw new Error(json && json.message ? json.message : 'Unknown error');
        }
      }

      const data = json.data || {};
      const fetchedAt = json.fetched_at || '';

      const mapping = {
        gold24: { node: data.gold_24 || {}, valueKey:'rate_per_g', prevKey:'previous_per_g' },
        gold22: { node: data.gold_22 || {}, valueKey:'rate_per_g', prevKey:'previous_per_g' },
        gold18: { node: data.gold_18 || {}, valueKey:'rate_per_g', prevKey:'previous_per_g' },
        silver: { node: data.silver || {}, valueKey:'rate_per_g', prevKey:'previous_per_g' },
      };

      Object.keys(mapping).forEach(key => {
        const cfg = mapping[key];
        const curr = cfg.node[cfg.valueKey];
        const prev = cfg.node[cfg.prevKey];

        const valueEl = root.querySelector('[data-rate-key="'+key+'"]');
        if(valueEl) valueEl.textContent = fmt(curr);

        const arrowEl = root.querySelector('[data-rate-arrow="'+key+'"]');
        if(arrowEl){
          const a = arrow(curr, prev);
          arrowEl.textContent = a.html;
          arrowEl.className = 'lux-gold-ticker__arrow ' + a.cls;
        }
      });

      const meta = root.querySelector('[data-fetched-at]');
      if(meta) meta.textContent = 'Updated: ' + fetchedAt;

      root.classList.remove('is-loading');
    }catch(e){
      const meta = document.querySelector(tickerSelector+' [data-fetched-at]');
      if(meta) meta.textContent = 'Update failed. Showing last known values.';
      root.classList.remove('is-loading');
      console.error(e);
    }
  }

  function start(){
    const root = document.querySelector(tickerSelector);
    if(!root) return;

    // Controls (admin-like via data attributes)
    // data-enabled="1|0" data-speed="slow|normal|fast" data-position="top|bottom"
    const enabled = root.getAttribute('data-enabled');
    if(enabled === '0') return;

    const speed = root.getAttribute('data-speed') || 'normal';
    root.setAttribute('data-speed', speed);

    // Position
    const pos = root.getAttribute('data-position') || 'top';
    if(pos === 'bottom'){
      root.style.top = 'auto';
      root.style.bottom = '0';
    }

    // Manual refresh button
    const btn = root.querySelector('[data-manual-refresh]');
    if(btn){
      btn.addEventListener('click', fetchRates);
    }

    fetchRates();
    setInterval(fetchRates, 60 * 60 * 1000); // every 1 hour
  }

  document.addEventListener('DOMContentLoaded', start);
})();

