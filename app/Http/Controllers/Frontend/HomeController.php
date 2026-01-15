<?php

namespace App\Http\Controllers\Frontend;

    use App\Http\Controllers\Controller;
    use App\Models\Service;
    use App\Models\HomeBanner;
    use App\Models\ProductReview;
    use App\Models\HomeFeature;
    use App\Models\Product;
    class HomeController extends Controller
    {
    
    

    public function index()
    {
        $reviews = ProductReview::orderByDesc('created_at')
            ->take(3)
            ->get();

        return view('frontend.home', [
            'banners'   => HomeBanner::all(),
            'services' => Service::all(),
            'features' => HomeFeature::all(),
            'bestSellers' => Product::where('is_active', true)
                ->latest()
                ->take(4)
                ->get(),
            'reviews' => $reviews,
        ]);
    }

    }
