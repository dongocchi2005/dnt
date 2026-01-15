<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', 'all');
        $sort = (string) $request->query('sort', 'newest');

        $categories = $this->demoCategories();
        $tags = $this->demoTags();

        if ($this->canQueryPostsTable()) {
            $postsQuery = Post::query();

            if (Schema::hasColumn('posts', 'is_active')) {
                $postsQuery->where('is_active', true);
            }

            if ($q !== '') {
                $postsQuery->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', '%' . $q . '%');
                    if (Schema::hasColumn('posts', 'excerpt')) {
                        $sub->orWhere('excerpt', 'like', '%' . $q . '%');
                    }
                    if (Schema::hasColumn('posts', 'content')) {
                        $sub->orWhere('content', 'like', '%' . $q . '%');
                    }
                });
            }

            if ($category !== '' && $category !== 'all') {
                if (Schema::hasColumn('posts', 'category_id')) {
                    $categoryId = is_numeric($category)
                        ? (int) $category
                        : (int) ($categories->firstWhere('slug', $category)->id ?? 0);

                    if ($categoryId > 0) {
                        $postsQuery->where('category_id', $categoryId);
                    }
                }

                if (Schema::hasColumn('posts', 'category')) {
                    $postsQuery->where('category', $category);
                }
            }

            $viewsColumn = Schema::hasColumn('posts', 'views');
            $publishedAtColumn = Schema::hasColumn('posts', 'published_at');

            if ($sort === 'popular' && $viewsColumn) {
                $postsQuery->orderByDesc('views')->orderByDesc($publishedAtColumn ? 'published_at' : 'created_at');
            } else {
                $postsQuery->orderByDesc($publishedAtColumn ? 'published_at' : 'created_at');
            }

            $posts = $postsQuery->paginate(9)->withQueryString();

            $featuredQuery = Post::query();
            if (Schema::hasColumn('posts', 'is_active')) {
                $featuredQuery->where('is_active', true);
            }
            if ($viewsColumn) {
                $featuredQuery->orderByDesc('views');
            }
            $featuredQuery->orderByDesc($publishedAtColumn ? 'published_at' : 'created_at');
            $featured = $featuredQuery->limit(3)->get();

            $popularQuery = Post::query();
            if (Schema::hasColumn('posts', 'is_active')) {
                $popularQuery->where('is_active', true);
            }
            if ($viewsColumn) {
                $popularQuery->orderByDesc('views');
            }
            $popularQuery->orderByDesc($publishedAtColumn ? 'published_at' : 'created_at');
            $popular = $popularQuery->limit(5)->get();

            $stats = [
                'posts' => (int) Post::when(Schema::hasColumn('posts', 'is_active'), fn ($q) => $q->where('is_active', true))->count(),
                'categories' => (int) $categories->count(),
                'views' => $viewsColumn
                    ? (int) Post::when(Schema::hasColumn('posts', 'is_active'), fn ($q) => $q->where('is_active', true))->sum('views')
                    : ((int) $posts->total() * 120),
            ];

            return view('frontend.blog.index', compact('posts', 'featured', 'popular', 'categories', 'tags', 'stats', 'q', 'category', 'sort'));
        }

        $all = $this->demoPosts($categories);

        $filtered = $all
            ->when($q !== '', function (Collection $c) use ($q) {
                $needle = mb_strtolower($q);
                return $c->filter(function ($p) use ($needle) {
                    return str_contains(mb_strtolower((string) $p->title), $needle)
                        || str_contains(mb_strtolower((string) $p->excerpt), $needle)
                        || str_contains(mb_strtolower(strip_tags((string) $p->content)), $needle);
                });
            })
            ->when($category !== '' && $category !== 'all', fn (Collection $c) => $c->where('category_slug', $category))
            ->values();

        $sorted = $sort === 'popular'
            ? $filtered->sortByDesc('views')->values()
            : $filtered->sortByDesc('published_at')->values();

        $featured = $all->sortByDesc('views')->take(3)->values();
        $popular = $all->sortByDesc('views')->take(5)->values();

        $posts = $this->paginateCollection($sorted, 9, $request);
        $stats = [
            'posts' => (int) $all->count(),
            'categories' => (int) $categories->count(),
            'views' => (int) $all->sum('views'),
        ];

        return view('frontend.blog.index', compact('posts', 'featured', 'popular', 'categories', 'tags', 'stats', 'q', 'category', 'sort'));
    }

    public function show(Request $request, string $slug)
    {
        $categories = $this->demoCategories();
        $tags = $this->demoTags();

        if ($this->canQueryPostsTable()) {
            $postQuery = Post::query()->where('slug', $slug);
            if (Schema::hasColumn('posts', 'is_active')) {
                $postQuery->where('is_active', true);
            }
            $post = $postQuery->firstOrFail();

            if (Schema::hasColumn('posts', 'views')) {
                Post::whereKey($post->getKey())->increment('views');
                $post->views = (int) ($post->views ?? 0) + 1;
            }

            $relatedQuery = Post::query()->where('slug', '!=', $slug);
            if (Schema::hasColumn('posts', 'is_active')) {
                $relatedQuery->where('is_active', true);
            }
            $relatedQuery->orderByDesc(Schema::hasColumn('posts', 'published_at') ? 'published_at' : 'created_at');
            $related = $relatedQuery->limit(4)->get();

            return view('frontend.blog.show', compact('post', 'related', 'categories', 'tags'));
        }

        $all = $this->demoPosts($categories);
        $post = $all->firstWhere('slug', $slug);
        abort_if(!$post, 404);

        $related = $all->where('slug', '!=', $slug)->sortByDesc('published_at')->take(4)->values();

        return view('frontend.blog.show', compact('post', 'related', 'categories', 'tags'));
    }

    private function canQueryPostsTable(): bool
    {
        try {
            return class_exists(Post::class) && Schema::hasTable('posts') && Post::query()->limit(1)->count() >= 0;
        } catch (\Throwable) {
            return false;
        }
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = (int) $request->query('page', 1);
        $page = $page > 0 ? $page : 1;

        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function demoCategories(): Collection
    {
        return collect([
            (object) ['id' => 1, 'name' => 'Bảo dưỡng', 'slug' => 'bao-duong'],
            (object) ['id' => 2, 'name' => 'Sửa iPhone', 'slug' => 'sua-iphone'],
            (object) ['id' => 3, 'name' => 'Sửa Android', 'slug' => 'sua-android'],
            (object) ['id' => 4, 'name' => 'MacBook/Laptop', 'slug' => 'macbook-laptop'],
            (object) ['id' => 5, 'name' => 'Phần mềm', 'slug' => 'phan-mem'],
        ]);
    }

    private function demoTags(): array
    {
        return [
            'Pin',
            'Màn hình',
            'Camera',
            'Bàn phím',
            'Nâng cấp SSD',
            'Tối ưu hiệu năng',
            'Chống nước',
            'Bảo mật',
        ];
    }

    private function demoPosts(Collection $categories): Collection
    {
        $now = Carbon::now();
        $cat = $categories->keyBy('id');

        $make = function (int $i, int $categoryId) use ($now, $cat) {
            $publishedAt = $now->copy()->subDays($i * 3)->subHours($i % 7);
            $category = $cat->get($categoryId);
            $views = 1800 - ($i * 63) + (($i % 4) * 37);

            return (object) [
                'id' => $i,
                'title' => "Mẹo kiểm tra nhanh lỗi thường gặp (#{$i})",
                'slug' => "meo-kiem-tra-nhanh-loi-thuong-gap-{$i}",
                'excerpt' => 'Checklist gọn gàng giúp chẩn đoán nhanh: pin, màn hình, nhiệt, cổng sạc và phần mềm.',
                'content' => '<p>Trong bài này, DNT Store tổng hợp checklist cơ bản để bạn tự kiểm tra trước khi mang máy đi sửa.</p>'
                    . '<h2>1) Dấu hiệu cần chú ý</h2>'
                    . '<ul><li>Máy nóng bất thường</li><li>Sụt pin nhanh</li><li>Loạn cảm ứng</li></ul>'
                    . '<blockquote>Luôn sao lưu dữ liệu trước khi thử các bước can thiệp phần mềm.</blockquote>'
                    . '<h2>2) Nên làm gì tiếp?</h2>'
                    . '<p>Nếu bạn cần kiểm tra chuyên sâu, hãy đặt lịch để kỹ thuật viên kiểm tra miễn phí.</p>',
                'image' => asset('image/logo.png'),
                'category_id' => $categoryId,
                'category' => $category?->name ?? 'Chung',
                'category_slug' => $category?->slug ?? 'chung',
                'published_at' => $publishedAt,
                'views' => max(80, $views),
                'created_at' => $publishedAt,
            ];
        };

        return collect([
            $make(1, 2),
            $make(2, 1),
            $make(3, 4),
            $make(4, 3),
            $make(5, 2),
            $make(6, 5),
            $make(7, 1),
            $make(8, 4),
            $make(9, 3),
            $make(10, 2),
            $make(11, 5),
            $make(12, 1),
            $make(13, 4),
            $make(14, 3),
            $make(15, 2),
            $make(16, 5),
            $make(17, 1),
            $make(18, 4),
        ]);
    }
}
