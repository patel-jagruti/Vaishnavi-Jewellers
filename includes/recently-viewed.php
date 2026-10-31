<!-- ===== RECENTLY VIEWED PRODUCTS STRIP (Feature C) ===== -->
<style>
.rv-strip {
  background: #FAF9F7;
  border-top: 1px solid rgba(0,0,0,0.06);
  padding: 32px 0 28px;
  display: none;
}
.rv-strip.has-items { display: block; }
.rv-strip-inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.rv-strip-title {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 700;
  color: #1A1A1A;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.rv-strip-title::before {
  content: '';
  display: inline-block;
  width: 22px; height: 2px;
  background: linear-gradient(90deg, #D4AF37, #F5E6A3);
}
.rv-scroll-wrap {
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.rv-scroll-wrap::-webkit-scrollbar { display: none; }
.rv-row {
  display: flex;
  gap: 14px;
  padding-bottom: 4px;
  width: max-content;
}
.rv-card {
  width: 130px;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 3px 14px rgba(0,0,0,0.06);
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  flex-shrink: 0;
  border: 1px solid rgba(0,0,0,0.04);
}
.rv-card:hover { transform: translateY(-5px); box-shadow: 0 10px 28px rgba(0,0,0,0.1); }
.rv-img {
  width: 100%; height: 100px;
  object-fit: cover;
  background: linear-gradient(135deg,#f8f5f0,#FAF8F6);
}
.rv-info { padding: 8px 10px 10px; }
.rv-name {
  font-size: 11px;
  font-weight: 600;
  color: #1A1A1A;
  font-family: 'Poppins', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 3px;
}
.rv-price {
  font-size: 12px;
  font-weight: 700;
  color: #7A1E3A;
  font-family: 'Poppins', sans-serif;
}
</style>

<div class="rv-strip" id="rvStrip">
  <div class="rv-strip-inner">
    <div class="rv-strip-title">Recently Viewed</div>
    <div class="rv-scroll-wrap">
      <div class="rv-row" id="rvRow"></div>
    </div>
  </div>
</div>

<script>
(function() {
  const STORAGE_KEY = 'vj_recently_viewed';
  const MAX_ITEMS = 8;

  function getViewed() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
    catch(e) { return []; }
  }

  function saveViewed(arr) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
  }

  // Track current page if it's a product
  const urlParams = new URLSearchParams(window.location.search);
  const pid = urlParams.get('id');
  if (pid && window.location.pathname.includes('product.php')) {
    // Fetch product data to store
    fetch('/visnavi jwallers/api/products.php?id=' + pid)
      .then(r => r.json())
      .then(data => {
        if (!data.product) return;
        const p = data.product;
        let viewed = getViewed().filter(v => v.id != p.id);
        viewed.unshift({
          id: p.id,
          name: p.name,
          price: p.price || p.base_price,
          img: p.images && p.images.length > 0 ? '/visnavi jwallers/' + p.images[0] : '/visnavi jwallers/assets/images/Rings.png'
        });
        if (viewed.length > MAX_ITEMS) viewed = viewed.slice(0, MAX_ITEMS);
        saveViewed(viewed);
      })
      .catch(() => {});
  }

  // Render strip (exclude current product)
  document.addEventListener('DOMContentLoaded', function() {
    const strip = document.getElementById('rvStrip');
    const row   = document.getElementById('rvRow');
    if (!strip || !row) return;

    const viewed = getViewed().filter(v => String(v.id) !== String(pid));
    if (viewed.length < 1) return;

    strip.classList.add('has-items');
    viewed.forEach(item => {
      const a = document.createElement('a');
      a.className = 'rv-card';
      a.href = '/visnavi jwallers/pages/product.php?id=' + item.id;
      a.innerHTML = `
        <img class="rv-img" src="${item.img}" alt="${item.name}" loading="lazy" onerror="this.src='/visnavi jwallers/assets/images/Rings.png'">
        <div class="rv-info">
          <div class="rv-name">${item.name}</div>
          <div class="rv-price">&#8377;${Math.round(item.price).toLocaleString('en-IN')}</div>
        </div>
      `;
      row.appendChild(a);
    });
  });
})();
</script>
