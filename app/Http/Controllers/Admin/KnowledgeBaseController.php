<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $tableMissing = !Schema::hasTable('knowledge_base');
        if ($tableMissing) {
            $items = new LengthAwarePaginator([], 0, 15);
            $items->withPath($request->url());
            return view('admin.knowledge-base.index', [
                'items' => $items,
                'search' => '',
                'tableMissing' => true,
                'categories' => [],
                'sourceTypes' => [],
            ]);
        }

        $query = KnowledgeBase::query();
        $search = trim((string)$request->query('q', ''));
        $category = $request->query('category');
        $sourceType = $request->query('source_type');
        $isActive = $request->query('is_active');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $query->when($category, fn($qq) => $qq->where('category', $category))
            ->when($sourceType, fn($qq) => $qq->where('source_type', $sourceType))
            ->when($isActive !== null && $isActive !== '', fn($qq) => $qq->where('is_active', (bool)$isActive))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo));

        $categories = KnowledgeBase::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values()
            ->all();

        $sourceTypes = KnowledgeBase::query()
            ->select('source_type')
            ->whereNotNull('source_type')
            ->distinct()
            ->orderBy('source_type')
            ->pluck('source_type')
            ->values()
            ->all();

        $items = $query->latest()->paginate(15)->appends($request->query());

        return view('admin.knowledge-base.index', [
            'items' => $items,
            'search' => $search,
            'tableMissing' => false,
            'categories' => $categories,
            'sourceTypes' => $sourceTypes,
        ]);
    }

    public function create()
    {
        if (!Schema::hasTable('knowledge_base')) {
            return redirect()->route('admin.knowledge-base.index')
                ->with('error', 'Chưa có bảng knowledge_base. Vui lòng chạy migration.');
        }
        return view('admin.knowledge-base.form');
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('knowledge_base')) {
            return redirect()->route('admin.knowledge-base.index')
                ->with('error', 'Chưa có bảng knowledge_base. Vui lòng chạy migration.');
        }
        $data = $this->validateData($request);
        KnowledgeBase::create($data);

        return redirect()->route('admin.knowledge-base.index')->with('success', 'Đã tạo mục kiến thức.');
    }

    public function edit(KnowledgeBase $knowledgeBase)
    {
        if (!Schema::hasTable('knowledge_base')) {
            return redirect()->route('admin.knowledge-base.index')
                ->with('error', 'Chưa có bảng knowledge_base. Vui lòng chạy migration.');
        }
        return view('admin.knowledge-base.form', ['item' => $knowledgeBase]);
    }

    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        if (!Schema::hasTable('knowledge_base')) {
            return redirect()->route('admin.knowledge-base.index')
                ->with('error', 'Chưa có bảng knowledge_base. Vui lòng chạy migration.');
        }
        $data = $this->validateData($request, true);
        $knowledgeBase->update($data);

        return redirect()->route('admin.knowledge-base.index')->with('success', 'Đã cập nhật mục kiến thức.');
    }

    public function destroy(KnowledgeBase $knowledgeBase)
    {
        if (!Schema::hasTable('knowledge_base')) {
            return redirect()->route('admin.knowledge-base.index')
                ->with('error', 'Chưa có bảng knowledge_base. Vui lòng chạy migration.');
        }
        $knowledgeBase->delete();
        return back()->with('success', 'Đã xoá mục kiến thức.');
    }

    private function validateData(Request $request, bool $isUpdate = false): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'source_type' => 'nullable|string|max:40',
            'content' => 'required|string',
            'tags' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['category'] = $data['category'] ?? 'general';
        $data['source_type'] = $data['source_type'] ?? 'faq';
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $tags = collect(explode(',', (string)($data['tags'] ?? '')))
            ->map(fn($t) => trim((string)$t))
            ->filter()
            ->values()
            ->all();
        $data['tags'] = $tags ?: null;

        if (!$isUpdate) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $data;
    }
}
