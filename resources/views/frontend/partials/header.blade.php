<header id="app-header">
    @php
        $user = Auth::user();

        // Tùy dự án bạn: dùng role hoặc is_admin
        $isAdmin = $user && (
            in_array(($user->role ?? ''), ['admin', 'superadmin'], true)
            || (($user->is_admin ?? false) === true)
        );
    @endphp

    <div class="header-container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="logo-link flex items-center gap-2">
            <img
                src="{{ asset('image/logo.png') }}"
                alt="DNT Store Logo"
                class="h-8 w-auto object-contain"
            >
            <span class="logo-highlight" style="color: var(--cyber-orange, #F37021) !important;">DNT</span><span>Store</span>
        </a>

        <!-- Desktop Nav -->
        <nav class="desktop-nav" aria-label="Main Navigation">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                Trang chủ
            </a>
            <a href="{{ route('clearance.index') }}" class="nav-link {{ request()->routeIs('clearance.*') ? 'active' : '' }}">
                Sản phẩm
            </a>
            <a href="{{ route('services') }}" class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}">
                Dịch vụ
            </a>
            <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
                Tin tức
            </a>
            <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                Liên hệ
            </a>
        </nav>

        <!-- Actions -->
        <div class="actions-group">
            <!-- Theme Toggle -->
            <button id="theme-toggle" class="icon-btn" aria-label="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>

        @php
    $unreadCount = 0;
    $unreadNotis = collect();
    $readNotis   = collect();

    if(auth()->check()){
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $unreadNotis = auth()->user()->unreadNotifications()->latest()->take(10)->get();
        $readNotis   = auth()->user()->readNotifications()->latest()->take(5)->get();
    }
@endphp

<div id="notify-dropdown-container" class="user-dropdown-container" aria-expanded="false">
    <button id="notify-btn" class="icon-btn" aria-label="Notifications" aria-haspopup="true">
        <i class="fas fa-bell"></i>

        @if(auth()->check() && $unreadCount > 0)
            <span id="notify-badge" class="badge" style="background-color: var(--header-accent); color: black;">
                {{ $unreadCount }}
            </span>
        @else
            <span id="notify-badge" class="badge" style="display:none; background-color: var(--header-accent); color: black;">0</span>
        @endif
    </button>

    <div class="user-menu" role="menu" style="width: 320px;">
        <div class="px-4 py-3 font-bold border-b border-gray-100">
            Thông báo
        </div>

        <div class="max-h-64 overflow-y-auto">
            @guest
                <div class="px-4 py-3 text-sm text-gray-500">
                    Vui lòng đăng nhập để xem thông báo.
                </div>
            @endguest

            @auth
                {{-- UNREAD --}}
                @if($unreadNotis->count() > 0)
                    @foreach($unreadNotis as $n)
                        @php
                            $data  = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?? []);
                            $title = $data['title'] ?? 'Thông báo mới';
                            $msg   = $data['message'] ?? ($data['body'] ?? '');
                            $url   = $data['url'] ?? '#';
                        @endphp

                        <a href="{{ $url }}"
                           class="dropdown-item flex-col items-start gap-1"
                           data-notification-id="{{ $n->id }}"
                           data-notification-unread="1"
                        >
                            <span class="font-semibold text-sm">{{ $title }}</span>
                            @if($msg)
                                <span class="text-xs text-gray-500">{{ $msg }}</span>
                            @endif
                            <span class="text-[11px] text-gray-400">{{ optional($n->created_at)->diffForHumans() }}</span>
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-3 text-sm text-gray-500">
                        Không có thông báo mới.
                    </div>
                @endif

                {{-- READ --}}
                @if($readNotis->count() > 0)
                    <div class="px-4 py-2 text-xs font-bold text-gray-500 border-t border-gray-100">
                        Đã đọc gần đây
                    </div>

                    @foreach($readNotis as $n)
                        @php
                            $data  = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?? []);
                            $title = $data['title'] ?? 'Thông báo';
                            $msg   = $data['message'] ?? ($data['body'] ?? '');
                            $url   = $data['url'] ?? '#';
                        @endphp

                        <a href="{{ $url }}" class="dropdown-item flex-col items-start gap-1 opacity-80">
                            <span class="font-semibold text-sm">{{ $title }}</span>
                            @if($msg)
                                <span class="text-xs text-gray-500">{{ $msg }}</span>
                            @endif
                            <span class="text-[11px] text-gray-400">{{ optional($n->created_at)->diffForHumans() }}</span>
                        </a>
                    @endforeach
                @endif
            @endauth
        </div>

        <div class="border-t border-gray-100 p-2 text-center">
            {{-- Bạn CHƯA có route notifications.index, nên để tạm --}}
            <a href="#" class="text-xs font-bold text-primary">Xem tất cả</a>
        </div>
    </div>
