const isBlogPage = () => {
  const page = document.body?.dataset?.page || ''
  return page.startsWith('blog.')
}

const prefersReducedMotion = () =>
  window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const isFinePointer = () =>
  (window.matchMedia?.('(hover: hover)')?.matches ?? false) &&
  (window.matchMedia?.('(pointer: fine)')?.matches ?? false)

const setupReveal = () => {
  const nodes = Array.from(document.querySelectorAll('.page-blog [data-reveal]'))
  if (nodes.length === 0) return

  if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
    nodes.forEach((el) => el.classList.add('is-revealed'))
    return
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        entry.target.classList.add('is-revealed')
        io.unobserve(entry.target)
      })
    },
    { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
  )

  nodes.forEach((el) => io.observe(el))
}

const setupFilterBarCompact = () => {
  const bar = document.querySelector('#blogFilterBar[data-filter-bar]')
  if (!bar) return

  let ticking = false
  const update = () => {
    ticking = false
    const y = window.scrollY || 0
    bar.dataset.compact = y > 120 ? '1' : '0'
  }

  update()
  window.addEventListener(
    'scroll',
    () => {
      if (ticking) return
      ticking = true
      window.requestAnimationFrame(update)
    },
    { passive: true },
  )
}

const setupTilt = () => {
  if (prefersReducedMotion() || !isFinePointer()) return
  const items = Array.from(document.querySelectorAll('.page-blog [data-tilt]'))
  if (items.length === 0) return

  items.forEach((el) => {
    let rect = null

    const onMove = (e) => {
      if (!rect) rect = el.getBoundingClientRect()
      const px = (e.clientX - rect.left) / rect.width
      const py = (e.clientY - rect.top) / rect.height

      const rotY = (px - 0.5) * 10
      const rotX = (0.5 - py) * 8

      el.style.setProperty('--tiltX', `${rotX.toFixed(2)}deg`)
      el.style.setProperty('--tiltY', `${rotY.toFixed(2)}deg`)
      el.style.setProperty('--tiltT', `-1px`)
    }

    const onEnter = () => {
      rect = el.getBoundingClientRect()
    }

    const onLeave = () => {
      rect = null
      el.style.setProperty('--tiltX', `0deg`)
      el.style.setProperty('--tiltY', `0deg`)
      el.style.setProperty('--tiltT', `0px`)
    }

    el.addEventListener('mouseenter', onEnter)
    el.addEventListener('mousemove', onMove)
    el.addEventListener('mouseleave', onLeave)
  })
}

const setupOpenChat = () => {
  const triggers = Array.from(document.querySelectorAll('.page-blog [data-open-chat]'))
  if (triggers.length === 0) return

  const toggle = document.getElementById('dntChatToggle')
  const panel = document.getElementById('dntChatPanel')

  if (!toggle) return

  const open = () => {
    const isOpen = panel ? !panel.classList.contains('hidden') : toggle.getAttribute('aria-expanded') === 'true'
    if (!isOpen) toggle.click()

    const input = document.getElementById('dntChatInput')
    input?.focus?.()
  }

  triggers.forEach((btn) => btn.addEventListener('click', open))
}

document.addEventListener('DOMContentLoaded', () => {
  if (!isBlogPage()) return

  setupReveal()
  setupFilterBarCompact()
  setupTilt()
  setupOpenChat()
})

