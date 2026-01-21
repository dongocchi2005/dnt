<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- APPLY THEME ASAP (chặn nháy trắng do theme apply muộn) --}}
    <script>
      (function () {
        try {
          const saved = localStorage.getItem('theme');
          const theme = (saved === 'dark' || saved === 'light') ? saved : 'light';
          document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      })();
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - DNT Store')</title>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $manifestPath = public_path('build/manifest.json');
        $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        $adminCssFile = $manifest['resources/css/pages/admin.css']['file'] ?? null;
    @endphp
    @if($adminCssFile)
        <link rel="stylesheet" href="{{ asset('build/' . $adminCssFile) }}">
    @endif

    <style>
        .admin-mobile-cards{display:none}
        @media (max-width:639px){
            .admin-table-wrap,.admin-table-mobile-hide{display:none!important}
            .admin-mobile-cards{display:flex;flex-direction:column;gap:.75rem}
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('css')
    @stack('head')
</head>

<body class="ui-admin font-sans font-medium antialiased">
<div class="admin-shell">

    {{-- SIDEBAR --}}
    <aside id="adminSidebar" class="admin-sidebar sidebar w-64 flex flex-col z-30 transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-white/10">
            <h1 class="text-2xl font-bold font-display neon">
                <span style="color: var(--cyber-orange, #F37021);">DNT</span>Store
            </h1>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto custom-scrollbar">
            <div class="space-y-1">
                <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Menu</p>

                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    Dashboard
                </a>

                <a href="{{ route('admin.chat-analytics.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.chat-analytics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                    Chat Analytics
                </a>

                <a href="{{ route('admin.chat-inbox.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.chat-inbox.*') || request()->routeIs('admin.chat-sessions.*') ? 'active' : '' }}">
                    <i class="fas fa-inbox w-5 h-5 mr-3"></i>
                    Chat Inbox
                </a>
            </div>

            <div class="mt-8 space-y-1">
                <p class="px-3 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Quản lý</p>

                <a href="{{ route('admin.bookings.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt w-5 h-5 mr-3"></i>
                    Đặt lịch
                </a>

                <a href="{{ route('admin.orders.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                    Đơn hàng
                </a>

                <a href="{{ route('admin.services.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-tools w-5 h-5 mr-3"></i>
                    Dịch vụ
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Người dùng
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-list w-5 h-5 mr-3"></i>
                    Danh mục
                </a>

                <a href="{{ route('admin.products.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes w-5 h-5 mr-3"></i>
                    Sản phẩm
                </a>

                <a href="{{ route('admin.posts.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper w-5 h-5 mr-3"></i>
                    Bài viết
                </a>

                <a href="{{ route('admin.knowledge-base.index') }}"
                   class="sidebar-link flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.knowledge-base.*') ? 'active' : '' }}">
                    <i class="fas fa-book w-5 h-5 mr-3"></i>
                    Knowledge Base
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-white/10">
             <div class="flex items-center gap-3">
                 <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-500 font-bold border border-blue-500/30">
                     {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                 </div>
                 <div class="flex-1 min-w-0">
                     <p class="text-sm font-medium text-bl truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                     <p class="text-xs text-bl/60 truncate">Admin</p>
                 </div>
             </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="admin-main">
        {{-- TOP BAR --}}
        <header class="topbar admin-topbar h-16 flex items-center justify-between px-4 sm:px-6 z-10">
            <div class="flex items-center gap-3">
                <button id="adminSidebarToggle" type="button" aria-controls="adminSidebar" aria-expanded="false"
                        class="admin-hamburger relative w-10 h-10 flex items-center justify-center rounded-lg text-bl/70 hover:bg-white/5 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                    <span class="sr-only">Mở menu điều hướng</span>
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 class="text-lg font-bold font-display text-bl">
                        @yield('page-title', 'Dashboard')
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button id="themeToggle" data-no-loading class="w-9 h-9 rounded-full flex items-center justify-center text-bl/70 hover:bg-white/5 transition-colors">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <div class="text-sm font-medium text-bl/70 hud-chip">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
        </header>

        {{-- CONTENT --}}
        <main class="admin-content custom-scrollbar">
            @yield('content')
        </main>
    </div>
</div>

<div class="admin-drawer-overlay" data-admin-overlay aria-hidden="true"></div>

@stack('scripts')

<div id="confirmModal" class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="confirm-backdrop" data-confirm-close></div>
    <div class="confirm-panel">
        <div id="confirmTitle" class="confirm-title">Xác nhận</div>
        <div id="confirmMessage" class="confirm-message">Bạn có chắc muốn xóa không?</div>
        <div class="confirm-actions">
            <button type="button" id="confirmCancel" class="confirm-btn confirm-cancel">Hủy</button>
            <button type="button" id="confirmOk" class="confirm-btn confirm-ok">Xóa</button>
        </div>
    </div>
</div>

<script>
/**
 * AUTO APPLY CYBER BUTTON + LOADING
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button').forEach(btn => {
        if (btn.hasAttribute('data-no-cyber')) return;
        btn.classList.add('cyber-btn');
    });

    document.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.hasAttribute('data-no-loading')) return;
            if ((btn.getAttribute('type') || '').toLowerCase() === 'submit') return;
            btn.classList.add('is-loading');
        });
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;
            if (submitBtn.hasAttribute('data-no-loading')) return;
            submitBtn.classList.add('is-loading');
        });
    });
});
</script>

<script>
/**
 * THEME TOGGLE (KHÔNG RELOAD -> hết nháy trắng khi đổi theme)
 */
(function(){
    const root = document.documentElement;
    const btn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    if(!btn) return;

    function syncIcon(){
        const theme = root.getAttribute('data-theme') || 'light';
        icon.className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }
    syncIcon();

    btn.addEventListener('click', ()=>{
        const current = root.getAttribute('data-theme') || 'light';
        const next = current === 'light' ? 'dark' : 'light';

        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        syncIcon();

        // Optional: lưu server-side (không reload)
        fetch('/set-theme', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ theme: next })
        }).catch(()=>{});
    });
})();
</script>

</body>
</html>
