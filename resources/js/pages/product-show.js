import { initVariantPickers } from '../lib/variant-picker'

function showToast(message) {
  try {
    const toast = document.getElementById('cyber-toast')
    if (!toast) return
    const span = toast.querySelector('span')
    if (span) span.textContent = message
    toast.classList.add('show')
    setTimeout(() => toast.classList.remove('show'), 2200)
  } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function () {
  initVariantPickers()

  const productId = document.querySelector('[data-product-id]')?.dataset.productId
  const addToCartBtn = document.getElementById('btn-add-to-cart')
  const qtyInput = document.getElementById('qty-input')
  const btnPlus = document.getElementById('btn-plus')
  const btnMinus = document.getElementById('btn-minus')

  const mainImage = document.getElementById('pd-main')
  const thumbnails = document.querySelectorAll('.pd-thumb')
  thumbnails.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      const newSrc = thumb.dataset.img
      if (mainImage && newSrc) mainImage.src = newSrc
      thumbnails.forEach((t) => t.classList.remove('is-active'))
      thumb.classList.add('is-active')
    })
  })

  const getStock = () => {
    const picker = document.querySelector('[data-variant-picker]')
    return Number(picker?.dataset.stock ?? 0)
  }

  const getVariantId = () => {
    return document.querySelector('[data-variant-picker] .vp-variant-id')?.value || null
  }

  const syncQtyBounds = () => {
    const stock = getStock()
    const current = Number(qtyInput?.value || 1)
    const next = Math.max(1, Math.min(stock > 0 ? stock : current, current))
    if (qtyInput) qtyInput.value = String(next)
  }

  btnMinus?.addEventListener('click', () => {
    const current = Number(qtyInput?.value || 1)
    const next = Math.max(1, current - 1)
    if (qtyInput) qtyInput.value = String(next)
  })
  btnPlus?.addEventListener('click', () => {
    const stock = getStock()
    const current = Number(qtyInput?.value || 1)
    const next = Math.min(stock > 0 ? stock : current + 1, current + 1)
    if (qtyInput) qtyInput.value = String(Math.max(1, next))
  })
  qtyInput?.addEventListener('change', syncQtyBounds)

  addToCartBtn?.addEventListener('click', async () => {
    if (addToCartBtn.disabled) return
    const variantId = getVariantId()
    const stock = getStock()
    const qty = Math.max(1, Number(qtyInput?.value || 1))
    if (!productId || !variantId) return
    if (stock > 0 && qty > stock) return

    const originalText = addToCartBtn.innerHTML
    addToCartBtn.disabled = true
    addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> XỬ LÝ...'

    try {
      const payload = {
        product_id: productId,
        qty,
        variant_id: variantId,
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      }

      const res = await fetch(`/cart/add/${productId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      })

      const data = await res.json().catch(() => null)
      if (data?.success) {
        showToast('Đã thêm vào giỏ hàng!')
        if (typeof window.updateCartBadge === 'function' && data.cart_count !== undefined) {
          window.updateCartBadge(data.cart_count)
        }
      }
    } finally {
      addToCartBtn.disabled = false
      addToCartBtn.innerHTML = originalText
    }
  })

  document.addEventListener('click', (e) => {
    if (!e.target?.closest?.('.vp-btn')) return
    syncQtyBounds()
  })
})
