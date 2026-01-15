<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'variants_json')) {
            return;
        }
        if (!Schema::hasTable('product_variants') || !Schema::hasTable('product_variant_values')) {
            return;
        }

        $products = DB::table('products')
            ->select(['id', 'slug', 'variants_json'])
            ->whereNotNull('variants_json')
            ->get();

        foreach ($products as $p) {
            $items = json_decode((string)$p->variants_json, true);
            if (!is_array($items) || empty($items)) {
                continue;
            }

            foreach ($items as $it) {
                $pairs = is_array($it['pairs'] ?? null) ? $it['pairs'] : [];
                $sku = trim((string)($it['sku'] ?? ''));
                if ($sku === '') {
                    $suffix = implode('-', array_map(fn($pair) => Str::slug((string)($pair['value'] ?? '')), $pairs));
                    $sku = Str::slug((string)$p->slug) . '-' . $suffix;
                }
                $price = $it['price'] ?? null;
                $variantName = collect($pairs)->map(function ($pair) {
                    $n = trim((string)($pair['name'] ?? ''));
                    $v = trim((string)($pair['value'] ?? ''));
                    return ($n !== '' && $v !== '') ? ($n . ':' . $v) : null;
                })->filter()->implode(' / ');

                $existing = DB::table('product_variants')
                    ->select(['id'])
                    ->where('product_id', $p->id)
                    ->where('sku', $sku)
                    ->first();

                if ($existing) {
                    $variantId = (int)$existing->id;
                    DB::table('product_variants')
                        ->where('id', $variantId)
                        ->update([
                            'variant_name' => $variantName !== '' ? $variantName : 'Biến thể',
                            'price' => $price,
                            'original_price' => $price,
                            'stock' => DB::raw('GREATEST(stock, 0)'),
                            'is_active' => 1,
                            'updated_at' => now(),
                        ]);
                } else {
                    $variantId = DB::table('product_variants')->insertGetId([
                        'product_id' => $p->id,
                        'variant_name' => $variantName !== '' ? $variantName : 'Biến thể',
                        'sku' => $sku,
                        'price' => $price,
                        'original_price' => $price,
                        'sale_price' => null,
                        'stock' => (int)(DB::table('products')->where('id', $p->id)->value('stock') ?? 0),
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('product_variant_values')
                    ->where('variant_id', $variantId)
                    ->orWhere('product_variant_id', $variantId)
                    ->delete();

                foreach ($pairs as $pair) {
                    $name = trim((string)($pair['name'] ?? ''));
                    $value = trim((string)($pair['value'] ?? ''));
                    if ($name === '' || $value === '') {
                        continue;
                    }
                    DB::table('product_variant_values')->insert([
                        'variant_id' => $variantId,
                        'product_variant_id' => $variantId,
                        'name' => $name,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // keep data
    }
};
