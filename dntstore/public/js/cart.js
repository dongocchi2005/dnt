/**
 * DNT Store Cart Manager
 * Handles Add/Remove actions and Badge updates.
 */

(function() {
    'use strict';

    window.__CART_COUNT__ = 0;

    /**
     * Update the cart badge in the header.
     * @param {number} count - Total items in cart
     */
    window.updateCartBadge = function(count) {
        const safeCount = parseInt(count, 10) || 0;
        window.__CART_COUNT__ = safeCount;

        const badge = document.getElementById('cart-count-badge');
        if (!badge) return;

        badge.textContent = safeCount;
        badge.style.display = safeCount > 0 ? '' : 'none';
    };

    /**
     * Generic Fetch Helper for Cart Actions
     */
    async function fetchCartAction(url, method = 'POST', body = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            };

            if (method !== 'GET' && method !== 'HEAD') {
                options.body = JSON.stringify(body);
            }

            const response = await fetch(url, options);
            const data = await response.json();

            if (data.success) {
                if (data.cart_count !== undefined) {
                    window.updateCartBadge(data.cart_count);
                }
                return data;
            } else {
                console.error('Cart Action Failed:', data.message);
                // Can dispatch event or show toast here
            }
            return data;
        } catch (error) {
            console.error('Cart Network Error:', error);
        }
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        // Init global state from DOM
        const badge = document.getElementById('cart-count-badge');
        if (badge) {
             const initialCount = parseInt(badge.textContent.trim(), 10) || 0;
             window.__CART_COUNT__ = initialCount;
        }

        document.body.addEventListener('click', async (e) => {
            // A. Remove from Cart
            const removeBtn = e.target.closest('.btn-remove-cart');
            if (removeBtn) {
                e.preventDefault();
                const url = removeBtn.getAttribute('data-url');
                
                if (url) {
                    const result = await fetchCartAction(url, 'POST');
                    
                    if (result && result.success) {
                         // Remove row from DOM
                         const row = removeBtn.closest('tr') || removeBtn.closest('.cart-item-card');
                         if (row) row.remove();
                         
                         if (result.cart_count === 0 && document.querySelector('.cart-wrap')) {
                             location.reload(); 
                         } else {
                             // Update subtotal if returned
                             if (result.subtotal !== undefined) {
                                 const subEl = document.getElementById('js-selected-subtotal');
                                 if (subEl) subEl.textContent = new Intl.NumberFormat('vi-VN').format(result.subtotal) + ' ₫';
                             }
                         }
                    }
                }
            }

            // B. Add to Cart
            const addBtn = e.target.closest('.btn-add-to-cart');
            if (addBtn) {
                e.preventDefault();
                const url = addBtn.getAttribute('data-url') || addBtn.dataset.url;
                
                if (url) {
                    // Gather payload
                    const qty = parseInt(addBtn.getAttribute('data-qty') || addBtn.dataset.qty || 1, 10);
                    const variantId = addBtn.getAttribute('data-variant-id') || addBtn.dataset.variantId || null;
                    const variantKey = addBtn.getAttribute('data-variant-key') || addBtn.dataset.variantKey || null;
                    const color = addBtn.getAttribute('data-color') || addBtn.dataset.color || null;
                    const size = addBtn.getAttribute('data-size') || addBtn.dataset.size || null;
                    
                    let options = {};
                    try {
                        const optRaw = addBtn.getAttribute('data-options') || addBtn.dataset.options;
                        if (optRaw) options = JSON.parse(optRaw);
                    } catch (e) {
                        console.error('Error parsing cart options', e);
                    }

                    const payload = {
                        qty: qty,
                        variant_id: variantId,
                        variant_key: variantKey,
                        color: color,
                        size: size,
                        options: options
                    };

                    addBtn.classList.add('disabled');
                    const oldText = addBtn.textContent;
                    addBtn.textContent = '...';

                    const result = await fetchCartAction(url, 'POST', payload);
                    
                    addBtn.classList.remove('disabled');
                    addBtn.textContent = oldText;

                    if (result && result.success) {
                         // Optional: Show toast success
                         const msgEl = document.getElementById('addMsg');
                         if (msgEl) {
                             msgEl.textContent = result.message || 'Đã thêm vào giỏ hàng';
                             msgEl.style.color = 'rgba(69,255,154,.92)';
                         } else {
                            try {
                                const toast = document.getElementById('cyber-toast');
                                if (toast) {
                                    toast.querySelector('span').textContent = result.message || 'Đã thêm vào giỏ hàng';
                                    toast.classList.add('show');
                                    setTimeout(() => toast.classList.remove('show'), 2200);
                                } else {
                                    console.log(result.message || 'Đã thêm vào giỏ hàng');
                                }
                            } catch (e) {
                                console.log(result.message || 'Đã thêm vào giỏ hàng');
                            }
                         }
                    }
                }
            }
        });
        
        // C. Update Quantity (Delegated for .js-qty-btn)
        // If the cart page uses the specific structure with form, we might intercept it
        // But the previous implementation used a form submit or custom JS. 
        // Let's hook into the existing class structure if we want to standardize.
        // Currently cart/index.blade.php uses inline script. We can leave it or unify.
        // For now, I'll respect the existing cart/index logic which is quite specific.
        
        window.addEventListener('cart:updated', (e) => {
             if (e.detail && e.detail.count !== undefined) {
                 window.updateCartBadge(e.detail.count);
             }
        });
    });

})();
