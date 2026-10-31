# SHOP_UI_UPGRADE_PLAN.md

## Goal
Make homepage and UI feel materially more premium (micro-interactions + refined components), without touching AI/try-on/admin/payment.

## Phase A — Homepage UI
1. Announcement bar (gold/black) + CTA
2. Hero refinement:
   - reuse existing hero CSS
   - add a proper hero slider container semantics (if missing)
3. Replace/upgrade sections:
   - Collections: DB-backed where possible
   - Featured: DB-backed cards with better micro-interactions
4. Ensure fade-in triggers actually add `is-visible` on scroll.

## Phase B — Fix UI missing behaviors
- add IntersectionObserver in `assets/js/app.js` for `[data-fade-in]`
- ensure product card hover/glow is consistent

## Phase C — Shop (next)
- convert `pages/shop.php` to AJAX infinite scroll using `api/products-filter.php`
- add grid/list toggle persisted in localStorage
- map filter fields to real DB attributes

