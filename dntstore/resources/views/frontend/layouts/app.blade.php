<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
  <meta charset="UTF-8">
  <title>@yield('title','DNT Store – Công nghệ sửa chữa 5.0')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script>
(function () {
  try {
    let t = localStorage.getItem('dnt_theme');
    if (!t) {
      const preferDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      t = preferDark ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', t);
  } catch (e) {}
})();
</script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  
  @yield('styles')
  @stack('head')
</head>

<body class="font-sans font-medium transition-colors duration-500 ui-scale cyber-theme" data-page="{{ \Illuminate\Support\Facades\Route::currentRouteName() ?? '' }}">
@include('frontend.partials.ambient')
@include('frontend.partials.toast')

@php
  $crumbs = [['label' => 'Trang chủ', 'url' => route('home')]];
  $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
  $routeParams = request()->route()?->parameters() ?? [];
  $addCrumb = function($label, $url = null) use (&$crumbs) {
      $crumbs[] = ['label' => $label, 'url' => $url];
  };

  $productModel = $product ?? null;
  $productParam = $routeParams['slug'] ?? $routeParams['id'] ?? null;
  if (!$productModel && $productParam) {
      $productModel = \App\Models\Product::where('slug', $productParam)
          ->orWhere('id', $productParam)
          ->first();
  }

  $postModel = $post ?? null;
  $postParam = $routeParams['slug'] ?? null;
  if (!$postModel && $postParam && $routeName === 'blog.show') {
      $postModel = \App\Models\Post::where('slug', $postParam)->first();
  }

  $categoryName = null;
  if ($productModel) {
      $categoryName = $productModel->category?->name ?? $productModel->category ?? null;
  }

  switch ($routeName) {
      case 'home':
          $crumbs = [['label' => 'Trang chủ', 'url' => route('home')]];
          break;
      case 'services':
          $addCrumb('Dịch vụ');
          break;
      case 'contact':
          $addCrumb('Liên hệ');
          break;
      case 'blog.index':
          $addCrumb('Tin tức', route('blog.index'));
          break;
      case 'blog.show':
          $addCrumb('Tin tức', route('blog.index'));
          $addCrumb($postModel->title ?? 'Chi tiết');
          break;
      case 'clearance.index':
          $addCrumb('Sản phẩm', route('clearance.index'));
          break;
      case 'clearance.show':
          $addCrumb('Sản phẩm', route('clearance.index'));
          if ($categoryName) $addCrumb($categoryName);
          $addCrumb($productModel->name ?? 'Chi tiết');
          break;
      case 'products.show':
          $addCrumb('Sản phẩm', route('clearance.index'));
          if ($categoryName) $addCrumb($categoryName);
          $addCrumb($productModel->name ?? 'Chi tiết');
          break;
      case 'cart.index':
          $addCrumb('Giỏ hàng');
          break;
      case 'booking.create':
          $addCrumb('Đặt lịch');
          break;
      case 'booking.history':
          $addCrumb('Lịch sử đặt lịch');
          break;
      case 'orders.history':
          $addCrumb('Đơn hàng');
          break;
      case 'orders.show':
          $addCrumb('Đơn hàng', route('orders.history'));
          $addCrumb('Chi tiết');
          break;
      default:
          if ($routeName && $routeName !== 'home') {
              $label = ucwords(str_replace(['.', '-'], ' ', $routeName));
              $addCrumb($label);
          }
          break;
  }
@endphp

@include('frontend.partials.header')
@include('frontend.partials.breadcrumbs', ['crumbs' => $crumbs])

<main class="cp-main max-w-7xl mx-auto px-4 mt-6">
  <div class="relative">
    @yield('content')
  </div>
</main>

@include('frontend.partials.footer')

@include('components.chat-widget')

<script src="{{ asset('js/cart.js') }}"></script>

@yield('scripts')
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
</body>
</html>
