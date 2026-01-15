import { initVariantPickers } from '../lib/variant-picker'

function clampInt(value, min, max) {
  const n = Number.parseInt(String(value ?? ''), 10)
  if (Number.isNaN(n)) return min
  return Math.min(max, Math.max(min, n))
}

function formatCountdown(ms) {
  const total = Math.max(0, Math.floor(ms / 1000))
  const h = Math.floor(total / 3600)
  const m = Math.floor((total % 3600) / 60)
  const s = total % 60
  const pad = (x) => String(x).padStart(2, '0')
  return `${pad(h)}:${pad(m)}:${pad(s)}`
}

async function postJson(url, payload) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken || '',
    },
    body: JSON.stringify(payload || {}),
  })
  const data = await res.json().catch(() => null)
  return { ok: res.ok, data }
}

document.addEventListener('DOMContentLoaded', () => {
  initVariantPickers()
  const wrap = document.querySelector('body[data-page="clearance.show"] .cl-wrap')
  if (!wrap) return

  const heroImg = document.getElementById('js-hero-img')
  const thumbs = document.getElementById('js-thumbs')
  const prevBtn = document.getElementById('js-prev')
  const nextBtn = document.getElementById('js-next')

  const qtyInput = document.getElementById('js-qty')
  const incBtn = document.getElementById('js-inc')
  const decBtn = document.getElementById('js-dec')

  const vpRoot = document.querySelector('[data-variant-picker]')

  const countdownWrap = document.getElementById('js-countdown')
  const countdownVal = document.getElementById('js-count-val')

  const cartIndexUrl = wrap.getAttribute('data-cart-index') || '/cart'
  const endIso = wrap.getAttribute('data-end')
  const endAt = endIso ? Date.parse(endIso) : NaN

  const state = {
    activeIndex: 0,
    images: [],
  }

  if (thumbs) {
    state.images = Array.from(thumbs.querySelectorAll('.thumb[data-src]'))
      .map((b) => b.getAttribute('data-src'))
      .filter(Boolean)
  }

  function syncCartButtonPayload() {
    const qty = clampInt(qtyInput?.value, 1, 999)
    const addButtons = document.querySelectorAll('body[data-page="clearance.show"] .btn-add-to-cart')
    addButtons.forEach((btn) => {
      btn.setAttribute('data-qty', String(qty))
      const variantId = vpRoot?.querySelector('.vp-variant-id')?.value
      if (variantId) btn.setAttribute('data-variant-id', String(variantId))
      else btn.removeAttribute('data-variant-id')
    })
  }

  function setActive(index) {
    const next = clampInt(index, 0, Math.max(0, state.images.length - 1))
    state.activeIndex = next
    const src = state.images[next]
    if (heroImg && src) heroImg.setAttribute('src', src)

    if (thumbs) {
      const all = thumbs.querySelectorAll('.thumb')
      all.forEach((el, idx) => {
        el.classList.toggle('active', idx === next)
        el.setAttribute('aria-current', idx === next ? 'true' : 'false')
      })
    }
  }

  thumbs?.addEventListener('click', (e) => {
    const btn = e.target?.closest?.('.thumb[data-src]')
    if (!btn) return
    const src = btn.getAttribute('data-src')
    const idx = state.images.indexOf(src)
    if (idx >= 0) setActive(idx)
  })

  prevBtn?.addEventListener('click', () => {
    if (!state.images.length) return
    setActive((state.activeIndex - 1 + state.images.length) % state.images.length)
  })

  nextBtn?.addEventListener('click', () => {
    if (!state.images.length) return
    setActive((state.activeIndex + 1) % state.images.length)
  })

  document.addEventListener('click', () => {
    syncCartButtonPayload()
  })

  function setQty(next) {
    if (!qtyInput) return
    const v = clampInt(next, 1, 999)
    qtyInput.value = String(v)
    syncCartButtonPayload()
  }

  incBtn?.addEventListener('click', () => setQty((clampInt(qtyInput?.value, 1, 999) || 1) + 1))
  decBtn?.addEventListener('click', () => setQty((clampInt(qtyInput?.value, 1, 999) || 1) - 1))
  qtyInput?.addEventListener('input', () => syncCartButtonPayload())
  syncCartButtonPayload()

  document.body.addEventListener('click', async (e) => {
    const buyBtn = e.target?.closest?.('body[data-page="clearance.show"] .btn-buy-now')
    if (!buyBtn) return
    e.preventDefault()

    const url = buyBtn.getAttribute('data-url')
    if (!url || buyBtn.hasAttribute('disabled')) return

    const qty = clampInt(qtyInput?.value, 1, 999)
    const variantId = vpRoot?.querySelector('.vp-variant-id')?.value
    if (!variantId) return
    const payload = {
      qty,
      variant_id: variantId,
    }

    const oldText = buyBtn.textContent
    buyBtn.setAttribute('disabled', 'disabled')
    buyBtn.textContent = '...'

    const result = await postJson(url, payload)
    buyBtn.textContent = oldText
    buyBtn.removeAttribute('disabled')

    if (result?.data?.success) {
      if (typeof window.updateCartBadge === 'function' && result.data.cart_count !== undefined) {
        window.updateCartBadge(result.data.cart_count)
      }
      window.location.assign(cartIndexUrl)
      return
    }

    const msg = result?.data?.message || 'Không thể thêm vào giỏ hàng'
    try {
      const toast = document.getElementById('cyber-toast')
      if (toast) {
        const span = toast.querySelector('span')
        if (span) span.textContent = msg
        toast.classList.add('show')
        setTimeout(() => toast.classList.remove('show'), 2200)
      } else {
        console.log(msg)
      }
    } catch (e) {
      console.log(msg)
    }
  })

  if (countdownWrap && countdownVal && Number.isFinite(endAt)) {
    const tick = () => {
      const now = Date.now()
      const remain = endAt - now
      if (remain <= 0) {
        countdownVal.textContent = '00:00:00'
        return
      }
      countdownVal.textContent = formatCountdown(remain)
    }
    countdownWrap.classList.remove('is-hidden')
    tick()
    const t = setInterval(() => {
      const remain = endAt - Date.now()
      if (remain <= 0) {
        tick()
        clearInterval(t)
        return
      }
      tick()
    }, 250)
  }

  setActive(0)
})
