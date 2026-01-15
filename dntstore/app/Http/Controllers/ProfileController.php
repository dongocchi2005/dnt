<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\Booking;
use App\Models\Service;
use App\Models\HomeBanner;
use App\Models\HomeFeature;
use App\Models\Product;
use App\Models\ProductReview;


class ProfileController extends Controller
{
    public function dashboard()
{
    $user = Auth::user();

    // Get current theme from session, default to 'light'
    $currentTheme = session('theme', 'light');

    return view('frontend.home', [
        // USER
        'user' => $user,

        // BOOKING
        'totalBookings' => Booking::where('user_id', $user->id)->count(),
        'latestBookings' => Booking::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(),

        // ✅ SLIDER BANNER (QUAN TRỌNG)
        'banners' => HomeBanner::all(),

        // SERVICES
        'services' => Service::latest()->take(6)->get(),

        // FEATURES
        'features' => HomeFeature::all(),

        // BEST SELLERS + REVIEWS
        'bestSellers' => Product::where('is_active', true)
            ->latest()
            ->take(4)
            ->get(),
        'reviews' => ProductReview::orderByDesc('created_at')
            ->take(3)
            ->get(),
    ]);
}

    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],

            // Nếu cho phép đổi email:
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],

            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],

            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->district = $request->district;
        $user->ward = $request->ward;
        $user->save();

        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng'])->withInput();
            }

            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }
}
