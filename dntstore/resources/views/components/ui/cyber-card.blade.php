@props([
  'tone' => 'cyan',
  'tilt' => true,
  'scanlines' => true,
  'noise' => true,
])

@php
  $toneGlow = [
    'cyan' => 'cy-neon-cyan',
    'purple' => 'cy-neon-purple',
    'gold' => '',
    'none' => '',
  ];

  $base = 'relative rounded-2xl border border-white/10 bg-white/5 shadow-glass backdrop-blur-xl overflow-hidden';
  $fx = ($scanlines ? ' cy-scanlines' : '') . ($noise ? ' cy-noise' : '');
  $className = trim("$base $fx {$toneGlow[$tone]}");
@endphp

<div
  {{ $attributes->merge(['class' => $className]) }}
  @if($tilt)
  data-cy-tilt="1"
  @endif
>
  <div class="relative p-6">
    {{ $slot }}
  </div>
</div>

