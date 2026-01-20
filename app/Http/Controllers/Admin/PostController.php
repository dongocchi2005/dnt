<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = trim((string)request('q', ''));
        $isActive = request('is_active');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $query = Post::query()
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                });
            })
            ->when($isActive !== null && $isActive !== '', fn($qq) => $qq->where('is_active', (bool)$isActive))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->latest();

        $posts = $query->paginate(15)->appends(request()->query());
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('image'), $filename);
            $data['image'] = 'image/' . $filename;
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được tạo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $post->id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('image'), $filename);
            $data['image'] = 'image/' . $filename;
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được cập nhật.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // delete image file if exists
        if (!empty($post->image) && file_exists(public_path($post->image))) {
            @unlink(public_path($post->image));
        }
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được xóa.');
    }
}
