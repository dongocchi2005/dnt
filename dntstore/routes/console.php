<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;
use App\Models\ChatSession;
use App\Models\Product;
use App\Models\ProductVariant;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:sweep-sessions', function () {
    $now = now();
    $sessions = ChatSession::query()
        ->whereNotIn('status', ['closed', 'expired'])
        ->whereNotNull('pending_close_at')
        ->where('pending_close_at', '<=', $now)
        ->orderBy('pending_close_at')
        ->limit(500)
        ->get();

    $closed = 0;
    foreach ($sessions as $session) {
        $waiting = (bool) ($session->waiting_customer_reply ?? false);
        if (!$waiting) {
            continue;
        }

        $lastStaff = $session->last_staff_message_at;
        $lastCustomer = $session->last_customer_message_at;

        $safeToClose = false;
        if ($lastStaff && $lastCustomer) {
            $safeToClose = $lastCustomer->lt($lastStaff);
        } elseif ($lastStaff && !$lastCustomer) {
            $safeToClose = true;
        }

        if (!$safeToClose) {
            continue;
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => $now,
            'waiting_customer_reply' => false,
            'pending_close_at' => null,
        ]);
        $closed++;
    }

    $this->info("Closed {$closed} session(s).");
})->purpose('Auto close chat sessions based on pending_close_at');

Schedule::command('chat:sweep-sessions')->everyMinute();

