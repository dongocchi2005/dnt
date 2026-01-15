<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantResolver
{
    public function optionGroups(Product $product): array
    {
        $variants = $product->variants()
            ->where('is_active', true)
            ->with(['values'])
            ->get();

        $groups = [];
        foreach ($variants as $variant) {
            foreach ($variant->values as $vv) {
                $name = trim((string)($vv->name ?? ''));
                $value = trim((string)($vv->value ?? ''));
                if ($name === '' || $value === '') {
                    continue;
                }
                $groups[$name] ??= [];
                $groups[$name][$value] = true;
            }
        }

        return collect($groups)
            ->map(fn($set) => array_values(array_keys($set)))
            ->toArray();
    }

    public function resolveVariant(Product $product, array $options): ?ProductVariant
    {
        $normalized = [];
        foreach ($options as $k => $v) {
            $name = trim((string)$k);
            $value = trim((string)$v);
            if ($name === '' || $value === '') {
                continue;
            }
            $normalized[$name] = $value;
        }

        if (empty($normalized)) {
            return null;
        }

        $variants = $product->variants()
            ->where('is_active', true)
            ->with(['values'])
            ->get();

        $targetCount = count($normalized);

        foreach ($variants as $variant) {
            $map = [];
            foreach ($variant->values as $vv) {
                $n = trim((string)($vv->name ?? ''));
                $val = trim((string)($vv->value ?? ''));
                if ($n === '' || $val === '') {
                    continue;
                }
                $map[$n] = $val;
            }

            if (count($map) !== $targetCount) {
                continue;
            }

            $ok = true;
            foreach ($normalized as $n => $val) {
                if (!isset($map[$n]) || $map[$n] !== $val) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return $variant;
            }
        }

        return null;
    }

    public function variantPayload(ProductVariant $variant): array
    {
        return [
            'variant_id' => $variant->id,
            'price' => $variant->effective_price,
            'stock' => (int)($variant->stock ?? 0),
            'sku' => $variant->sku,
        ];
    }

    public function variantLabel(ProductVariant $variant): string
    {
        $pairs = $variant->values()
            ->get()
            ->map(function ($vv) {
                $n = trim((string)($vv->name ?? ''));
                $v = trim((string)($vv->value ?? ''));
                return ($n !== '' && $v !== '') ? ($n . ': ' . $v) : null;
            })
            ->filter()
            ->values()
            ->all();

        return implode(', ', $pairs);
    }
}

