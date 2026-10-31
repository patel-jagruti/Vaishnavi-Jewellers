/* Luxury Cart/Wishlist UI (AJAX)
   Fixes:
   - Uses correct BASE_URL for /visnavi jwallers/ deployments.
   - Removes noisy console logs.
   - Expects consistent JSON response from backend.
*/

(() => {
  const baseFromDocument = () => {
    // If <base href="/visnavi jwallers/"> exists, document.baseURI will already include it.
    // document.baseURI is absolute; we trim to the origin + base path.
    const baseEl = document.querySelector('base[href]');
    if (baseEl) {
      const href = baseEl.getAttribute('href') || '';
      if (!href) return '';
      // Ensure ends with /
      return href.endsWith('/') ? href : href + '/';
    }

    // Fallback: infer from current URL path by taking the first segment after origin.
    try {
      const u = new URL(document.baseURI);
      const parts = u.pathname.split('/').filter(Boolean); // e.g. ["visnavi jwallers", "pages", "shop.php"]
      if (parts.length > 0) {
        return '/' + parts[0] + '/';
      }
    } catch (e) {}
    return '';
  };

  const BASE_URL = baseFromDocument() || '/visnavi jwallers/';

  const apiUrl = (path) => {
    // path like "api/cart-toggle.php"
    const base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
    return base + String(path).replace(/^\/+/, '');
  };

  const toast = (msg, isError = false) => {
    const el = document.createElement('div');
    el.className = 'toast' + (isError ? ' error' : '');
    el.textContent = msg;
    document.body.appendChild(el);
    requestAnimationFrame(() => el.classList.add('show'));
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }, 2500);
  };

  const setButtonLoading = (btn, isLoading) => {
    if (!btn) return;
    if (isLoading) {
      btn.disabled = true;
      btn.style.opacity = '0.6';
      btn.style.cursor = 'not-allowed';
      // Only replace text if it doesn't contain HTML icons (like <i>) to preserve structure
      if (!btn.querySelector('i') && btn.textContent.trim().length > 0) {
        btn.dataset._oldText = btn.textContent;
        btn.textContent = '…';
      }
    } else {
      btn.disabled = false;
      btn.style.opacity = '';
      btn.style.cursor = '';
      if (btn.dataset._oldText !== undefined) {
        btn.textContent = btn.dataset._oldText;
        delete btn.dataset._oldText;
      }
    }
  };

  const updateNavbarCartBadge = (cartCount) => {
    const count = Number(cartCount || 0);
    let badge = document.querySelector('[data-count-role="cart"]');

    if (count > 0) {
      if (!badge) {
        const iconDiv = document.querySelector('.lux-actions a[title="Cart"] .action-icon');
        if (iconDiv) {
          badge = document.createElement('span');
          badge.className = 'action-badge';
          badge.setAttribute('data-count-role', 'cart');
          iconDiv.appendChild(badge);
        }
      }
      if (badge) {
        badge.textContent = String(count);
        badge.style.display = '';
      }
    } else {
      if (badge) {
        badge.style.display = 'none';
      }
    }
  };

  const updateNavbarWishlistBadge = (wishCount) => {
    const count = Number(wishCount || 0);
    let badge = document.querySelector('[data-count-role="wishlist"]');

    if (count > 0) {
      if (!badge) {
        const iconDiv = document.querySelector('.lux-actions a[title="Wishlist"] .action-icon');
        if (iconDiv) {
          badge = document.createElement('span');
          badge.className = 'action-badge';
          badge.setAttribute('data-count-role', 'wishlist');
          iconDiv.appendChild(badge);
        }
      }
      if (badge) {
        badge.textContent = String(count);
        badge.style.display = '';
      }
    } else {
      if (badge) {
        badge.style.display = 'none';
      }
    }
  };

  const attachCouponHandler = () => {
    const couponForm = document.querySelector('.coupon-form');
    if (!couponForm) return;

    couponForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = couponForm.querySelector('.coupon-input');
      const btn = couponForm.querySelector('.coupon-btn');
      const code = (input?.value || '').trim();

      if (!code) {
        toast('Please enter a coupon code', true);
        return;
      }

      setButtonLoading(btn, true);
      try {
        const res = await fetch(apiUrl('api/apply-coupon.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({ coupon_code: code })
        });

        const data = await res.json().catch(() => null);
        if (data && data.ok) {
          toast(data.message || 'Coupon applied!');
          window.location.reload();
        } else {
          toast(data?.message || 'Invalid coupon', true);
        }
      } catch (err) {
        toast('Network error', true);
      } finally {
        setButtonLoading(btn, false);
      }
    });
  };

  const attachCartHandlers = () => {
    const minusBtns = document.querySelectorAll('[data-cart-qty-minus]');
    const plusBtns = document.querySelectorAll('[data-cart-qty-plus]');
    const removeBtns = document.querySelectorAll('[data-cart-remove]');
    const wishlistRemoveBtns = document.querySelectorAll('[data-wishlist-remove]');
    const cartAddBtns = document.querySelectorAll('[data-cart-add]');

    const sendCart = async (productId, action) => {
      console.log('Sending cart request:', { product_id: productId, action });
      const res = await fetch(apiUrl('api/cart-toggle.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: new URLSearchParams({ product_id: String(productId), action })
      });
      console.log('Response status:', res.status);
      const data = await res.json().catch((e) => {
        console.log('JSON parse error:', e);
        return null;
      });
      console.log('Response data:', data);
      return { httpOk: res.ok, data };
    };

    const sendWishlistRemove = async (productId) => {
      const res = await fetch(apiUrl('api/wishlist-toggle.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: new URLSearchParams({ product_id: String(productId), action: 'remove' })
      });
      const data = await res.json().catch(() => null);
      return { httpOk: res.ok, data };
    };

    const updateSummaryAndRow = (pid, newQty, r) => {
      // 1. Update the row elements
      const row = document.querySelector(`[data-item-row="${pid}"]`);
      if (row) {
        if (newQty <= 0) {
          // Remove row from DOM
          row.style.transition = 'all 0.5s ease';
          row.style.opacity = '0';
          row.style.transform = 'translateX(50px)';
          setTimeout(() => {
            row.remove();
            checkEmptyCart();
          }, 500);
        } else {
          // Update quantity and line total
          const qtyVal = row.querySelector('.qty-value');
          if (qtyVal) qtyVal.textContent = newQty;
          
          const lineTotalEl = row.querySelector('.line-total');
          const unitPrice = parseFloat(row.getAttribute('data-unit-price') || '0');
          if (lineTotalEl && unitPrice > 0) {
            const lineTotal = unitPrice * newQty;
            lineTotalEl.textContent = 'Rs. ' + lineTotal.toLocaleString('en-IN', { maximumFractionDigits: 0 });
          }
        }
      }
      
      // 2. Update order summary values dynamically in the DOM
      const subtotalEl = document.getElementById('summary-subtotal');
      const gstEl = document.getElementById('summary-gst');
      const totalEl = document.getElementById('summary-total');
      const shippingWrap = document.getElementById('summary-shipping-wrap');
      const shippingFeeRowWrap = document.getElementById('summary-shipping-fee-row-wrap');
      
      if (subtotalEl && r.data.formatted_subtotal) {
        subtotalEl.textContent = 'Rs. ' + r.data.formatted_subtotal;
      }
      if (gstEl && r.data.formatted_tax) {
        gstEl.textContent = 'Rs. ' + r.data.formatted_tax;
      }
      if (totalEl && r.data.formatted_total) {
        totalEl.textContent = 'Rs. ' + r.data.formatted_total;
      }
      
      // Update Shipping logic
      if (shippingWrap) {
        if (r.data.shipping === 0) {
          shippingWrap.innerHTML = '<div class="summary-row shipping-free"><span>Shipping</span><span><i class="fas fa-truck"></i> FREE</span></div>';
          if (shippingFeeRowWrap) shippingFeeRowWrap.innerHTML = '';
        } else {
          shippingWrap.innerHTML = `
            <div class="free-shipping-progress">
              <div class="progress-label">Add Rs. ${r.data.free_shipping_needed} more for FREE shipping!</div>
              <div class="progress-bar-wrap"><div class="progress-bar" style="width: ${r.data.free_shipping_percent}%"></div></div>
              <div class="progress-text">${Math.round(r.data.free_shipping_percent)}% towards free shipping</div>
            </div>
          `;
          if (shippingFeeRowWrap) {
            shippingFeeRowWrap.innerHTML = `
              <div class="summary-row"><span>Shipping</span><span id="summary-shipping-fee">Rs. ${r.data.formatted_shipping}</span></div>
            `;
          }
        }
      }
      
      // Update cart title count
      const cartTitle = document.querySelector('.cart-title');
      if (cartTitle) {
        let currentCount = 0;
        document.querySelectorAll('[data-item-row]').forEach(itemRow => {
          const rowId = itemRow.getAttribute('data-item-row');
          if (rowId === String(pid) && newQty <= 0) {
            // Deleted
          } else {
            currentCount += 1;
          }
        });
        cartTitle.textContent = `Your Cart (${currentCount} ${currentCount === 1 ? 'Item' : 'Items'})`;
      }
    };
    
    const checkEmptyCart = () => {
      const remainingItems = document.querySelectorAll('[data-item-row]');
      if (remainingItems.length === 0) {
        const wrap = document.getElementById('cart-container-wrap');
        if (wrap) {
          wrap.innerHTML = `
            <!-- Empty Cart -->
            <div class="empty-cart" style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin: 40px 0;">
              <div class="empty-icon" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i></div>
              <h3 class="empty-title" style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #333; margin-bottom: 10px;">Your cart is empty</h3>
              <p class="empty-text" style="color: #666; margin-bottom: 25px;">Looks like you haven't added anything to your cart yet.</p>
              <a href="pages/shop.php" class="btn-shop-now" style="background: #a52a2a; color: #fff; padding: 14px 30px; border: none; border-radius: 30px; font-weight: 600; text-decoration: none; display: inline-block;">Shop Now</a>
            </div>
          `;
        }
      }
    };

    minusBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const pid = btn.getAttribute('data-product-id');
        if (!pid) return;
        
        const row = document.querySelector(`[data-item-row="${pid}"]`);
        const qtyValEl = row ? row.querySelector('.qty-value') : null;
        const currentQty = qtyValEl ? parseInt(qtyValEl.textContent || '1') : 1;
        
        if (currentQty <= 1) {
          const removeBtn = row ? row.querySelector('[data-cart-remove]') : null;
          if (removeBtn) {
            removeBtn.click();
          }
          return;
        }
        
        setButtonLoading(btn, true);
        try {
          const r = await sendCart(pid, 'remove_one');
          if (r.data?.ok) {
            toast(r.data.message || 'Updated cart');
            updateNavbarCartBadge(r.data.cart_count);
            updateSummaryAndRow(pid, currentQty - 1, r);
          } else {
            toast(r.data?.message || 'Cart update failed', true);
          }
        } catch (err) {
          toast('Network error', true);
        } finally {
          setButtonLoading(btn, false);
        }
      });
    });

    plusBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const pid = btn.getAttribute('data-product-id');
        if (!pid) return;
        
        const row = document.querySelector(`[data-item-row="${pid}"]`);
        const qtyValEl = row ? row.querySelector('.qty-value') : null;
        const currentQty = qtyValEl ? parseInt(qtyValEl.textContent || '1') : 1;
        
        if (currentQty >= 99) {
          toast('Maximum limit of 99 reached', true);
          return;
        }
        
        setButtonLoading(btn, true);
        try {
          const r = await sendCart(pid, 'add');
          if (r.data?.ok) {
            toast(r.data.message || 'Updated cart');
            updateNavbarCartBadge(r.data.cart_count);
            updateSummaryAndRow(pid, currentQty + 1, r);
          } else {
            toast(r.data?.message || 'Cart update failed', true);
          }
        } catch (err) {
          toast('Network error', true);
        } finally {
          setButtonLoading(btn, false);
        }
      });
    });

    removeBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const pid = btn.getAttribute('data-product-id');
        if (!pid) return;
        if (!confirm('Remove this item from cart?')) return;
        setButtonLoading(btn, true);
        try {
          const r = await sendCart(pid, 'remove');
          if (r.data?.ok) {
            toast(r.data.message || 'Item removed');
            updateNavbarCartBadge(r.data.cart_count);
            updateSummaryAndRow(pid, 0, r);
          } else {
            toast(r.data?.message || 'Failed to remove', true);
          }
        } catch (err) {
          toast('Network error', true);
        } finally {
          setButtonLoading(btn, false);
        }
      });
    });

    wishlistRemoveBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const pid = btn.getAttribute('data-product-id');
        if (!pid) return;
        setButtonLoading(btn, true);
        try {
          const r = await sendWishlistRemove(pid);
          if (r.data?.ok) {
            toast(r.data.message || 'Removed from wishlist');
            updateNavbarWishlistBadge(r.data.wishlist_count);
            
            // Smoothly remove card from DOM without full page reload
            const card = document.querySelector(`.wish-card[data-item-row="${pid}"]`);
            if (card) {
              card.style.transition = 'all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1)';
              card.style.opacity = '0';
              card.style.transform = 'scale(0.9) translateY(20px)';
              setTimeout(() => {
                card.remove();
                const remaining = document.querySelectorAll('.wish-card');
                if (remaining.length === 0) {
                  const container = document.getElementById('wishlist-container-wrap');
                  if (container) {
                    container.innerHTML = `
                      <div class="wish-empty-wrap">
                        <div class="wish-empty-icon"><i class="far fa-heart"></i></div>
                        <h3 class="wish-empty-title">Your Wishlist is Empty</h3>
                        <p class="wish-empty-desc">Explore our exquisite collections of gold, diamond, and silver jewellery. Add items you love to your wishlist to keep track of them.</p>
                        <a href="pages/shop.php" class="btn-wish-shop">Explore Collection</a>
                      </div>
                    `;
                  }
                } else {
                  const subtitle = document.querySelector('.wishlist-subtitle');
                  if (subtitle) {
                    const cnt = remaining.length;
                    subtitle.textContent = `You have ${cnt} premium ${cnt === 1 ? 'item' : 'items'} saved in your collection.`;
                  }
                }
              }, 400);
            } else {
              window.location.reload();
            }
          } else {
            toast(r.data?.message || 'Failed to remove', true);
          }
        } catch (err) {
          toast('Network error', true);
        } finally {
          setButtonLoading(btn, false);
        }
      });
    });

    cartAddBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const pid = btn.getAttribute('data-product-id');
        if (!pid) return;
        setButtonLoading(btn, true);
        try {
          const r = await sendCart(pid, 'add');
          if (r.data?.ok) {
            toast(r.data.message || 'Added to cart');
            updateNavbarCartBadge(r.data.cart_count);
            // Redirect to cart page after short delay
            setTimeout(() => {
              window.location.href = BASE_URL + 'cart.php';
            }, 500);
          } else {
            toast(r.data?.message || 'Failed to add', true);
          }
        } catch (err) {
          toast('Network error', true);
        } finally {
          setButtonLoading(btn, false);
        }
      });
    });

    // Wishlist Toggle event listener (event delegation)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-wishlist-toggle]');
      if (!btn) return;

      e.preventDefault();
      e.stopPropagation();

      const pid = btn.getAttribute('data-product-id');
      if (!pid) return;

      const icon = btn.querySelector('i');
      setButtonLoading(btn, true);
      console.log('Wishlist toggle request sent', { pid, action: 'toggle' });
      try {
        const res = await fetch(apiUrl('api/wishlist-toggle.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({ product_id: String(pid), action: 'toggle' })
        });
        const data = await res.json().catch(() => null);
        console.log('Wishlist toggle response received', data);
        if (data && data.ok) {
          const isAdded = data.action === 'added';
          toast(isAdded ? 'Added to wishlist' : 'Removed from wishlist');
          updateNavbarWishlistBadge(data.wishlist_count);

          // Update global tracking if it exists
          if (window.wishlistProductIds) {
            if (isAdded) {
              window.wishlistProductIds.add(String(pid));
            } else {
              window.wishlistProductIds.delete(String(pid));
            }
          }

          // Update heart icon classes
          if (icon) {
            if (isAdded) {
              icon.classList.remove('far');
              icon.classList.add('fas');
            } else {
              icon.classList.remove('fas');
              icon.classList.add('far');
            }
          }

          // If we are on the wishlist page itself, reload to reflect changes
          if (window.location.pathname.includes('wishlist.php')) {
            window.location.reload();
          }
        } else {
          toast(data?.error || 'Failed to update wishlist', true);
        }
      } catch (err) {
        toast('Network error', true);
      } finally {
        setButtonLoading(btn, false);
      }
    });
  };

  const init = () => {
    attachCouponHandler();
    attachCartHandlers();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

