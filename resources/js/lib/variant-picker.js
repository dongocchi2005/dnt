function formatVnd(amount) {
  const n = Number(amount || 0)
  try {
    return new Intl.NumberFormat('vi-VN').format(n) + ' ₫'
  } catch (e) {
    return String(n) + ' ₫'
  }
}

function buildVariantMap(variant) {
  const map = {}
  const vals = Array.isArray(variant?.values) ? variant.values : []
  vals.forEach((p) => {
    const name = String(p?.name || '').trim()
    const value = String(p?.value || '').trim()
    if (!name || !value) return
    map[name] = value
  })
  return map
}

function matchesSelection(map, selection) {
  const keys = Object.keys(selection || {})
  for (let i = 0; i < keys.length; i++) {
    const k = keys[i]
    if (!map[k] || map[k] !== selection[k]) return false
  }
  return true
}

function setText(root, selector, text) {
  root.querySelectorAll(selector).forEach((el) => {
    el.textContent = text
  })
}

function setDisabled(root, selector, disabled) {
  root.querySelectorAll(selector).forEach((el) => {
    if (disabled) el.setAttribute('disabled', 'disabled')
    else el.removeAttribute('disabled')
  })
}

function updatePriceStockSku(picker, payload) {
  const root = picker.closest('[data-product-id]') || document
  const price = payload?.price
  const stock = Number(payload?.stock ?? 0)
  const sku = payload?.sku

  if (price !== null && price !== undefined) setText(root, '[data-vp-price]', formatVnd(price))
  if (sku !== null && sku !== undefined) setText(root, '[data-vp-sku]', sku || 'N/A')

  const stockText = stock > 0 ? `Còn ${stock}` : 'Hết hàng'
  root.querySelectorAll('[data-vp-stock]').forEach((el) => {
    const mode = el.getAttribute('data-vp-stock-mode')
    el.textContent = mode === 'qty' ? String(stock) : stockText
    el.classList.toggle('text-green-400', stock > 0)
    el.classList.toggle('text-red-500', stock <= 0)
  })

  setDisabled(root, '[data-vp-cta]', stock <= 0 || !payload?.variant_id)
}

function buildQuery(options) {
  const parts = []
  Object.keys(options || {}).forEach((name) => {
    const value = options[name]
    parts.push(`options[${encodeURIComponent(name)}]=${encodeURIComponent(String(value))}`)
  })
  return parts.join('&')
}

async function fetchVariant(picker, selectedOptions) {
  const endpoint = picker.getAttribute('data-variant-endpoint')
  if (!endpoint) return null

  const q = buildQuery(selectedOptions)
  const url = q ? `${endpoint}?${q}` : endpoint
  const res = await fetch(url, { headers: { Accept: 'application/json' } })
  if (!res.ok) return null
  return await res.json().catch(() => null)
}

function initVariantPicker(picker) {
  const variantsJsonEl = picker.querySelector('.vp-variants-json')
  const variants = JSON.parse(variantsJsonEl?.textContent || '[]')
  const groupEls = Array.from(picker.querySelectorAll('.vp-group[data-option-name]'))
  const groupNames = groupEls.map((g) => g.getAttribute('data-option-name')).filter(Boolean)

  let selectedOptions = {}
  try {
    const initStr = picker.querySelector('.vp-selected-options')?.value || '{}'
    selectedOptions = JSON.parse(initStr) || {}
  } catch (e) {
    selectedOptions = {}
  }

  const variantMaps = variants.map((v) => ({ raw: v, map: buildVariantMap(v) }))

  const getCandidates = (selection) => {
    return variantMaps.filter((vm) => matchesSelection(vm.map, selection))
  }

  const refreshAvailability = () => {
    groupEls.forEach((groupEl) => {
      const name = groupEl.getAttribute('data-option-name')
      if (!name) return
      const buttons = Array.from(groupEl.querySelectorAll('.vp-btn[data-option-value]'))
      buttons.forEach((btn) => {
        const val = btn.getAttribute('data-option-value')
        const testSel = { ...selectedOptions, [name]: val }
        const candidates = getCandidates(testSel)
        const exists = candidates.length > 0
        btn.toggleAttribute('disabled', !exists)
      })
    })
  }

  const refreshActive = () => {
    groupEls.forEach((groupEl) => {
      const name = groupEl.getAttribute('data-option-name')
      if (!name) return
      const buttons = Array.from(groupEl.querySelectorAll('.vp-btn[data-option-value]'))
      buttons.forEach((btn) => {
        btn.classList.toggle('active', btn.getAttribute('data-option-value') === selectedOptions[name])
      })
    })
  }

  const tryResolve = async () => {
    const complete = groupNames.length > 0 && groupNames.every((n) => !!selectedOptions[n])
    if (!complete) {
      picker.dataset.variantId = ''
      picker.querySelector('.vp-variant-id').value = ''
      updatePriceStockSku(picker, { variant_id: null, stock: 0 })
      return
    }

    const data = await fetchVariant(picker, selectedOptions)
    if (!data || !data.variant_id) {
      picker.dataset.variantId = ''
      picker.querySelector('.vp-variant-id').value = ''
      updatePriceStockSku(picker, { variant_id: null, stock: 0 })
      return
    }

    picker.dataset.variantId = String(data.variant_id || '')
    picker.dataset.stock = String(data.stock ?? 0)
    picker.querySelector('.vp-variant-id').value = String(data.variant_id || '')
    updatePriceStockSku(picker, data)
  }

  groupEls.forEach((groupEl) => {
    groupEl.addEventListener('click', async (e) => {
      const btn = e.target?.closest?.('.vp-btn[data-option-value]')
      if (!btn || btn.hasAttribute('disabled')) return
      const name = groupEl.getAttribute('data-option-name')
      const val = btn.getAttribute('data-option-value')
      selectedOptions = { ...selectedOptions, [name]: val }
      picker.querySelector('.vp-selected-options').value = JSON.stringify(selectedOptions)
      refreshActive()
      refreshAvailability()
      await tryResolve()
    })
  })

  refreshActive()
  refreshAvailability()
  if (groupNames.length > 0) {
    tryResolve()
    return
  }

  let initial = null
  try {
    initial = JSON.parse(picker.getAttribute('data-initial') || 'null')
  } catch (e) {
    initial = null
  }
  if (initial?.variant_id) {
    picker.dataset.variantId = String(initial.variant_id)
    picker.dataset.stock = String(initial.stock ?? 0)
    picker.querySelector('.vp-variant-id').value = String(initial.variant_id)
    updatePriceStockSku(picker, initial)
  }
}

export function initVariantPickers() {
  document.querySelectorAll('[data-variant-picker]').forEach((picker) => {
    initVariantPicker(picker)
  })
}
