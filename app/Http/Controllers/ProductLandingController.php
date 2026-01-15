<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ProductLandingController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategorySlug = (string) $request->query('cat', '');

        $listingUrl = Route::has('clearance.index')
            ? route('clearance.index')
            : (Route::has('products.index') ? route('products.index') : '#');

        $productDetailRouteName = Route::has('products.show')
            ? 'products.show'
            : (Route::has('clearance.show') ? 'clearance.show' : null);

        [$categories, $products, $featured, $bestSellers, $dealProduct, $reviews, $isFallback] = $this->loadLandingData();

        $payload = [
            'selectedCategory' => $selectedCategorySlug,
            'listingUrl' => $listingUrl,
            'productDetailRouteName' => $productDetailRouteName,
            'isFallback' => $isFallback,
            'categories' => $categories,
            'products' => $products,
            'featured' => $featured,
            'bestSellers' => $bestSellers,
            'dealProduct' => $dealProduct,
            'reviews' => $reviews,
        ];

        return view('frontend.products.landing', [
            'payload' => $payload,
            'selectedCategorySlug' => $selectedCategorySlug,
            'listingUrl' => $listingUrl,
            'productDetailRouteName' => $productDetailRouteName,
            'isFallback' => $isFallback,
            'categories' => $categories,
            'products' => $products,
            'featured' => $featured,
            'bestSellers' => $bestSellers,
            'dealProduct' => $dealProduct,
            'reviews' => $reviews,
        ]);
    }

    private function loadLandingData(): array
    {
        try {
            $categories = Category::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->limit(12)
                ->get()
                ->map(function (Category $category) {
                    $slug = Str::slug($category->name);
                    $imageCandidate = public_path('image/categories/' . $slug . '.png');
                    $image = file_exists($imageCandidate)
                        ? asset('image/categories/' . $slug . '.png')
                        : asset('image/no-image.png');

                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $slug,
                        'image' => $image,
                    ];
                })
                ->values()
                ->all();

            $productQuery = Product::query()->where('is_active', true);

            if (Schema::hasColumn('products', 'category_id')) {
                $productQuery->with('category');
            }

            $productQuery
                ->withCount('activeVariants')
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->limit(48);

            if (Schema::hasColumn('products', 'sold_count')) {
                $productQuery->orderByDesc('sold_count');
            } else {
                $productQuery->latest('id');
            }

            $productModels = $productQuery->get();

            $products = $productModels->map(function (Product $product) {
                $displayPrice = (float) ($product->display_price ?? $product->price ?? 0);
                $displayOriginal = (float) ($product->display_original_price ?? $product->original_price ?? 0);
                $isSale = $displayOriginal > 0 && $displayOriginal > $displayPrice;
                $discount = $isSale ? (int) ($product->discount_percentage ?? 0) : 0;

                $rating = (float) ($product->reviews_avg_rating ?? 0);
                if ($rating <= 0 && method_exists($product, 'reviews')) {
                    $rating = (float) $product->reviews()->avg('rating');
                }

                $categoryName = $product->category?->name ?? null;
                $categorySlug = $categoryName ? Str::slug($categoryName) : '';

                $badges = [];
                if ($isSale && $discount >= 10) {
                    $badges[] = 'SALE';
                }
                if ($discount >= 30) {
                    $badges[] = 'HOT';
                }
                if (!$isSale) {
                    $badges[] = 'NEW';
                }

                return [
                    'id' => $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'image' => $product->image ? asset($product->image) : asset('image/no-image.png'),
                    'original_price' => $displayOriginal,
                    'sale_price' => $displayPrice,
                    'rating' => round($rating, 1),
                    'sold_count' => (int) ($product->sold_count ?? 0),
                    'is_featured' => (bool) ($product->is_featured ?? false),
                    'category_id' => (int) ($product->category_id ?? 0),
                    'category_name' => $categoryName,
                    'category_slug' => $categorySlug,
                    'has_variants' => (int) ($product->active_variants_count ?? 0) > 0,
                    'badges' => array_values(array_unique($badges)),
                ];
            })->values()->all();

            $featured = $this->pickFeaturedProducts($products);
            $bestSellers = array_slice($products, 0, 12);
            $dealProduct = $this->pickBestDeal($products);

            $reviews = ProductReview::query()
                ->where('is_approved', true)
                ->with(['user:id,name,avatar', 'product:id,name,slug'])
                ->latest('id')
                ->limit(10)
                ->get()
                ->map(function (ProductReview $review) {
                    return [
                        'id' => $review->id,
                        'rating' => (int) $review->rating,
                        'title' => (string) ($review->title ?? ''),
                        'content' => (string) ($review->content ?? ''),
                        'user' => [
                            'name' => (string) ($review->user?->name ?? 'Khách hàng'),
                            'avatar' => $review->user?->avatar ? asset($review->user->avatar) : asset('image/logo.png'),
                        ],
                        'product' => [
                            'name' => (string) ($review->product?->name ?? ''),
                            'slug' => (string) ($review->product?->slug ?? ''),
                        ],
                    ];
                })
                ->values()
                ->all();

            if (count($categories) === 0 || count($products) === 0) {
                throw new QueryException('', [], new \Exception('Empty dataset'));
            }

            return [$categories, $products, $featured, $bestSellers, $dealProduct, $reviews, false];
        } catch (Throwable $e) {
            return $this->fallbackLandingData();
        }
    }

    private function pickFeaturedProducts(array $products): array
    {
        $featured = array_values(array_filter($products, fn ($p) => !empty($p['is_featured'])));
        if (count($featured) >= 4) {
            return array_slice($featured, 0, 4);
        }

        $sale = array_values(array_filter($products, function ($p) {
            return ($p['original_price'] ?? 0) > 0 && ($p['original_price'] ?? 0) > ($p['sale_price'] ?? 0);
        }));
        $merged = array_merge($featured, $sale, $products);

        $unique = [];
        foreach ($merged as $item) {
            $unique[$item['id']] = $item;
            if (count($unique) >= 4) {
                break;
            }
        }

        return array_values($unique);
    }

    private function pickBestDeal(array $products): ?array
    {
        $best = null;
        $bestDiscount = 0;

        foreach ($products as $product) {
            $original = (float) ($product['original_price'] ?? 0);
            $sale = (float) ($product['sale_price'] ?? 0);
            if ($original <= 0 || $sale <= 0 || $sale >= $original) {
                continue;
            }

            $discount = (int) round((($original - $sale) / $original) * 100);
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $best = $product;
                $best['discount'] = $discount;
            }
        }

        return $best;
    }

    private function fallbackLandingData(): array
    {
        $categories = [
            ['id' => 1, 'name' => 'Tai nghe', 'slug' => 'tai-nghe', 'image' => asset('image/no-image.png')],
            ['id' => 2, 'name' => 'Loa', 'slug' => 'loa', 'image' => asset('image/no-image.png')],
            ['id' => 3, 'name' => 'Phụ kiện', 'slug' => 'phu-kien', 'image' => asset('image/no-image.png')],
            ['id' => 4, 'name' => 'Chuột & Bàn phím', 'slug' => 'chuot-ban-phim', 'image' => asset('image/no-image.png')],
            ['id' => 5, 'name' => 'Sạc & Cáp', 'slug' => 'sac-cap', 'image' => asset('image/no-image.png')],
            ['id' => 6, 'name' => 'Thiết bị mạng', 'slug' => 'thiet-bi-mang', 'image' => asset('image/no-image.png')],
            ['id' => 7, 'name' => 'Gaming', 'slug' => 'gaming', 'image' => asset('image/no-image.png')],
            ['id' => 8, 'name' => 'Văn phòng', 'slug' => 'van-phong', 'image' => asset('image/no-image.png')],
        ];

        $products = collect([
            [
                'id' => 101,
                'name' => 'Tai nghe Bluetooth DNT AirPulse',
                'slug' => 'dnt-airpulse',
                'image' => asset('image/no-image.png'),
                'original_price' => 1290000,
                'sale_price' => 990000,
                'rating' => 4.8,
                'sold_count' => 812,
                'is_featured' => true,
                'category_id' => 1,
                'category_name' => 'Tai nghe',
                'category_slug' => 'tai-nghe',
                'has_variants' => true,
                'badges' => ['HOT', 'SALE'],
            ],
            [
                'id' => 102,
                'name' => 'Loa mini DNT NeonBeat',
                'slug' => 'dnt-neonbeat',
                'image' => asset('image/no-image.png'),
                'original_price' => 890000,
                'sale_price' => 690000,
                'rating' => 4.6,
                'sold_count' => 540,
                'is_featured' => true,
                'category_id' => 2,
                'category_name' => 'Loa',
                'category_slug' => 'loa',
                'has_variants' => false,
                'badges' => ['NEW'],
            ],
            [
                'id' => 103,
                'name' => 'Sạc nhanh DNT Turbo 65W',
                'slug' => 'dnt-turbo-65w',
                'image' => asset('image/no-image.png'),
                'original_price' => 590000,
                'sale_price' => 449000,
                'rating' => 4.7,
                'sold_count' => 910,
                'is_featured' => false,
                'category_id' => 5,
                'category_name' => 'Sạc & Cáp',
                'category_slug' => 'sac-cap',
                'has_variants' => false,
                'badges' => ['SALE'],
            ],
            [
                'id' => 104,
                'name' => 'Chuột không dây DNT Flux',
                'slug' => 'dnt-flux-mouse',
                'image' => asset('image/no-image.png'),
                'original_price' => 490000,
                'sale_price' => 490000,
                'rating' => 4.5,
                'sold_count' => 420,
                'is_featured' => false,
                'category_id' => 4,
                'category_name' => 'Chuột & Bàn phím',
                'category_slug' => 'chuot-ban-phim',
                'has_variants' => false,
                'badges' => ['NEW'],
            ],
        ])->all();

        $featured = $this->pickFeaturedProducts($products);
        $bestSellers = $products;
        $dealProduct = $this->pickBestDeal($products);

        $reviews = [
            [
                'id' => 1,
                'rating' => 5,
                'title' => 'Giao nhanh, đóng gói kỹ',
                'content' => 'Mua tai nghe nhận trong 24h, hộp đẹp, dùng ổn định. Sẽ quay lại.',
                'user' => ['name' => 'Minh Anh', 'avatar' => asset('image/logo.png')],
                'product' => ['name' => 'DNT AirPulse', 'slug' => 'dnt-airpulse'],
            ],
            [
                'id' => 2,
                'rating' => 5,
                'title' => 'Giá tốt, tư vấn nhiệt tình',
                'content' => 'Hỏi chat được gợi ý đúng nhu cầu. Giá hợp lý, bảo hành rõ ràng.',
                'user' => ['name' => 'Quang Huy', 'avatar' => asset('image/logo.png')],
                'product' => ['name' => 'DNT Turbo 65W', 'slug' => 'dnt-turbo-65w'],
            ],
            [
                'id' => 3,
                'rating' => 4,
                'title' => 'Đáng tiền',
                'content' => 'Loa nhỏ nhưng âm khá. Màu nhìn “neon” đúng vibe cyber.',
                'user' => ['name' => 'Thanh Trúc', 'avatar' => asset('image/logo.png')],
                'product' => ['name' => 'DNT NeonBeat', 'slug' => 'dnt-neonbeat'],
            ],
        ];

        return [$categories, $products, $featured, $bestSellers, $dealProduct, $reviews, true];
    }
}