</div>

            <!-- Cart -->
            <a href="{{ route('cart.index') }}" class="icon-btn" aria-label="View Cart">
                <i class="fas fa-shopping-cart"></i>
                @php
                    $cartCount = (int) collect(session('cart.items', []))->sum(fn($i) => (int)($i['qty'] ?? 0));
                @endphp
                <span class="badge" id="cart-count-badge" style="{{ $cartCount > 0 ? '' : 'display: none;' }}">{{ $cartCount }}</span>
            </a>

            <!-- User Menu (Avatar Dropdown) -->
            <div id="user-dropdown-container" class="user-dropdown-container" aria-expanded="false">
                <button id="user-menu-btn" class="icon-btn" aria-label="User Menu" aria-haspopup="true">
                    <i class="fas fa-user-circle"></i>
                </button>

                <div class="user-menu" role="menu">
                    @auth
                        <div class="px-4 py-2 text-sm text-gray-500 border-b border-gray-100">
                            Xin chào, {{ Auth::user()->name }}
                        </div>

                        {{-- Lịch sử đơn hàng --}}
                        @if(Route::has('orders.history'))
                            <a href="{{ route('orders.history') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-box-open"></i> Lịch sử đơn hàng
                            </a>
                        @elseif(Route::has('orders.index'))
                            <a href="{{ route('orders.index') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-box-open"></i> Lịch sử đơn hàng
                            </a>
                        @endif

                        {{-- Lịch sử đặt lịch --}}
                        @if(Route::has('booking.history'))
                            <a href="{{ route('booking.history') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-calendar-check"></i> Lịch sử đặt lịch
                            </a>
                        @elseif(Route::has('bookings.history'))
                            <a href="{{ route('bookings.history') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-calendar-check"></i> Lịch sử đặt lịch
                            </a>
                        @elseif(Route::has('bookings.index'))
                            <a href="{{ route('bookings.index') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-calendar-check"></i> Lịch sử đặt lịch
                            </a>
                        @endif

                        {{-- Tài khoản / Cài đặt (ưu tiên route settings) --}}
                        @if(Route::has('settings'))
                            <a href="{{ route('settings') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-cog"></i> Tài khoản
                            </a>
                        @elseif(Route::has('settings.index'))
                            <a href="{{ route('settings.index') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-cog"></i> Tài khoản
                            </a>
                        @elseif(Route::has('profile.settings'))
                            <a href="{{ route('profile.settings') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-cog"></i> Tài khoản
                            </a>
                        @elseif(Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-cog"></i> Tài khoản
                            </a>
                        @elseif(Route::has('profile'))
                            <a href="{{ route('profile') }}" class="dropdown-item" role="menuitem">
                                <i class="fas fa-user-cog"></i> Tài khoản
                            </a>
                        @endif

                        {{-- Quản trị (Admin) --}}
                        @if($isAdmin)
                            @if(Route::has('admin.dashboard'))
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-gauge-high"></i> Quản trị
                                </a>
                            @elseif(Route::has('admin.index'))
                                <a href="{{ route('admin.index') }}" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-gauge-high"></i> Quản trị
                                </a>
                            @elseif(Route::has('admin'))
                                <a href="{{ route('admin') }}" class="dropdown-item" role="menuitem">
                                    <i class="fas fa-gauge-high"></i> Quản trị
                                </a>
                            @endif
                        @endif

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item w-full text-left" role="menuitem">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="dropdown-item" role="menuitem">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="dropdown-item" role="menuitem">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Toggle -->
            <button id="mobile-toggle" class="icon-btn mobile-toggle" aria-label="Open Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<div id="drawer-overlay" class="mobile-drawer-overlay" aria-hidden="true"></div>

<aside id="mobile-drawer" class="mobile-drawer" aria-label="Mobile Navigation">
    <div class="drawer-header">
        <span class="text-xl font-bold">Menu</span>
        <button id="close-drawer" class="icon-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="drawer-content">
        <nav class="flex flex-col">
            <a href="{{ route('home') }}" class="mobile-nav-link">Trang chủ</a>
            <a href="{{ route('clearance.index') }}" class="mobile-nav-link">Sản phẩm</a>
            <a href="{{ route('services') }}" class="mobile-nav-link">Dịch vụ</a>
            <a href="{{ route('blog.index') }}" class="mobile-nav-link">Tin tức</a>
            <a href="{{ route('contact') }}" class="mobile-nav-link">Liên hệ</a>

            <div class="mt-4 border-t pt-4">
                @auth
                    @if(Route::has('orders.history'))
                        <a href="{{ route('orders.history') }}" class="mobile-nav-link">Lịch sử đơn hàng</a>
                    @elseif(Route::has('orders.index'))
                        <a href="{{ route('orders.index') }}" class="mobile-nav-link">Lịch sử đơn hàng</a>
                    @endif

                    @if(Route::has('booking.history'))
                        <a href="{{ route('booking.history') }}" class="mobile-nav-link">Lịch sử đặt lịch</a>
                    @elseif(Route::has('bookings.history'))
                        <a href="{{ route('bookings.history') }}" class="mobile-nav-link">Lịch sử đặt lịch</a>
                    @elseif(Route::has('bookings.index'))
                        <a href="{{ route('bookings.index') }}" class="mobile-nav-link">Lịch sử đặt lịch</a>
                    @endif

                    @if(Route::has('settings'))
                        <a href="{{ route('settings') }}" class="mobile-nav-link">Tài khoản</a>
                    @elseif(Route::has('settings.index'))
                        <a href="{{ route('settings.index') }}" class="mobile-nav-link">Tài khoản</a>
                    @elseif(Route::has('profile.settings'))
                        <a href="{{ route('profile.settings') }}" class="mobile-nav-link">Tài khoản</a>
                    @elseif(Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" class="mobile-nav-link">Tài khoản</a>
                    @elseif(Route::has('profile'))
                        <a href="{{ route('profile') }}" class="mobile-nav-link">Tài khoản</a>
                    @endif

                    @if($isAdmin)
                        @if(Route::has('admin.dashboard'))
                            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link">Quản trị</a>
                        @elseif(Route::has('admin.index'))
                            <a href="{{ route('admin.index') }}" class="mobile-nav-link">Quản trị</a>
                        @elseif(Route::has('admin'))
                            <a href="{{ route('admin') }}" class="mobile-nav-link">Quản trị</a>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 font-bold text-red-500">
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mobile-nav-link">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="mobile-nav-link">Đăng ký</a>
                @endauth
            </div>
        </nav>
    </div>
</aside>
