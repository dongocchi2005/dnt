@extends('layouts.admin')

@section('title', 'Admin Dashboard - DNT Store')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-bl font-display neon">Admin Dashboard</h1>
    <p class="text-bl/60 mt-1">Quản lý hệ thống DNT Store</p>
</div>

{{-- Notifications --}}
<div class="cyber-panel p-6 mb-8">
    <h2 class="text-lg font-bold text-bl mb-4 flex items-center">
        <i class="fas fa-bell text-blue-500 mr-2 neon"></i> Thông báo mới
    </h2>
    <div class="space-y-3">
        @forelse($latestNotifications as $noti)
            <a href="{{ $noti->data['url'] ?? '#' }}" class="block p-4 rounded-lg border transition-all {{ $noti->read_at ? 'bg-white/5 border-white/5' : 'bg-blue-500/10 border-blue-500/20 hover:bg-blue-500/20 shadow-[0_0_10px_rgba(59,130,246,0.1)]' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-bl {{ !$noti->read_at ? 'neon' : '' }}">{{ $noti->data['title'] ?? 'Thông báo' }}</div>
                        <div class="text-sm text-bl/60 mt-1">{{ $noti->data['message'] ?? '' }}</div>
                    </div>
                    <span class="text-xs text-bl/40 whitespace-nowrap ml-4">{{ $noti->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <div class="text-center py-8 text-bl/40">
                <i class="fas fa-check-circle text-4xl text-white/10 mb-3 block"></i>
                Bạn chưa có thông báo nào.
            </div>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="cyber-panel p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500/20 text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.3)]">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-bl/60">Tổng người dùng</p>
                <p class="text-2xl font-bold text-bl neon">{{ $totalUsers ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="cyber-panel p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-emerald-500/20 text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.3)]">
                <i class="fas fa-calendar-check text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-bl/60">Tổng đặt lịch</p>
                <p class="text-2xl font-bold text-bl neon">{{ $totalBookings ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Pending Bookings -->
    <div class="cyber-panel p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500/20 text-yellow-400 shadow-[0_0_10px_rgba(234,179,8,0.3)]">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-bl/60">Đặt lịch chờ xử lý</p>
                <p class="text-2xl font-bold text-bl neon">{{ $pendingBookings ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Total Services -->
    <div class="cyber-panel p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500/20 text-purple-400 shadow-[0_0_10px_rgba(168,85,247,0.3)]">
                <i class="fas fa-tools text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-bl/60">Tổng dịch vụ</p>
                <p class="text-2xl font-bold text-bl neon">{{ $totalServices ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('admin.bookings.index') }}"
       class="cyber-panel p-6 hover:bg-white/5 transition-all group">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-cyan-500/20 text-cyan-400 group-hover:bg-cyan-500/30 transition-colors shadow-[0_0_10px_rgba(6,182,212,0.3)]">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-bl group-hover:text-cyan-400 transition-colors">Quản lý đặt lịch</h3>
                <p class="text-bl/50 text-sm">Xem và xử lý các yêu cầu đặt lịch</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.services.index') }}"
       class="cyber-panel p-6 hover:bg-white/5 transition-all group">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/30 transition-colors shadow-[0_0_10px_rgba(16,185,129,0.3)]">
                <i class="fas fa-tools text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-bl group-hover:text-emerald-400 transition-colors">Quản lý dịch vụ</h3>
                <p class="text-bl/50 text-sm">Thêm, sửa, xóa dịch vụ</p>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.users.index') }}"
       class="cyber-panel p-6 hover:bg-white/5 transition-all group">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500/20 text-purple-400 group-hover:bg-purple-500/30 transition-colors shadow-[0_0_10px_rgba(168,85,247,0.3)]">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-bl group-hover:text-purple-400 transition-colors">Quản lý người dùng</h3>
                <p class="text-bl/50 text-sm">Xem và quản lý tài khoản</p>
            </div>
        </div>
    </a>
</div>

<!-- Revenue Sections -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Booking Revenue -->
    <div class="cyber-panel p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-bl">Doanh thu đặt lịch</h3>
                <p class="text-bl/60 text-sm mt-1">6 tháng gần nhất</p>
            </div>
            <div class="text-sm bg-blue-500/10 text-blue-400 px-3 py-1 rounded-full font-bold border border-blue-500/20 shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                {{ number_format(array_sum((array) json_decode($bookingRevenueData ?? '[]', true)), 0, ',', '.') }} VND
            </div>
        </div>
        <div class="h-80 relative">
            <canvas id="bookingRevenueChart"></canvas>
        </div>
    </div>

    <!-- Sales Revenue -->
    <div class="cyber-panel p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-bl">Doanh thu bán hàng</h3>
                <p class="text-bl/60 text-sm mt-1">6 tháng gần nhất</p>
            </div>
            <div class="text-sm bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full font-bold border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                {{ number_format(array_sum((array) json_decode($salesRevenueData ?? '[]', true)), 0, ',', '.') }} VND
            </div>
        </div>
        <div class="h-80 relative">
            <canvas id="salesRevenueChart"></canvas>
        </div>
    </div>
</div>

<!-- Additional Revenue Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue by Category -->
    <div class="cyber-panel p-6">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-bl">Doanh thu theo danh mục</h3>
            <p class="text-bl/60 text-sm mt-1">Tháng hiện tại</p>
        </div>
        <div class="h-80 relative">
            <canvas id="categoryRevenueChart"></canvas>
        </div>
    </div>

    <!-- Daily Revenue Trend -->
    <div class="cyber-panel p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-bl">Xu hướng doanh thu ngày</h3>
                <p class="text-bl/60 text-sm mt-1">7 ngày gần nhất</p>
            </div>
            @php
                $dailyArr = (array) json_decode($dailyRevenueData ?? '[]', true);
                $dailyAvg = count($dailyArr) ? (array_sum($dailyArr) / count($dailyArr)) : 0;
            @endphp
            <div class="text-sm bg-orange-500/10 text-orange-400 px-3 py-1 rounded-full font-bold border border-orange-500/20 shadow-[0_0_10px_rgba(249,115,22,0.2)]">
                TB: {{ number_format($dailyAvg, 0, ',', '.') }} VND/ngày
            </div>
        </div>
        <div class="h-80 relative">
            <canvas id="dailyRevenueChart"></canvas>
        </div>
    </div>
</div>

<!-- Revenue Summary Table -->
<div class="cyber-panel p-6 mb-8 overflow-hidden">
    <div class="mb-6">
        <h3 class="text-lg font-bold text-bl">Bảng thống kê doanh thu</h3>
        <p class="text-bl/60 text-sm mt-1">Chi tiết doanh thu theo tháng</p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Tháng</th>
                    <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Đặt lịch</th>
                    <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Bán hàng</th>
                    <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Tổng</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @php
                    $labels = (array) json_decode($revenueLabels ?? '[]', true);
                    $bookingData = (array) json_decode($bookingRevenueData ?? '[]', true);
                    $salesData = (array) json_decode($salesRevenueData ?? '[]', true);
                    $totalData = (array) json_decode($totalRevenueData ?? '[]', true);
                @endphp

                @for($i = 0; $i < count($labels); $i++)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4 font-bold text-bl">{{ $labels[$i] ?? '' }}</td>
                        <td class="px-6 py-4 text-blue-400 font-medium">{{ number_format((int)($bookingData[$i] ?? 0), 0, ',', '.') }} VND</td>
                        <td class="px-6 py-4 text-emerald-400 font-medium">{{ number_format((int)($salesData[$i] ?? 0), 0, ',', '.') }} VND</td>
                        <td class="px-6 py-4 text-purple-400 font-bold neon">{{ number_format((int)($totalData[$i] ?? 0), 0, ',', '.') }} VND</td>
                    </tr>
                @endfor

                <tr class="bg-white/10 font-bold">
                    <td class="px-6 py-4 text-bl">TỔNG CỘNG</td>
                    <td class="px-6 py-4 text-blue-400">{{ number_format(array_sum($bookingData), 0, ',', '.') }} VND</td>
                    <td class="px-6 py-4 text-emerald-400">{{ number_format(array_sum($salesData), 0, ',', '.') }} VND</td>
                    <td class="px-6 py-4 text-purple-400 neon">{{ number_format(array_sum($totalData), 0, ',', '.') }} VND</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Total Revenue Chart -->
<div class="cyber-panel p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-bl">Tổng doanh thu</h3>
            <p class="text-bl/60 text-sm mt-1">Biểu đồ tổng hợp theo tháng</p>
        </div>
        <div class="text-sm bg-purple-500/10 text-purple-400 px-3 py-1 rounded-full font-bold border border-purple-500/20 shadow-[0_0_10px_rgba(168,85,247,0.2)]">
            {{ number_format(array_sum((array) json_decode($totalRevenueData ?? '[]', true)), 0, ',', '.') }} VND
        </div>
    </div>
    <div class="h-80 relative">
        <canvas id="totalRevenueChart"></canvas>
    </div>
</div>
@endsection

@php
    $revenueLabelsArr   = json_decode($revenueLabels ?? '[]', true) ?: [];
    $bookingRevenueArr  = json_decode($bookingRevenueData ?? '[]', true) ?: [];
    $salesRevenueArr    = json_decode($salesRevenueData ?? '[]', true) ?: [];
    $totalRevenueArr    = json_decode($totalRevenueData ?? '[]', true) ?: [];

    $categoryLabelsArr  = json_decode($categoryLabels ?? '[]', true) ?: [];
    $categoryRevenueArr = json_decode($categoryRevenueData ?? '[]', true) ?: [];

    $dailyLabelsArr     = json_decode($dailyLabels ?? '[]', true) ?: [];
    $dailyRevenueArr    = json_decode($dailyRevenueData ?? '[]', true) ?: [];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const revenueLabels  = @json($revenueLabelsArr);
    const bookingData    = @json($bookingRevenueArr);
    const salesData      = @json($salesRevenueArr);
    const totalData      = @json($totalRevenueArr);

    const categoryLabels = @json($categoryLabelsArr);
    const categoryData   = @json($categoryRevenueArr);

    const dailyLabels    = @json($dailyLabelsArr);
    const dailyData      = @json($dailyRevenueArr);

    function toNumberArray(arr) {
        if (!Array.isArray(arr)) return [];
        return arr.map(v => Number(v) || 0);
    }

    // Colors
    const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : '#e2e8f0';
    const tooltipBg = isDark ? 'rgba(15, 23, 42, 0.9)' : 'rgba(255, 255, 255, 0.9)';
    const tooltipText = isDark ? '#f8fafc' : '#0f172a';

    // Common Chart Options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: textColor, font: { family: 'DM Sans' } } },
            tooltip: {
                backgroundColor: tooltipBg,
                titleColor: tooltipText,
                bodyColor: tooltipText,
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: (c) => ' ' + (Number(c.raw)||0).toLocaleString('vi-VN') + ' VND'
                }
            }
        },
        scales: {
            x: { 
                ticks: { color: textColor }, 
                grid: { color: gridColor, drawBorder: false } 
            },
            y: { 
                beginAtZero: true, 
                ticks: { color: textColor }, 
                grid: { color: gridColor, borderDash: [4, 4], drawBorder: false } 
            }
        }
    };

    function initBarChart(canvasId, labels, data, datasetLabel, color) {
        const el = document.getElementById(canvasId);
        if (!el || typeof Chart === 'undefined') return;

        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: datasetLabel,
                    data: toNumberArray(data),
                    backgroundColor: color,
                    borderRadius: 6,
                    barThickness: 24,
                    hoverBackgroundColor: color
                }]
            },
            options: commonOptions
        });
    }

    initBarChart('bookingRevenueChart', revenueLabels, bookingData, 'Doanh thu đặt lịch', '#3b82f6'); // blue-500
    initBarChart('salesRevenueChart',  revenueLabels, salesData,   'Doanh thu bán hàng', '#10b981'); // emerald-500
    initBarChart('totalRevenueChart',  revenueLabels, totalData,   'Tổng doanh thu', '#8b5cf6');   // violet-500

    // Doughnut Chart
    (function () {
        const el = document.getElementById('categoryRevenueChart');
        if (!el || typeof Chart === 'undefined') return;

        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: toNumberArray(categoryData),
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: textColor }, position: 'bottom' },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        callbacks: {
                            label: (c) => (c.label || '') + ': ' + (Number(c.raw)||0).toLocaleString('vi-VN') + ' VND'
                        }
                    }
                }
            }
        });
    })();

    // Line Chart
    (function () {
        const el = document.getElementById('dailyRevenueChart');
        if (!el || typeof Chart === 'undefined') return;

        new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Doanh thu ngày',
                    data: toNumberArray(dailyData),
                    borderColor: '#f97316', // orange-500
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#f97316',
                    pointBorderWidth: 2
                }]
            },
            options: commonOptions
        });
    })();

});
</script>
@endpush
