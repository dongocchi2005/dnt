<?php

namespace App\Services\Chat;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductComparisonService
{
    public function compare(array $keywords, int $limit = 3): array
    {
        $query = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0);

        $filtered = collect($keywords)
            ->map(fn($k) => trim((string)$k))
            ->filter(fn($k) => $k !== '')
            ->map(fn($k) => Str::lower($k))
            ->unique()
            ->values()
            ->all();

        if ($filtered) {
            $query->where(function ($q) use ($filtered) {
                foreach ($filtered as $kw) {
                    $q->orWhere('name', 'like', '%' . $kw . '%')
                        ->orWhere('description', 'like', '%' . $kw . '%');
                }
            });
        }

        return $query->limit($limit)->get()->map(function (Product $p) {
            return [
                'id' => (int)$p->id,
                'name' => (string)$p->name,
                'price' => (float)($p->sale_price ?? $p->original_price ?? 0),
                'stock' => (int)($p->stock ?? 0),
                'url' => url('/clearance/' . ($p->slug ?? $p->id)),
                'image' => $p->image ? asset($p->image) : null,
            ];
        })->all();
    }
}
