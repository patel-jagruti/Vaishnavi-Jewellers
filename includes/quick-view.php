<!-- ========== FULL PRODUCT DETAIL MODAL (replaces product.php) ========== -->
<style>
/* ── Modal Size ── */
.qv-modal-dialog { max-width: 1080px !important; }
.modal-body { padding: 0 !important; }

/* ── Left: Image Gallery ── */
.qv-img-wrap {
  background: #FAF9F7;
  border-radius: 0;
  position: relative;
  min-height: 420px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.qv-img-wrap img#qvMainImg {
  width: 100%;
  height: 420px;
  object-fit: contain;
  cursor: zoom-in;
  user-select: none;
  transition: transform 0.1s ease-out;
  transform-origin: center center;
}
.qv-media-toggles {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  gap: 6px;
  z-index: 10;
}
.qv-media-toggle {
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 10px;
  font-weight: 600;
  color: #555;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-family: 'Poppins', sans-serif;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.qv-media-toggle:hover {
  background: #fff;
  color: #7A1E3A;
}
.qv-media-toggle.active {
  background: #7A1E3A;
  color: #fff;
  border-color: #7A1E3A;
}
.rotate-hint-qv {
  position: absolute; bottom:12px; left:50%; transform:translateX(-50%);
  background:rgba(0,0,0,0.45); color:#fff; font-size:10px;
  padding:4px 12px; border-radius:20px; font-family:'Poppins',sans-serif;
  pointer-events:none; transition:opacity 0.5s; white-space:nowrap;
}
.qv-thumbnails { display:flex; gap:8px; padding:12px 16px; flex-wrap:wrap; background:#F5F1EC; }
.qv-thumb {
  width: 58px; height: 58px; border-radius: 8px; overflow: hidden;
  border: 2px solid transparent; cursor: pointer; transition: border-color 0.2s;
  background: #e8e0d6;
}
.qv-thumb.active, .qv-thumb:hover { border-color: #7A1E3A; }
.qv-thumb img { width:100%; height:100%; object-fit:cover; }

/* ── Right Panel ── */
.qv-right {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow-y: auto;
  font-family: 'Poppins', sans-serif;
  padding: 28px 24px 20px;
  max-height: 85vh;
}
.qv-right::-webkit-scrollbar { width: 4px; }
.qv-right::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 2px; }

/* Category badge */
.qv-category-badge {
  display: inline-block;
  color: #7A1E3A;
  background: rgba(122,30,58,0.07);
  font-size: 10px; font-weight: 600;
  letter-spacing: 2px; text-transform: uppercase;
  padding: 5px 12px; border-radius: 20px; margin-bottom: 8px;
}

/* Title */
.qv-title {
  font-family: 'Playfair Display', serif !important;
  font-size: 22px !important; font-weight: 700 !important;
  color: #1A1A1A !important; margin-bottom: 8px !important;
  line-height: 1.3 !important;
}

/* Price */
.qv-price-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.qv-price { font-size: 22px; font-weight: 700; color: #7A1E3A; }
.qv-base-price { font-size: 14px; color: #999; text-decoration: line-through; }
.qv-discount-badge { background:#dc2626; color:#fff; font-size:10px; font-weight:700; padding:3px 8px; border-radius:20px; }

/* BIS Badges row */
.qv-bis-row { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.qv-bis-badge {
  display:inline-flex; align-items:center; gap:5px;
  font-size:10px; font-weight:700; padding:5px 11px; border-radius:20px;
  cursor:pointer; border:none; font-family:'Poppins',sans-serif;
  transition: all 0.2s;
}
.qv-bis-gold { background:linear-gradient(135deg,#D4AF37,#F5E6A3,#D4AF37); color:#1a1a1a; box-shadow:0 2px 8px rgba(212,175,55,0.25); }
.qv-bis-green { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.qv-bis-blue  { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }

/* Desc */
.qv-desc { font-size:12.5px; color:#555; line-height:1.7; margin-bottom:12px; }

/* Tabs */
.qv-tabs { display:flex; gap:2px; margin-bottom:14px; background:#F5F1EC; border-radius:10px; padding:4px; }
.qv-tab {
  flex:1; padding:8px 6px; border:none; background:none; border-radius:8px;
  font-size:11px; font-weight:600; color:#888; cursor:pointer;
  transition:all 0.2s; font-family:'Poppins',sans-serif; text-align:center;
}
.qv-tab.active { background:#fff; color:#7A1E3A; box-shadow:0 2px 6px rgba(0,0,0,0.08); }

/* Tab panels */
.qv-tab-panel { display:none; }
.qv-tab-panel.active { display:block; }

/* Specs grid */
.qv-specs-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px; }
.qv-spec-item { background:#FAF9F7; border-radius:8px; padding:10px 12px; }
.qv-spec-label { font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#aaa; font-weight:600; }
.qv-spec-val   { font-size:13px; font-weight:700; color:#1A1A1A; margin-top:2px; }

/* Purity switcher */
.qv-purity-row { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.qv-purity-btn {
  padding:6px 14px; border-radius:20px; font-size:11px; font-weight:700;
  border:1.5px solid #ddd; background:#fff; color:#666;
  cursor:pointer; font-family:'Poppins',sans-serif; transition:all 0.2s;
}
.qv-purity-btn.active {
  border-color:#D4AF37;
  background:linear-gradient(135deg,#D4AF37,#F5E6A3,#D4AF37);
  color:#1a1a1a;
}

/* EMI */
.qv-emi-btns { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
.qv-emi-btn {
  flex:1; min-width:60px; padding:8px 4px; border-radius:22px;
  font-size:11px; font-weight:700; cursor:pointer; text-align:center;
  border:1.5px solid #ddd; background:#fff; color:#666;
  font-family:'Poppins',sans-serif; transition:all 0.2s;
}
.qv-emi-btn.active { border-color:#7A1E3A; background:linear-gradient(135deg,#7A1E3A,#5a1529); color:#fff; }
.qv-emi-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; background:#FAF9F7; border-radius:10px; padding:12px; }
.qv-emi-cell { text-align:center; }
.qv-emi-lbl  { font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#aaa; font-weight:600; margin-bottom:3px; }
.qv-emi-val  { font-size:16px; font-weight:800; font-family:'Playfair Display',serif; }

/* Reviews */
.qv-review-scroll { overflow-x:auto; scrollbar-width:none; }
.qv-review-scroll::-webkit-scrollbar { display:none; }
.qv-review-row { display:flex; gap:10px; width:max-content; padding-bottom:4px; }
.qv-review-card {
  width:140px; border-radius:12px; overflow:hidden;
  box-shadow:0 3px 12px rgba(0,0,0,0.07); flex-shrink:0;
  background:#fff; border:1px solid rgba(0,0,0,0.04);
}
.qv-review-emoji { height:110px; display:flex; align-items:center; justify-content:center; font-size:36px; }
.qv-review-info { padding:8px 10px; }
.qv-review-name { font-size:10px; font-weight:700; color:#1A1A1A; }
.qv-review-city { font-size:9px; color:#aaa; margin-bottom:3px; }
.qv-review-stars { color:#D4AF37; font-size:10px; margin-bottom:3px; }
.qv-review-text { font-size:9px; color:#666; line-height:1.4; }

/* Qty row */
.qv-qty-row { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.qv-qty-label { font-size:12px; font-weight:600; color:#555; }
.qv-qty-box { display:flex; align-items:center; gap:0; border:1.5px solid #e0d4c8; border-radius:30px; overflow:hidden; }
.qv-qty-btn {
  width:34px; height:34px; background:none; border:none;
  font-size:16px; cursor:pointer; color:#7A1E3A; font-weight:700;
  transition:background 0.2s;
}
.qv-qty-btn:hover { background:rgba(122,30,58,0.05); }
.qv-qty-val { min-width:32px; text-align:center; font-size:14px; font-weight:700; color:#1A1A1A; }

/* Action Buttons */
.qv-btn-cart {
  width:100%; padding:14px; margin-bottom:8px;
  background:linear-gradient(135deg,#7A1E3A,#5a1529);
  color:#fff; border:none; border-radius:30px;
  font-size:13px; font-weight:700; cursor:pointer;
  font-family:'Poppins',sans-serif; letter-spacing:0.3px;
  box-shadow:0 6px 20px rgba(122,30,58,0.22);
  transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:8px;
}
.qv-btn-cart:hover { transform:translateY(-2px); box-shadow:0 10px 26px rgba(122,30,58,0.3); }
.qv-btn-row { display:flex; gap:8px; margin-bottom:10px; }

.qv-btn-wishlist {
  padding:11px 16px;
  background:none; border:1.5px solid #e0d4c8;
  border-radius:30px; color:#888; cursor:pointer;
  font-size:14px; transition:all 0.3s;
}
.qv-btn-wishlist:hover { color:#dc2626; border-color:#dc2626; }

/* Trust row */
.qv-trust-row { display:flex; gap:10px; flex-wrap:wrap; padding-top:10px; border-top:1px solid rgba(0,0,0,0.05); }
.qv-trust-item { display:flex; align-items:center; gap:5px; font-size:10px; color:#888; font-weight:500; }
.qv-trust-item i { color:#D4AF37; font-size:11px; }

/* BIS Certificate Modal inside QV */
.qv-cert-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(6px);
  z-index:200000; display:none; align-items:center; justify-content:center; padding:16px;
}
.qv-cert-overlay.show { display:flex; }
.qv-cert-box {
  background:#fff; border-radius:20px; width:440px; max-width:100%;
  overflow:hidden; animation:certUp 0.3s ease;
  box-shadow:0 20px 60px rgba(0,0,0,0.2);
}
@keyframes certUp { from{transform:translateY(24px);opacity:0} to{transform:translateY(0);opacity:1} }
.qv-cert-hdr { background:linear-gradient(135deg,#D4AF37,#F5E6A3,#D4AF37); padding:24px; text-align:center; }
.qv-cert-seal { width:60px; height:60px; background:linear-gradient(135deg,#7A1E3A,#5a1529); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; font-size:24px; box-shadow:0 4px 14px rgba(122,30,58,0.3); }
.qv-cert-hdr h3 { font-family:'Playfair Display',serif; font-size:19px; font-weight:700; color:#1a1a1a; margin:0 0 3px; }
.qv-cert-bd { padding:20px; }
.qv-cert-rw { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.05); font-family:'Poppins',sans-serif; }
.qv-cert-rw:last-child { border:none; }
.qv-cert-lbl { font-size:10px; color:#888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.qv-cert-vl  { font-size:12px; color:#1A1A1A; font-weight:700; }
.qv-cert-vl.gold { color:#D4AF37; }
.qv-cert-cbtn { display:block; width:calc(100% - 40px); margin:0 20px 20px; padding:12px; background:linear-gradient(135deg,#7A1E3A,#5a1529); color:#fff; border:none; border-radius:30px; font-size:13px; font-weight:700; cursor:pointer; font-family:'Poppins',sans-serif; }
</style>

<!-- Modal HTML -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true" style="z-index:99999;">
  <div class="modal-dialog modal-xl modal-dialog-centered qv-modal-dialog">
    <div class="modal-content" style="border-radius:20px; overflow:hidden; border:none; box-shadow:0 24px 70px rgba(0,0,0,0.18); max-height:90vh;">

      <!-- Header -->
      <div class="modal-header" style="border-bottom:1px solid rgba(0,0,0,0.05); padding:14px 22px; background:#FAF9F7;">
        <span style="font-family:'Playfair Display',serif; font-weight:700; color:#7A1E3A; font-size:15px;">✦ Product Details</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">

        <!-- Loading -->
        <div id="quickViewLoading" style="display:flex; justify-content:center; align-items:center; min-height:380px;">
          <div class="text-center">
            <div class="spinner-border" role="status" style="color:#D4AF37; width:2.5rem; height:2.5rem;"></div>
            <p style="margin-top:15px; color:#888; font-family:'Poppins',sans-serif; font-size:13px;">Fetching product details...</p>
          </div>
        </div>

        <!-- Content -->
        <div id="quickViewContent" style="display:none;">
          <div class="row g-0" style="max-height:85vh;">

            <!-- LEFT: Image Gallery -->
            <div class="col-md-5" style="border-right:1px solid rgba(0,0,0,0.05);">
              <div class="qv-img-wrap">
                <div class="qv-media-toggles">
                  <button type="button" class="qv-media-toggle active" data-media-type="photo"><i class="fas fa-image"></i> Photo</button>
                  <button type="button" class="qv-media-toggle" data-media-type="360"><i class="fas fa-sync-alt"></i> 360°</button>
                  <button type="button" class="qv-media-toggle" data-media-type="video"><i class="fas fa-play"></i> Video</button>
                </div>
                <img id="qvMainImg" src="" alt="Product Image">
                <video id="qvVideo" controls style="width: 100%; height: 420px; object-fit: contain; display: none; background: #000;" src="/visnavi jwallers/assets/video/bridal.mp4"></video>
                <div class="rotate-hint-qv" id="qvRotateHint" style="display: none;"><i class="fas fa-arrows-alt-h"></i> Drag to rotate</div>
              </div>
              <div class="qv-thumbnails" id="qvThumbnails"></div>
            </div>

            <!-- RIGHT: Product Info -->
            <div class="col-md-7">
              <div class="qv-right" id="qvRightPanel">

                <span class="qv-category-badge" id="qvCategory">Gold</span>
                <h3 class="qv-title" id="qvName">Loading...</h3>

                <!-- Price -->
                <div class="qv-price-row">
                  <span class="qv-price" id="qvPrice">&#8377;0</span>
                  <del class="qv-base-price" id="qvBasePrice" style="display:none;">&#8377;0</del>
                  <span class="qv-discount-badge" id="qvDiscount" style="display:none;">-0%</span>
                </div>

                <!-- BIS Badges -->
                <div class="qv-bis-row">
                  <button class="qv-bis-badge qv-bis-gold" id="qvCertBtn"><i class="fas fa-certificate"></i> BIS Hallmarked</button>
                  <span class="qv-bis-badge qv-bis-green"><i class="fas fa-shield-alt"></i> Certified Genuine</span>
                  <span class="qv-bis-badge qv-bis-blue"><i class="fas fa-recycle"></i> Lifetime Exchange</span>
                </div>

                <!-- Short Description -->
                <p class="qv-desc" id="qvShortDesc">Exquisite piece from our exclusive collection.</p>

                <!-- Tabs -->
                <div class="qv-tabs">
                  <button class="qv-tab active" data-tab="details">Details</button>
                  <button class="qv-tab" data-tab="purity">Purity</button>
                  <button class="qv-tab" data-tab="emi">EMI</button>
                  <button class="qv-tab" data-tab="reviews">Reviews</button>
                </div>

                <!-- Tab: Details -->
                <div class="qv-tab-panel active" id="qvTab-details">
                  <div class="qv-specs-grid">
                    <div class="qv-spec-item">
                      <div class="qv-spec-label">Metal</div>
                      <div class="qv-spec-val" id="qvMetal">Gold</div>
                    </div>
                    <div class="qv-spec-item">
                      <div class="qv-spec-label">Stone</div>
                      <div class="qv-spec-val" id="qvStone">—</div>
                    </div>
                    <div class="qv-spec-item">
                      <div class="qv-spec-label">Weight</div>
                      <div class="qv-spec-val" id="qvWeight">—</div>
                    </div>
                    <div class="qv-spec-item">
                      <div class="qv-spec-label">SKU</div>
                      <div class="qv-spec-val" id="qvSku">—</div>
                    </div>
                  </div>
                </div>

                <!-- Tab: Purity -->
                <div class="qv-tab-panel" id="qvTab-purity">
                  <p style="font-size:11px; color:#888; margin-bottom:10px;">Tap a purity to see adjusted price:</p>
                  <div class="qv-purity-row" id="qvPurityRow">
                    <button class="qv-purity-btn active" data-multiplier="1.0">22K Gold</button>
                    <button class="qv-purity-btn" data-multiplier="0.755">18K Gold</button>
                    <button class="qv-purity-btn" data-multiplier="1.09">24K Gold</button>
                    <button class="qv-purity-btn" data-multiplier="1.35">Platinum</button>
                  </div>
                  <div style="background:#FAF9F7; border-radius:10px; padding:12px 14px; font-size:12px; color:#888; line-height:1.6;">
                    <i class="fas fa-info-circle" style="color:#D4AF37;"></i> Price changes based on metal purity. 22K is our standard. Making charges & GST apply separately.
                  </div>
                </div>

                <!-- Tab: EMI -->
                <div class="qv-tab-panel" id="qvTab-emi">
                  <div class="qv-emi-btns" id="qvEmiBtns">
                    <button class="qv-emi-btn active" data-months="3">3 Mo.</button>
                    <button class="qv-emi-btn" data-months="6">6 Mo.</button>
                    <button class="qv-emi-btn" data-months="12">12 Mo.</button>
                    <button class="qv-emi-btn" data-months="24">24 Mo.</button>
                  </div>
                  <div class="qv-emi-grid">
                    <div class="qv-emi-cell">
                      <div class="qv-emi-lbl">Monthly EMI</div>
                      <div class="qv-emi-val" id="qvEmiMonthly" style="color:#7A1E3A;">&#8377;0</div>
                    </div>
                    <div class="qv-emi-cell">
                      <div class="qv-emi-lbl">Interest</div>
                      <div class="qv-emi-val" id="qvEmiInterest" style="color:#D4AF37;">&#8377;0</div>
                    </div>
                    <div class="qv-emi-cell">
                      <div class="qv-emi-lbl">Total</div>
                      <div class="qv-emi-val" id="qvEmiTotal" style="color:#333;">&#8377;0</div>
                    </div>
                  </div>
                  <p style="font-size:9px; color:#ccc; margin-top:8px; font-family:'Poppins',sans-serif;">*Indicative at 12% p.a. Subject to bank approval.</p>
                </div>

                <!-- Tab: Reviews -->
                <div class="qv-tab-panel" id="qvTab-reviews">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span style="font-size:11px; font-weight:700; color:#1A1A1A;">Customer Photos</span>
                    <span style="color:#D4AF37; font-size:12px;">&#9733;&#9733;&#9733;&#9733;&#9733; <span style="color:#888; font-size:10px;">4.9 (128)</span></span>
                  </div>
                  <div class="qv-review-scroll">
                    <div class="qv-review-row" id="qvReviewRow">
                      <!-- populated by JS -->
                    </div>
                  </div>
                </div>

                <!-- Qty + Size Guide -->
                <div class="qv-qty-row" style="margin-top:14px;">
                  <span class="qv-qty-label">Qty</span>
                  <div class="qv-qty-box">
                    <button class="qv-qty-btn" id="qvQtyMinus">−</button>
                    <span class="qv-qty-val" id="qvQtyVal">1</span>
                    <button class="qv-qty-btn" id="qvQtyPlus">+</button>
                  </div>
                  <button type="button" id="qvRingSizeTrigger" style="display:none; background:none; border:none; font-size:11px; color:#D4AF37; font-weight:600; cursor:pointer; padding:0; font-family:'Poppins',sans-serif;">
                    <i class="fas fa-ruler-combined"></i> Size Guide
                  </button>
                </div>

                <!-- Add to Cart -->
                <button type="button" class="qv-btn-cart" id="qvAddToCart">
                  <i class="fas fa-shopping-bag"></i> Add to Bag
                </button>

                <!-- Wishlist -->
                <div class="qv-btn-row">
                  <button type="button" class="qv-btn-wishlist" id="qvWishlistBtn" title="Add to Wishlist" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="far fa-heart"></i> Add to Wishlist
                  </button>
                </div>

                <!-- Trust Badges -->
                <div class="qv-trust-row">
                  <div class="qv-trust-item"><i class="fas fa-certificate"></i> BIS Hallmarked</div>
                  <div class="qv-trust-item"><i class="fas fa-undo"></i> 30-Day Returns</div>
                  <div class="qv-trust-item"><i class="fas fa-shield-alt"></i> Lifetime Polish</div>
                  <div class="qv-trust-item"><i class="fas fa-truck"></i> Free Delivery</div>
                </div>

              </div>
            </div>

          </div><!-- row -->
        </div><!-- quickViewContent -->

      </div><!-- modal-body -->
    </div>
  </div>
</div>

<!-- BIS Certificate Mini-Modal -->
<div class="qv-cert-overlay" id="qvCertOverlay">
  <div class="qv-cert-box">
    <div class="qv-cert-hdr">
      <div class="qv-cert-seal">&#127885;</div>
      <h3>Certificate of Authenticity</h3>
      <p style="font-size:11px; color:rgba(0,0,0,0.6); font-family:'Poppins',sans-serif; margin:0;">Vaishnavi Jewellers — Government of India</p>
    </div>
    <div class="qv-cert-bd">
      <div class="qv-cert-rw"><span class="qv-cert-lbl">BIS Hallmark No.</span><span class="qv-cert-vl gold" id="certHallmark">BIS-VJ-000001</span></div>
      <div class="qv-cert-rw"><span class="qv-cert-lbl">Purity</span><span class="qv-cert-vl" id="certPurity">22K Gold — BIS Certified</span></div>
      <div class="qv-cert-rw"><span class="qv-cert-lbl">Hallmarking Centre</span><span class="qv-cert-vl">Bureau of Indian Standards</span></div>
      <div class="qv-cert-rw"><span class="qv-cert-lbl">Stone Certification</span><span class="qv-cert-vl">GIA Graded (if applicable)</span></div>
      <div class="qv-cert-rw"><span class="qv-cert-lbl">Warranty</span><span class="qv-cert-vl">Lifetime Manufacturer Warranty</span></div>
      <div class="qv-cert-rw"><span class="qv-cert-lbl">Exchange Policy</span><span class="qv-cert-vl">Lifetime Exchange at Full Value</span></div>
    </div>
    <button class="qv-cert-cbtn" onclick="document.getElementById('qvCertOverlay').classList.remove('show')"><i class="fas fa-check me-2"></i>Shop with Confidence</button>
  </div>
</div>

<!-- ==================== QUICK VIEW JAVASCRIPT ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {

  const modal       = document.getElementById('quickViewModal');
  const loading     = document.getElementById('quickViewLoading');
  const content     = document.getElementById('quickViewContent');
  const qvMainImg   = document.getElementById('qvMainImg');
  const qvThumbs    = document.getElementById('qvThumbnails');
  const qvCategory  = document.getElementById('qvCategory');
  const qvName      = document.getElementById('qvName');
  const qvPrice     = document.getElementById('qvPrice');
  const qvBasePrice = document.getElementById('qvBasePrice');
  const qvDiscount  = document.getElementById('qvDiscount');
  const qvShortDesc = document.getElementById('qvShortDesc');
  const qvMetal     = document.getElementById('qvMetal');
  const qvStone     = document.getElementById('qvStone');
  const qvWeight    = document.getElementById('qvWeight');
  const qvSku       = document.getElementById('qvSku');
  const qvQtyVal    = document.getElementById('qvQtyVal');
  const qvQtyMinus  = document.getElementById('qvQtyMinus');
  const qvQtyPlus   = document.getElementById('qvQtyPlus');
  const qvAddCart   = document.getElementById('qvAddToCart');

  const qvWishlist  = document.getElementById('qvWishlistBtn');
  const qvCertBtn   = document.getElementById('qvCertBtn');
  const qvCertOv    = document.getElementById('qvCertOverlay');

  let currentProductId  = null;
  let currentProductImgUrl = '';
  let qvBasePrice_num   = 0;
  let qty = 1;

  // ── Qty ──────────────────────────────────────────────────────
  qvQtyMinus.addEventListener('click', () => { if(qty>1){qty--;qvQtyVal.textContent=qty;} });
  qvQtyPlus.addEventListener('click',  () => { if(qty<99){qty++;qvQtyVal.textContent=qty;} });

  // ── Tabs ─────────────────────────────────────────────────────
  document.querySelectorAll('.qv-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.qv-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.qv-tab-panel').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      const panel = document.getElementById('qvTab-' + this.dataset.tab);
      if(panel) panel.classList.add('active');
    });
  });

  // ── BIS Certificate Modal ─────────────────────────────────────
  if(qvCertBtn && qvCertOv) {
    qvCertBtn.addEventListener('click', () => qvCertOv.classList.add('show'));
    qvCertOv.addEventListener('click', e => { if(e.target===qvCertOv) qvCertOv.classList.remove('show'); });
  }

  // ── Wishlist heart toggle ─────────────────────────────────────
  if(qvWishlist) {
    qvWishlist.addEventListener('click', function() {
      const icon = this.querySelector('i');
      icon.className = icon.className.includes('far') ? 'fas fa-heart' : 'far fa-heart';
      this.style.color = icon.className.includes('fas') ? '#dc2626' : '#888';
      this.style.borderColor = icon.className.includes('fas') ? '#dc2626' : '#e0d4c8';
    });
  }

  // ── EMI Calculator ────────────────────────────────────────────
  const emiBtns     = document.querySelectorAll('.qv-emi-btn');
  const emiMonthly  = document.getElementById('qvEmiMonthly');
  const emiInterest = document.getElementById('qvEmiInterest');
  const emiTotal    = document.getElementById('qvEmiTotal');
  let emiBasePrice  = 0;

  function calcEMI(n) {
    if(!n || !emiBasePrice) return;
    const r = 12/100/12, P = emiBasePrice;
    const emi = P*r*Math.pow(1+r,n)/(Math.pow(1+r,n)-1);
    const tot = emi*n;
    const intr= tot-P;
    if(emiMonthly)  emiMonthly.innerHTML  = '&#8377;'+Math.round(emi).toLocaleString('en-IN');
    if(emiInterest) emiInterest.innerHTML = '&#8377;'+Math.round(intr).toLocaleString('en-IN');
    if(emiTotal)    emiTotal.innerHTML    = '&#8377;'+Math.round(tot).toLocaleString('en-IN');
  }
  emiBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      emiBtns.forEach(b => { b.classList.remove('active'); });
      this.classList.add('active');
      calcEMI(parseInt(this.dataset.months));
    });
  });

  // ── Purity Switcher ───────────────────────────────────────────
  const purityBtns = document.querySelectorAll('#qvPurityRow .qv-purity-btn');
  purityBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      purityBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const adj = Math.round(qvBasePrice_num * parseFloat(this.dataset.multiplier||1));
      qvPrice.innerHTML = '&#8377;' + adj.toLocaleString('en-IN');
    });
  });

  // ── Reviews ───────────────────────────────────────────────────
  const reviewData = [
    ['Priya M.','Mumbai','💍','Absolutely stunning!'],
    ['Rekha S.','Ahmedabad','📿','So elegant and lightweight!'],
    ['Kavya R.','Surat','💎','She cried happy tears!'],
    ['Anita D.','Delhi','✨','Gorgeous packaging too!'],
    ['Sunita K.','Rajkot','👑','Everyone complimented it!'],
  ];
  const reviewRow = document.getElementById('qvReviewRow');
  if(reviewRow) {
    reviewData.forEach(r => {
      const card = document.createElement('div');
      card.className = 'qv-review-card';
      card.innerHTML = `<div class="qv-review-emoji" style="background:linear-gradient(135deg,#fdf6e4,#f5e6a3);">${r[2]}</div>
        <div class="qv-review-info">
          <div class="qv-review-name">${r[0]}</div>
          <div class="qv-review-city">${r[1]} · Verified</div>
          <div class="qv-review-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
          <div class="qv-review-text">"${r[3]}"</div>
        </div>`;
      reviewRow.appendChild(card);
    });
  }

  // ── Media Toggles, Zoom, and 360 Drag Viewer ───────────────────────────
  let isDragging = false, lastX = 0, imgFlipped = false;
  let currentMediaType = 'photo'; // photo | 360 | video

  const hint360 = document.getElementById('qvRotateHint');
  const qvVideoElement = document.getElementById('qvVideo');
  const mediaToggleButtons = document.querySelectorAll('.qv-media-toggle');
  const imgWrap = document.querySelector('.qv-img-wrap');

  // Media toggles handler
  mediaToggleButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      mediaToggleButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      const type = this.getAttribute('data-media-type');
      currentMediaType = type;

      // Reset transforms
      qvMainImg.style.transform = 'scale(1)';
      qvMainImg.style.transformOrigin = 'center center';
      imgFlipped = false;

      if (type === 'photo') {
        qvMainImg.style.display = 'block';
        qvMainImg.style.cursor = 'zoom-in';
        if (qvVideoElement) { qvVideoElement.style.display = 'none'; qvVideoElement.pause(); }
        if (hint360) hint360.style.display = 'none';
        if (qvThumbs) qvThumbs.style.display = 'flex';
      } else if (type === '360') {
        qvMainImg.style.display = 'block';
        qvMainImg.style.cursor = 'grab';
        if (qvVideoElement) { qvVideoElement.style.display = 'none'; qvVideoElement.pause(); }
        if (hint360) { hint360.style.display = 'block'; hint360.style.opacity = '1'; }
        if (qvThumbs) qvThumbs.style.display = 'none';
      } else if (type === 'video') {
        qvMainImg.style.display = 'none';
        if (qvVideoElement) {
          qvVideoElement.style.display = 'block';
          qvVideoElement.play().catch(() => {});
        }
        if (hint360) hint360.style.display = 'none';
        if (qvThumbs) qvThumbs.style.display = 'none';
      }
    });
  });

  // Inner Image Zoom (Only active on 'photo' mode)
  if (imgWrap && qvMainImg) {
    imgWrap.addEventListener('mousemove', e => {
      if (currentMediaType !== 'photo') return;
      const rect = imgWrap.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const xPercent = (x / rect.width) * 100;
      const yPercent = (y / rect.height) * 100;
      qvMainImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
      qvMainImg.style.transform = 'scale(2.2)';
    });

    imgWrap.addEventListener('mouseleave', () => {
      if (currentMediaType !== 'photo') return;
      qvMainImg.style.transform = 'scale(1)';
      qvMainImg.style.transformOrigin = 'center center';
    });
  }

  // 360 Drag rotation (Only active on '360' mode)
  if (imgWrap) {
    imgWrap.addEventListener('mousedown', e => {
      if (currentMediaType !== '360') return;
      isDragging = true;
      lastX = e.clientX;
      imgWrap.style.cursor = 'grabbing';
      e.preventDefault();
    });
    window.addEventListener('mousemove', e => {
      if (!isDragging || currentMediaType !== '360') return;
      const d = e.clientX - lastX;
      lastX = e.clientX;
      if (Math.abs(d) > 8) {
        imgFlipped = d < 0 ? !imgFlipped : imgFlipped;
        qvMainImg.style.transform = imgFlipped ? 'scaleX(-1)' : 'scaleX(1)';
      }
    });
    window.addEventListener('mouseup', () => {
      isDragging = false;
      if (imgWrap && currentMediaType === '360') imgWrap.style.cursor = 'grab';
    });
    imgWrap.addEventListener('touchstart', e => {
      if (currentMediaType !== '360') return;
      isDragging = true;
      lastX = e.touches[0].clientX;
    }, {passive: true});
    imgWrap.addEventListener('touchmove', e => {
      if (!isDragging || currentMediaType !== '360') return;
      const d = e.touches[0].clientX - lastX;
      lastX = e.touches[0].clientX;
      if (Math.abs(d) > 8) {
        imgFlipped = d < 0 ? !imgFlipped : imgFlipped;
        qvMainImg.style.transform = imgFlipped ? 'scaleX(-1)' : 'scaleX(1)';
      }
    }, {passive: true});
    imgWrap.addEventListener('touchend', () => { isDragging = false; });
  }

  // ── Open Quick View ───────────────────────────────────────────
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-qv-id]') || e.target.closest('[data-quick-view]');
    if(!btn) return;
    e.preventDefault();
    const pid = btn.getAttribute('data-qv-id') || btn.getAttribute('data-product-id');
    if(pid) openQV(pid);
  });

  function openQV(pid) {
    qty = 1; if(qvQtyVal) qvQtyVal.textContent = '1';
    imgFlipped = false; if(qvMainImg) qvMainImg.style.transform='scaleX(1)';
    currentProductId = pid;
    currentMediaType = 'photo';
    document.querySelectorAll('.qv-media-toggle').forEach((btn, idx) => {
      btn.classList.toggle('active', idx === 0);
    });
    if (qvMainImg) {
      qvMainImg.style.display = 'block';
      qvMainImg.style.transform = 'scale(1)';
      qvMainImg.style.transformOrigin = 'center center';
      qvMainImg.style.cursor = 'zoom-in';
    }
    if (qvVideoElement) {
      qvVideoElement.style.display = 'none';
      qvVideoElement.pause();
    }
    if (hint360) hint360.style.display = 'none';
    if (qvThumbs) qvThumbs.style.display = 'flex';

    // Reset tabs to first tab
    document.querySelectorAll('.qv-tab').forEach((t,i) => t.classList.toggle('active', i===0));
    document.querySelectorAll('.qv-tab-panel').forEach((p,i) => p.classList.toggle('active', i===0));

    // Reset purity buttons
    purityBtns.forEach((b,i) => b.classList.toggle('active', i===0));

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    loading.style.display = 'flex';
    content.style.display = 'none';

    fetch('/visnavi jwallers/api/products.php?id=' + pid)
      .then(r => r.json())
      .then(data => {
        if(!data.product) return;
        const p = data.product;

        // Basic info
        qvCategory.textContent = p.category || p.metal || 'Gold';
        qvName.textContent = p.name;

        // Pricing
        const disc = parseFloat(p.discount_percent || 0);
        const base = parseFloat(p.base_price || 0);
        const sale = disc > 0 ? base*(1-disc/100) : base;
        qvBasePrice_num = Math.round(sale);
        emiBasePrice    = Math.round(sale);
        qvPrice.innerHTML = '&#8377;' + Math.round(sale).toLocaleString('en-IN');
        if(disc > 0) {
          qvBasePrice.innerHTML = '&#8377;' + Math.round(base).toLocaleString('en-IN');
          qvBasePrice.style.display = 'inline';
          qvDiscount.textContent = '-' + disc + '%';
          qvDiscount.style.display = 'inline';
        } else {
          qvBasePrice.style.display = 'none';
          qvDiscount.style.display  = 'none';
        }

        // Specs
        qvMetal.textContent  = p.metal  || 'Gold';
        qvStone.textContent  = p.stone  || '—';
        qvWeight.textContent = p.weight_grams ? p.weight_grams+'g' : '—';
        qvSku.textContent    = p.sku || '—';
        qvShortDesc.textContent = p.short_description || p.description || 'A beautifully handcrafted piece from our exclusive collection.';

        // BIS certificate fields
        const certNum = document.getElementById('certHallmark');
        const certPur = document.getElementById('certPurity');
        if(certNum) certNum.textContent = 'BIS-VJ-' + String(p.id||1).padStart(6,'0');
        if(certPur) certPur.textContent = (p.metal||'22K Gold') + ' — BIS Certified';

        // EMI
        calcEMI(3);

        // Ring size trigger
        const sizeTrigger = document.getElementById('qvRingSizeTrigger');
        const isRing = (p.category||p.name||'').toLowerCase().includes('ring');
        if(sizeTrigger) {
          sizeTrigger.style.display = isRing ? 'inline-flex' : 'none';
          sizeTrigger.onclick = () => {
            const rsfOverlay = document.getElementById('rsfOverlay');
            if(rsfOverlay) { rsfOverlay.classList.add('show'); document.body.style.overflow='hidden'; }
          };
        }

        // Images
        const fallback = '/visnavi jwallers/assets/images/Rings.png';
        if(p.images && p.images.length > 0) {
          const firstUrl = '/visnavi jwallers/' + p.images[0];
          qvMainImg.src = firstUrl;
          currentProductImgUrl = firstUrl;
          qvThumbs.innerHTML = '';
          p.images.forEach((img, i) => {
            const url = '/visnavi jwallers/' + img;
            const t = document.createElement('div');
            t.className = 'qv-thumb' + (i===0?' active':'');
            t.innerHTML = `<img src="${url}" alt="thumb">`;
            t.addEventListener('click', () => {
              qvMainImg.src = url;
              currentProductImgUrl = url;
              qvMainImg.style.transform = 'scaleX(1)';
              imgFlipped = false;
              document.querySelectorAll('.qv-thumb').forEach(x=>x.classList.remove('active'));
              t.classList.add('active');
            });
            qvThumbs.appendChild(t);
          });
        } else {
          qvMainImg.src = fallback;
          currentProductImgUrl = fallback;
          qvThumbs.innerHTML = '';
        }

        loading.style.display = 'none';
        content.style.display = 'block';
      })
      .catch(() => { loading.style.display='none'; });
  }

  // ── Add to Cart ───────────────────────────────────────────────
  if(qvAddCart) {
    qvAddCart.addEventListener('click', function() {
      if(!currentProductId) return;
      const orig = this.innerHTML;
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
      this.disabled = true;
      const fd = new FormData();
      fd.append('action', 'add');
      fd.append('product_id', currentProductId);
      fd.append('qty', qty);
      fetch('api/cart-toggle.php', {method:'POST', body:fd, credentials:'same-origin'})
        .then(r => r.json())
        .then(data => {
          this.innerHTML = '<i class="fas fa-check"></i> Added!';
          this.style.background = 'linear-gradient(135deg,#16a34a,#15803d)';
          setTimeout(() => {
            this.innerHTML = orig;
            this.style.background = '';
            this.disabled = false;
          }, 2000);
          // Update cart count
          const cc = document.getElementById('cartCount');
          if(cc && data.cart_count !== undefined) cc.textContent = data.cart_count;
          // Open cart drawer and fetch items
          if(window.openCartDrawer) {
            window.openCartDrawer();
          } else {
            const drawer = document.getElementById('cartDrawer');
            if(drawer) drawer.classList.add('open');
          }
        })
        .catch(() => { this.innerHTML = orig; this.disabled = false; });
    });
  }



  // ── View Details button → open QV (we removed it, no product.php) ──
  // Handle direct URL like shop.php?open=123
  const urlP = new URLSearchParams(window.location.search);
  if(urlP.get('open')) openQV(urlP.get('open'));

});
</script>
