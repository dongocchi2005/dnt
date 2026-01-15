<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class AdminBlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\IsAdmin::class);
    }

    public function index()
    {
        $posts = Post::latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['title', 'content', 'excerpt', 'is_active']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Bài viết đã được tạo thành công.');
    }

    public function show(Post $post)
    {
        return view('admin.blog.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->only(['title', 'content', 'excerpt', 'is_active']);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Bài viết đã được cập nhật thành công.');
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Bài viết đã được xóa thành công.');
    }
    public function confirmPayment(Request $request, Booking $booking)
{
    // chặn xác nhận khi chưa có ảnh
    if (!$booking->payment_proof) {
        return back()->with('error', 'Chưa có ảnh chuyển khoản để xác nhận.');
    }

    $booking->update([
        'payment_status' => 'completed',
    ]);

    return back()->with('success', 'Đã xác nhận thanh toán đặt lịch.');
}

public function rejectPayment(Request $request, Booking $booking)
{
    $booking->update([
        'payment_status' => 'failed',
    ]);

    return back()->with('success', 'Đã từ chối thanh toán đặt lịch.');
}

}
