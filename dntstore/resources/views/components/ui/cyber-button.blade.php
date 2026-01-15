@props([
  'as' => 'button',
  'href' => null,
  'variant' => 'primary',
  'size' => 'md',
  'loading' => false,
])

@php
  $base = 'relative inline-flex items-center justify-center gap-2 rounded-xl font-semibold text-ink shadow-glass transition will-change-transform focus:outline-none focus-visible:ring-2 focus-visible:ring-neon-cyan/50';

  $sizes = [
    'sm' => 'px-4 py-2 text-sm',
    'md' => 'px-5 py-3 text-sm',
    'lg' => 'px-6 py-3.5 text-base',
  ];

  $variants = [
    'primary' => 'bg-neon-cyan/15 border border-neon-cyan/35 shadow-neon-cyan hover:bg-neon-cyan/20 active:translate-y-px',
    'purple' => 'bg-neon-purple/15 border border-neon-purple/35 shadow-neon-purple hover:bg-neon-purple/20 active:translate-y-px',
    'gold' => 'bg-neon-gold/12 border border-neon-gold/35 hover:bg-neon-gold/18 active:translate-y-px',
    'ghost' => 'bg-white/5 border border-white/10 hover:bg-white/7 active:translate-y-px',
  ];

  $fx = 'overflow-hidden';
  $className = trim("$base $fx {$sizes[$size]} {$variants[$variant]}");
@endphp

@if($as === 'a')
  <a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => $className]) }}
    x-data="{ rippling: false }"
    @click="rippling = true; setTimeout(()=>rippling=false, 520)"
  >
    <span class="pointer-events-none absolute inset-0">
      <span
        class="absolute inset-0 opacity-0 transition"
        :class="rippling ? 'opacity-100' : 'opacity-0'"
        style="background: radial-gradient(circle at var(--rx,50%) var(--ry,50%), rgba(34,211,238,.25), transparent 55%);"
      ></span>
    </span>
    <span class="relative">{{ $slot }}</span>
  </a>
@else
  <button
    type="button"
    {{ $attributes->merge(['class' => $className]) }}
    :disabled="{{ $loading ? 'true' : 'false' }}"
    x-data="{ rippling: false }"
    @click="if (!{{ $loading ? 'true' : 'false' }}) { rippling = true; setTimeout(()=>rippling=false, 520) }"
  >
    <span class="pointer-events-none absolute inset-0">
      <span
        class="absolute inset-0 opacity-0 transition"
        :class="rippling ? 'opacity-100' : 'opacity-0'"
        style="background: radial-gradient(circle at var(--rx,50%) var(--ry,50%), rgba(34,211,238,.25), transparent 55%);"
      ></span>
    </span>

    @if($loading)
      <span class="relative h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
    @endif

    <span class="relative">{{ $slot }}</span>
  </button>
@endif

