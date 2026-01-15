<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Revenue;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\IsAdmin::class);
    }

    public function index()
    {
        // Thống kê tổng quan
        $totalUsers = User::count();
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $totalServices = Service::count();

        // Đặt lịch gần đây (5 cái gần nhất)
        $recentBookings = Booking::with('user')->latest()->take(5)->get();

        // 3 thông báo mới nhất
        $latestNotifications = auth()->user()->notifications()->latest()->take(3)->get();

        // Revenue for last 6 months
        $revenueLabels = json_encode([]);
        $revenueData = json_encode([]);
        $bookingRevenueData = [];
        $salesRevenueData = [];
        $totalRevenueData = [];
        $hasPriceColumn = Schema::hasColumn('bookings', 'price');

        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $start = $date->copy()->startOfMonth()->toDateTimeString();
            $end = $date->copy()->endOfMonth()->toDateTimeString();

            // Booking revenue (repair revenue)
            $bookingSum = 0;
            if ($hasPriceColumn) {
                $bookingSum = Booking::whereBetween('created_at', [$start, $end])
                    ->whereNotNull('price')
                    ->where(function ($q) {
                        $q->where('status', 'đã hoàn thành')
                          ->orWhere('status', 'completed')
                          ->orWhere('status', 'Đã hoàn thành');
                    })
                    ->sum('price');
            }
            $bookingRevenueData[] = (float) $bookingSum;

            // Sales revenue
            $salesSum = Order::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'completed')
                ->sum('total_amount');
            $salesRevenueData[] = (float) $salesSum;

            // Total revenue
            $totalRevenueData[] = (float) ($bookingSum + $salesSum);
        }

        $revenueLabels = json_encode($labels);
        $revenueData = json_encode($totalRevenueData); // For backward compatibility
        $bookingRevenueData = json_encode($bookingRevenueData);
        $salesRevenueData = json_encode($salesRevenueData);
        $totalRevenueData = json_encode($totalRevenueData);

        // Revenue by category for current month
        $currentMonthStart = Carbon::now()->startOfMonth()->toDateTimeString();
        $currentMonthEnd = Carbon::now()->endOfMonth()->toDateTimeString();

        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$currentMonthStart, $currentMonthEnd])
            ->where('orders.payment_status', 'completed')
            ->select('categories.name as category_name', DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('revenue', 'desc')
            ->get();

        $categoryLabels = json_encode($categoryRevenue->pluck('category_name')->toArray());
        $categoryRevenueData = json_encode($categoryRevenue->pluck('revenue')->map(function($value) {
            return (float) $value;
        })->toArray());

        // Daily revenue for last 7 days
        $dailyLabels = [];
        $dailyRevenueData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyLabels[] = $date->format('d/m');
            $start = $date->copy()->startOfDay()->toDateTimeString();
            $end = $date->copy()->endOfDay()->toDateTimeString();

            // Booking revenue for the day
            $bookingSum = 0;
            if ($hasPriceColumn) {
                $bookingSum = Booking::whereBetween('created_at', [$start, $end])
                    ->whereNotNull('price')
                    ->where(function ($q) {
                        $q->where('status', 'đã hoàn thành')
                          ->orWhere('status', 'completed')
                          ->orWhere('status', 'Đã hoàn thành');
                    })
                    ->sum('price');
            }

            // Sales revenue for the day
            $salesSum = Order::whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'completed')
                ->sum('total_amount');

            $dailyRevenueData[] = (float) ($bookingSum + $salesSum);
        }

        $dailyLabels = json_encode($dailyLabels);
        $dailyRevenueData = json_encode($dailyRevenueData);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalBookings',
            'pendingBookings',
            'totalServices',
            'recentBookings',
            'latestNotifications',
            'revenueLabels',
            'revenueData',
            'bookingRevenueData',
            'salesRevenueData',
            'totalRevenueData',
            'categoryLabels',
            'categoryRevenueData',
            'dailyLabels',
            'dailyRevenueData',
            'hasPriceColumn'
        ));
    }
}
