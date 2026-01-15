@props([
  'label' => null,
  'error' => null,
])

<label class="block">
  @if($label)
    <span class="mb-2 block text-sm text-ink-muted">{{ $label }}</span>
  @endif

  <div class="group relative">
    <input
      {{ $attributes->merge(['class' => 'w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-ink placeholder:text-ink-muted/70 outline-none transition focus:border-neon-cyan/45 focus:shadow-neon-cyan']) }}
    >
    <div class="pointer-events-none absolute inset-0 rounded-xl opacity-0 transition group-focus-within:opacity-100" style="box-shadow: var(--glow-cyan);"></div>
  </div>

  @if($error)
    <p class="mt-2 text-sm text-neon-purple">{{ $error }}</p>
  @endif
</label>

