@php
  $initialSelectedOptions = [];
  if (isset($initialVariant) && $initialVariant) {
      $initialSelectedOptions = $initialVariant->values
          ->mapWithKeys(function ($vv) {
              $n = trim((string)($vv->name ?? ''));
              $v = trim((string)($vv->value ?? ''));
              return ($n !== '' && $v !== '') ? [$n => $v] : [];
          })
          ->toArray();
  }
@endphp

@php
  $initialPayload = [
      'variant_id' => $initialVariant?->id,
      'price' => $initialVariant?->effective_price,
      'stock' => (int)($initialVariant?->stock ?? ($product->stock ?? 0)),
      'sku' => $initialVariant?->sku,
  ];
@endphp

<div class="vp"
     data-variant-picker
     data-product-id="{{ $product->id }}"
     data-variant-endpoint="{{ url('/api/products/' . $product->id . '/variant') }}"
     data-initial="{{ htmlspecialchars(json_encode($initialPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') }}">
  <input type="hidden" name="variant_id" class="vp-variant-id" value="{{ $initialVariant?->id }}">
  <input type="hidden" class="vp-selected-options" value="{{ json_encode($initialSelectedOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">

  @foreach(($variantOptions ?? []) as $name => $values)
    <div class="vp-group" data-option-name="{{ $name }}">
      <div class="vp-label">{{ $name }}</div>
      <div class="vp-buttons">
        @foreach(($values ?? []) as $value)
          <button type="button" class="vp-btn" data-option-value="{{ $value }}">{{ $value }}</button>
        @endforeach
      </div>
    </div>
  @endforeach

  <script type="application/json" class="vp-variants-json">{!! json_encode($variantsPayload ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</div>