// Gom sản phẩm trùng slug thành một sản phẩm và tạo biến thể
Artisan::command('products:import-variants', function () {
    $this->info('Bắt đầu gom sản phẩm trùng slug và tạo biến thể...');

    // Lấy danh sách slug trùng
    $dups = Product::select('slug', DB::raw('COUNT(*) as cnt'))
        ->groupBy('slug')
        ->having('cnt', '>', 1)
        ->pluck('slug');

    $countGroups = 0;
    $countVariants = 0;

    foreach ($dups as $slug) {
        // Lấy tất cả bản ghi theo slug
        $items = Product::where('slug', $slug)->orderBy('id')->get();
        if ($items->count() < 2) continue;
        $countGroups++;

        // Chọn sản phẩm gốc
        $base = $items->first();
        $others = $items->slice(1);

        // Cập nhật giá từ ... bằng min giá bán trong nhóm
        $minSale = $items->min('sale_price');
        $minOrigin = $items->min('original_price');
        $base->sale_price = $minSale ?? $base->sale_price;
        $base->original_price = $minOrigin ?? $base->original_price;
        $base->save();

        // Tạo biến thể từ các bản ghi còn lại
        foreach ($others as $row) {
            // Rút trích color/size đơn giản từ mô tả hoặc tên
            $desc = ($row->description ?? '') . ' ' . ($row->name ?? '');
            $color = null;
            $size = null;

            $colors = [
                'Đen' => ['đen','black'],
                'Trắng' => ['trắng','white'],
                'Đỏ' => ['đỏ','red'],
                'Xanh' => ['xanh','blue','green'],
                'Vàng' => ['vàng','yellow'],
                'Hồng' => ['hồng','pink'],
                'Tím' => ['tím','purple'],
            ];
            $lower = Str::lower($desc);
            foreach ($colors as $label => $keys) {
                foreach ($keys as $k) {
                    if (Str::contains($lower, $k)) { $color = $label; break 2; }
                }
            }
            if (preg_match('/(\d{2,4}\s?ml)/i', $desc, $m)) { $size = trim($m[1]); }
            elseif (preg_match('/\b(XXL|XL|L|M|S|XS)\b/i', $desc, $m)) { $size = strtoupper($m[1]); }

            $variantName = trim(($color ?? '') . ' ' . ($size ?? ''));
            if ($variantName === '') {
                if ($row->sale_price !== $base->sale_price) {
                    $variantName = 'Giá ' . number_format((float)$row->sale_price, 0, ',', '.') . 'đ';
                } else {
                    $variantName = 'Mặc định';
                }
            }

            ProductVariant::create([
                'product_id'     => $base->id,
                'variant_name'   => $variantName,
                'color'          => $color,
                'size'           => $size,
                'sku'            => Str::limit($base->slug, 40, '') . '-' . $row->id,
                'original_price' => $row->original_price,
                'sale_price'     => $row->sale_price,
            ]);
            $countVariants++;

            // Xóa sản phẩm trùng đã chuyển thành biến thể để tránh trùng lặp listing
            $row->delete();
        }
    }

    // Nếu không có nhóm trùng trong DB (do INSERT IGNORE), fallback: parse file SQL để tạo biến thể từ dữ liệu bị bỏ qua
    if ($countGroups === 0) {
        $path = base_path('database/products_insert_from_excel.sql');
        if (file_exists($path)) {
            $sql = file_get_contents($path);
            if (is_string($sql) && $sql !== '') {
                $rows = [];
                // Bóc từng record: name, slug, description, category, original_price, sale_price, image(NULL hoặc path), stock, is_active, is_clearance, created_at, updated_at
                $pattern = "/\\(\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*,\\s*'([^']*)'\\s*,\\s*([0-9]+\\.[0-9]+)\\s*,\\s*([0-9]+\\.[0-9]+)\\s*,\\s*(NULL|'[^']*')\\s*,\\s*([0-9]+)\\s*,\\s*([01])\\s*,\\s*([01])\\s*,/mi";
                if (preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $rows[] = [
                            'name' => $m[1],
                            'slug' => $m[2],
                            'description' => $m[3],
                            'category_text' => $m[4],
                            'original_price' => (float)$m[5],
                            'sale_price' => (float)$m[6],
                            'image' => $m[7] === 'NULL' ? null : trim($m[7], "'"),
                            'stock' => (int)$m[8],
                            'is_active' => (int)$m[9] === 1,
                            'is_clearance' => (int)$m[10] === 1,
                        ];
                    }
                }
                // Nhóm theo slug
                $groups = collect($rows)->groupBy('slug');
                foreach ($groups as $slug => $items) {
                    $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
                    if ($items->count() < 2) continue;
                    $base = Product::where('slug', $slug)->first();
                    if (!$base) {
                        // Tạo sản phẩm gốc nếu chưa có
                        $first = $items->first();
                        $base = Product::create([
                            'name' => $first['name'],
                            'slug' => $slug,
                            'description' => $first['description'],
                            'original_price' => $first['original_price'],
                            'sale_price' => $first['sale_price'],
                            'image' => $first['image'],
                            'stock' => $first['stock'],
                            'is_active' => $first['is_active'],
                            'is_clearance' => $first['is_clearance'],
                            'category' => $first['category_text'],
                        ]);
                    }
                    $countGroups++;
                    // Cập nhật giá từ …
                    $minSale = $items->min('sale_price');
                    $minOrigin = $items->min('original_price');
                    $base->sale_price = $minSale ?? $base->sale_price;
                    $base->original_price = $minOrigin ?? $base->original_price;
                    $base->save();
                    // Tạo biến thể từ các item còn lại
                    foreach ($items->slice(1)->values() as $row) {
                        $desc = ($row['description'] ?? '') . ' ' . ($row['name'] ?? '');
                        $color = null; $size = null;
                        $colors = [
                            'Đen' => ['đen','black'],
                            'Trắng' => ['trắng','white'],
                            'Đỏ' => ['đỏ','red'],
                            'Xanh' => ['xanh','blue','green'],
                            'Vàng' => ['vàng','yellow'],
                            'Hồng' => ['hồng','pink'],
                            'Tím' => ['tím','purple'],
                        ];
                        $lower = Str::lower($desc);
                        foreach ($colors as $label => $keys) {
                            foreach ($keys as $k) {
                                if (Str::contains($lower, $k)) { $color = $label; break 2; }
                            }
                        }
                        if (preg_match('/(\\d{2,4}\\s?ml)/i', $desc, $mm)) { $size = trim($mm[1]); }
                        elseif (preg_match('/\\b(XXL|XL|L|M|S|XS)\\b/i', $desc, $mm)) { $size = strtoupper($mm[1]); }
                        $variantName = trim(($color ?? '') . ' ' . ($size ?? ''));
                        if ($variantName === '') {
                            if ($row['sale_price'] !== $base->sale_price) {
                                $variantName = 'Giá ' . number_format((float)$row['sale_price'], 0, ',', '.') . 'đ';
                            } else {
                                $variantName = 'Mặc định';
                            }
                        }
                        $sku = Str::limit($base->slug, 40, '') . '-v-' .
                            substr(md5(($row['name'] ?? '') . ($row['description'] ?? '') .
                                ($row['original_price'] ?? '') . ($row['sale_price'] ?? '')), 0, 8);
                        if (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
                            $sku .= '-' . substr((string) Str::uuid(), 0, 8);
                        }
                        ProductVariant::create([
                            'product_id' => $base->id,
                            'variant_name' => $variantName,
                            'color' => $color,
                            'size' => $size,
                            'sku' => $sku,
                            'original_price' => $row['original_price'],
                            'sale_price' => $row['sale_price'],
                        ]);
                        $countVariants++;
                    }
                }
            }
        }
    }

    $this->info("Đã gom {$countGroups} nhóm slug và tạo {$countVariants} biến thể.");
})->purpose('Gom sản phẩm trùng slug thành product_variants');
