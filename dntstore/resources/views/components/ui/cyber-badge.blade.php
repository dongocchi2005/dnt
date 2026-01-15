@props([
  'tone' => 'cyan',
])

@php
  $tones = [
    'cyan' => 'bg-neon-cyan/12 text-neon-cyan border-neon-cyan/30',
    'purple' => 'bg-neon-purple/12 text-neon-purple border-neon-purple/30',
    'gold' => 'bg-neon-gold/12 text-neon-gold border-neon-gold/30',
    'blue' => 'bg-neon-blue/12 text-neon-blue border-neon-blue/30',
    'gray' => 'bg-white/5 text-ink-muted border-white/10',
  ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold tracking-wide {$tones[$tone]}"]) }}>
  <span class="mr-2 h-1.5 w-1.5 rounded-full bg-current shadow-[0_0_12px_currentColor]"></span>
  {{ $slot }}
</span>

