<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductRecommenderService
{
    public function recommend(array $slots, int $limit = 4): array
    {
        try {
            $query = Product::query()
                ->where('is_active', true)
                ->where('stock', '>', 0);

            $type = $this->normalizeType($slots['product_type'] ?? null);
            $brand = trim((string)($slots['brand'] ?? ''));
            $keywords = trim((string)($slots['keywords'] ?? ''));
            $budget = trim((string)($slots['budget'] ?? ''));

            if ($type) {
                $query->where(function ($q) use ($type) {
                    $q->whereHas('category', function ($qc) use ($type) {
                        $qc->where('name', 'like', '%' . $type . '%');
                    })->orWhere('name', 'like', '%' . $type . '%')
                      ->orWhere('description', 'like', '%' . $type . '%');
                });
            }

            if ($brand) {
                $query->where(function ($q) use ($brand) {
                    $q->where('name', 'like', '%' . $brand . '%')
                      ->orWhere('description', 'like', '%' . $brand . '%');
                });
            }

            if ($keywords) {
                foreach ($this->splitKeywords($keywords) as $kw) {
                    $query->where(function ($q) use ($kw) {
                        $q->where('name', 'like', '%' . $kw . '%')
                          ->orWhere('description', 'like', '%' . $kw . '%');
                    });
                }
            }

            if ($budget) {
                [$min, $max] = $this->parseBudgetRange($budget);
                if ($min !== null) {
                    $query->where('sale_price', '>=', $min);
                }
                if ($max !== null) {
                    $query->where('sale_price', '<=', $max);
                }
            }

            $query->orderBy('sale_price', 'asc')->orderBy('id', 'desc');

            $products = $query->limit($limit)->get();

            return $products->map(function (Product $p) {
                $price = (float)($p->sale_price ?? $p->original_price ?? 0);
         $sale = $p->sale_price !== null ? (float)$p->sale_price : null;
if ($sale !== null && $sale <= 0) $sale = null;

                $inStock = ($p->stock ?? 0) > 0;
                $url = url('/clearance/' . ($p->slug ?? $p->id));
                $image = $p->image ? asset($p->image) : null;
                return [
                    'id' => (int)$p->id,
                    'name' => (string)$p->name,
                    'price' => $price,
                    'sale_price' => $sale,
                    'url' => $url,
                    'image' => $image,
                    'in_stock' => $inStock,
                ];
            })->all();
        } catch (\Throwable $e) {
            Log::error('[DNT Chat] Product recommend failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    protected function splitKeywords(string $keywords): array
    {
        $norm = Str::of($keywords)->lower()->replace([',', ';', '|'], ' ');
        return collect(explode(' ', (string)$norm))
            ->map(fn ($w) => trim($w))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeType(?string $type): ?string
    {
        if (!$type) return null;
        $type = Str::lower($type);
        return match ($type) {
            'tai-nghe' => 'tai nghe',
            'loa' => 'loa',
            'phu-kien' => 'phụ kiện',
            'linh-kien' => 'linh kiện',
            default => $type,
        };
    }

    protected function parseBudgetRange(string $budget): array
    {
        $text = Str::of($budget)->lower();

        if ($text->contains('dưới')) {
            $n = $this->extractMillionNumber($text);
            if ($n !== null) {
                return [0, $n];
            }
        }

        if ($text->contains('trên') || $text->contains('tối thiểu') || $text->contains('ít nhất')) {
            $n = $this->extractMillionNumber($text);
            if ($n !== null) {
                return [$n, null];
            }
        }

        $numbers = $this->extractNumbers($text);
        if (count($numbers) >= 2) {
            $a = min($numbers[0], $numbers[1]);
            $b = max($numbers[0], $numbers[1]);
            return [$a, $b];
        }
        if (count($numbers) === 1) {
            $n = $numbers[0];
            $delta = max(500000, (int) round($n * 0.2));
            return [$n - $delta, $n + $delta];
        }

        return [null, null];
    }

    protected function extractMillionNumber(\Illuminate\Support\Stringable $text): ?int
    {
        $numbers = $this->extractNumbers($text);
        return $numbers[0] ?? null;
    }

    protected function extractNumbers(\Illuminate\Support\Stringable $text): array
    {
        $s = (string)$text->replace(',', '.');
        preg_match_all('/([0-9]+(?:\\.[0-9]+)?)\\s*(triệu|tr|m|vnđ|vnd|đ)?/u', $s, $m, PREG_SET_ORDER);
        $out = [];
        foreach ($m as $match) {
            $num = (float) $match[1];
            $unit = isset($match[2]) ? Str::lower($match[2]) : '';
            if ($unit === 'triệu' || $unit === 'tr' || $unit === 'm') {
                $out[] = (int) round($num * 1000000);
            } else {
                if ($num < 1000) {
                    $out[] = (int) round($num * 1000000);
                } else {
                    $out[] = (int) round($num);
                }
            }
        }
        return $out;
    }
}

