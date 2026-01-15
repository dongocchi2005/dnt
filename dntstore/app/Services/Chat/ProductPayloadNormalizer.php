<?php

namespace App\Services\Chat;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductPayloadNormalizer
{
    public static function normalizeProducts($products): array
    {
        if (!is_iterable($products)) {
            return [];
        }

        $out = [];
        foreach ($products as $p) {
            $normalized = self::normalizeProduct($p);
            if ($normalized) {
                $out[] = $normalized;
            }
        }

        return $out;
    }

    private static function normalizeProduct($p): ?array
    {
        if ($p instanceof Product) {
            $sale = self::positiveOrNull(self::toFloat($p->sale_price));
            $orig = self::positiveOrNull(self::toFloat($p->original_price));
            $price = $sale ?? $orig ?? 0.0;

            $url = url('/clearance/' . (($p->slug ?? null) ?: $p->id));
            $image = self::normalizeImage($p->image ? (string) $p->image : null);

            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => (float) $price,
                'sale_price' => $sale !== null ? (float) $sale : null,
                'original_price' => $orig !== null ? (float) $orig : null,
                'url' => (string) $url,
                'image' => $image,
                'in_stock' => ((int) ($p->stock ?? 0)) > 0,
            ];
        }

        if (!is_array($p)) {
            return null;
        }

        $id = (int) ($p['id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $name = (string) ($p['name'] ?? '');
        if ($name === '') {
            $name = 'Sản phẩm';
        }

        $sale = self::positiveOrNull(self::toFloat($p['sale_price'] ?? ($p['salePrice'] ?? null)));
        $orig = self::positiveOrNull(self::toFloat($p['original_price'] ?? ($p['originalPrice'] ?? null)));

        $fallbackPrice = self::positiveOrNull(self::toFloat($p['price'] ?? null));
        $price = $sale ?? $orig ?? ($fallbackPrice ?? 0.0);

        $url = (string) ($p['url'] ?? '');
        if ($url === '') {
            $slug = (string) ($p['slug'] ?? '');
            $url = url('/clearance/' . ($slug !== '' ? $slug : $id));
        }

        $image = self::normalizeImage($p['image'] ?? null);

        $inStock = $p['in_stock'] ?? ($p['inStock'] ?? null);
        if ($inStock === null && array_key_exists('stock', $p)) {
            $inStock = ((int) ($p['stock'] ?? 0)) > 0;
        }

        return [
            'id' => $id,
            'name' => $name,
            'price' => (float) $price,
            'sale_price' => $sale !== null ? (float) $sale : null,
            'original_price' => $orig !== null ? (float) $orig : null,
            'url' => $url,
            'image' => $image,
            'in_stock' => self::toBool($inStock),
        ];
    }

    private static function normalizeImage($image): ?string
    {
        $image = is_string($image) ? trim($image) : null;
        if (!$image) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://', '//'])) {
            return $image;
        }

        return asset($image);
    }

    private static function toFloat($v): ?float
    {
        if ($v === null) {
            return null;
        }

        if (is_string($v)) {
            $v = trim($v);
            if ($v === '') {
                return null;
            }
        }

        if (!is_numeric($v)) {
            return null;
        }

        return (float) $v;
    }

    private static function positiveOrNull(?float $v): ?float
    {
        if ($v === null) {
            return null;
        }

        return $v > 0 ? $v : null;
    }

    private static function toBool($v): bool
    {
        if (is_bool($v)) {
            return $v;
        }

        if (is_numeric($v)) {
            return ((float) $v) > 0;
        }

        if (is_string($v)) {
            $t = Str::lower(trim($v));
            if ($t === 'true' || $t === 'yes' || $t === '1') {
                return true;
            }
            if ($t === 'false' || $t === 'no' || $t === '0') {
                return false;
            }
        }

        return false;
    }
}

