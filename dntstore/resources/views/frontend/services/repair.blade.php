@extends('frontend.layouts.app')

@section('title', 'Dịch vụ sửa chữa | DNT Store')
@section('page', 'services')

@section('content')
@php
  $servicesList = collect($services ?? [])
    ->filter(function ($svc) {
      if (!is_object($svc)) return false;
      if (!isset($svc->status)) return true;
      $status = is_string($svc->status) ? mb_strtolower($svc->status) : $svc->status;
      return $status === 1 || $status === true || $status === 'active' || $status === 'on' || $status === 'published';
    })
    ->values();

  $formatPrice = function ($value) {
    $n = is_numeric($value) ? (float)$value : 0;
    if ($n <= 0) return null;
    return number_format($n, 0, ',', '.') . '₫';
  };
@endphp

<div class="svc-wrap">
  <div class="svc-hero" data-reveal>
    <div class="svc-kicker">
      <span>DNT STORE</span>
      <span class="svc-kicker-divider"></span>
      <span>REPAIR LAB</span>
    </div>
    <h1 class="svc-title">Sửa nhanh • Giá chuẩn • Bảo hành rõ ràng</h1>
    <div class="svc-hr"></div>
    <div class="mt-5 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
      <a href="{{ route('booking.create') }}" class="svc-btn">
        Đặt lịch
        <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m13 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <a href="{{ route('contact') }}" class="svc-btn">
        Tư vấn nhanh
        <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m13 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <a href="#services" class="svc-btn">
        Xem dịch vụ
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m6 11 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
    <div class="mt-6 flex flex-wrap justify-center gap-2 text-xs">
      <span class="svc-btn" style="pointer-events:none">Miễn phí kiểm tra</span>
      <span class="svc-btn" style="pointer-events:none">Báo giá trước khi làm</span>
      <span class="svc-btn" style="pointer-events:none">Linh kiện chuẩn</span>
      <span class="svc-btn" style="pointer-events:none">Bảo hành rõ ràng</span>
    </div>
  </div>

  <section class="mt-6" id="trust" data-reveal data-reveal-delay="40">
    <div class="grid gap-3 sm:grid-cols-3">
      <div class="svc-trust-card">
        <div class="svc-trust-k">CHUẨN</div>
        <div class="svc-trust-v">Báo giá minh bạch</div>
      </div>
      <div class="svc-trust-card">
        <div class="svc-trust-k">NHANH</div>
        <div class="svc-trust-v">Kiểm tra trong 15’</div>
      </div>
      <div class="svc-trust-card">
        <div class="svc-trust-k">AN TÂM</div>
        <div class="svc-trust-v">Bảo hành rõ ràng</div>
      </div>
    </div>
  </section>

  <section class="mt-8" id="services" data-reveal data-reveal-delay="60">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h2 class="svc-h2">Danh sách dịch vụ</h2>
        <p class="svc-sub mt-2 max-w-3xl">Chọn hạng mục phù hợp hoặc đặt lịch để kỹ thuật viên chẩn đoán.</p>
      </div>
      <a href="{{ route('booking.create') }}" class="hidden svc-btn sm:inline-flex">Đặt lịch</a>
    </header>

    <div class="mt-5 svc-grid">
      @forelse($servicesList as $svc)
        @php
          $priceText = $formatPrice($svc->price ?? null);
          $desc = (string)($svc->description ?? '');
        @endphp
        <article class="svc-card">
          <div class="hud-bars"></div>
          <div class="hud-slits"></div>
          <div class="scanline"></div>
          <div class="particles"></div>
          <div class="shine"></div>
          <div class="svc-notch"></div>
          <div class="svc-inner">
            <div class="flex items-start justify-between gap-3">
              <div class="svc-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2v4M12 18v4M2 12h4M18 12h4M5 5l3 3M16 16l3 3M19 5l-3 3M8 16l-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              @if($priceText)
                <div class="text-right">
                  <div class="svc-sub text-xs font-semibold">Từ</div>
                  <div class="svc-price">{{ $priceText }}</div>
                </div>
              @endif
            </div>
            <div class="svc-name">{{ $svc->name ?? 'Dịch vụ sửa chữa' }}</div>
            <p class="svc-desc">{{ \Illuminate\Support\Str::limit($desc, 160) }}</p>
            <div class="svc-actions">
              <a href="{{ route('booking.create') }}" class="svc-btn">
                Đặt lịch
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m13 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
            </div>
          </div>
        </article>
      @empty
        <div class="svc-trust-card sm:col-span-2 lg:col-span-3">
          <div class="svc-trust-v">Hiện chưa có dịch vụ.</div>
        </div>
      @endforelse
    </div>
    <div class="mt-4 sm:hidden">
      <a href="{{ route('booking.create') }}" class="svc-btn" style="width:100%;justify-content:center">Đặt lịch</a>
    </div>
  </section>

  <section class="mt-10" id="process" data-reveal data-reveal-delay="90">
    <header>
      <h2 class="svc-h2">Quy trình sửa chữa</h2>
      <p class="svc-sub mt-2 max-w-3xl">Rõ ràng từng bước: minh bạch, kiểm soát, an tâm bảo hành.</p>
    </header>
    @php
      $steps = [
        ['k' => '01', 't' => 'Tiếp nhận', 'd' => 'Ghi nhận tình trạng, kiểm tra sơ bộ, xác nhận nhu cầu.'],
        ['k' => '02', 't' => 'Chẩn đoán', 'd' => 'Khoanh vùng lỗi, kiểm tra linh kiện, đề xuất phương án.'],
        ['k' => '03', 't' => 'Báo giá', 'd' => 'Báo giá minh bạch trước khi sửa, không phát sinh.'],
        ['k' => '04', 't' => 'Sửa chữa', 'd' => 'Thực hiện theo quy chuẩn, ưu tiên linh kiện đạt chuẩn.'],
        ['k' => '05', 't' => 'Kiểm tra', 'd' => 'Test lại toàn bộ chức năng, vệ sinh, tối ưu trải nghiệm.'],
        ['k' => '06', 't' => 'Bàn giao', 'd' => 'Hướng dẫn sử dụng, kích hoạt bảo hành, lưu hồ sơ.'],
      ];
    @endphp
    <ol class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($steps as $s)
        <li class="relative overflow-hidden rounded-3xl neo-surface neo-border p-6 transition hover:-translate-y-1">
          <div class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_18%_18%,rgba(34,211,238,.10),transparent_40%),radial-gradient(circle_at_80%_65%,rgba(37,99,235,.10),transparent_45%)]"></div>
          <div class="relative">
            <div class="flex items-start justify-between gap-3">
              <div class="svc-step-kicker">STEP</div>
              <div class="svc-step-num">{{ $s['k'] }}</div>
            </div>
            <h3 class="svc-step-title mt-3">{{ $s['t'] }}</h3>
            <p class="svc-sub mt-2">{{ $s['d'] }}</p>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  <section class="mt-10" id="pricing" data-reveal data-reveal-delay="110">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h2 class="svc-h2">Gói ưu đãi</h2>
        <p class="svc-sub mt-2 max-w-3xl">Tiết kiệm chi phí cho các nhu cầu phổ biến.</p>
      </div>
    </header>
    @php
      $plans = [
        ['name'=>'Cơ bản','price'=>'199.000₫','features'=>['Vệ sinh', 'Kiểm tra nhanh', 'Báo giá trước']],
        ['name'=>'Nâng cao','price'=>'399.000₫','features'=>['Chẩn đoán sâu', 'Tối ưu hiệu năng', 'Ưu tiên lịch']],
        ['name'=>'Premium','price'=>'799.000₫','features'=>['Bảo hành mở rộng', 'Theo dõi hồ sơ', 'Hỗ trợ từ xa']],
      ];
    @endphp
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($plans as $p)
        <article class="relative overflow-hidden rounded-3xl neo-surface neo-border p-6">
          <div class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_18%_18%,rgba(168,85,247,.10),transparent_40%),radial-gradient(circle_at_78%_62%,rgba(34,211,238,.10),transparent_45%)]"></div>
          <div class="relative">
            <div class="svc-card-title">{{ $p['name'] }}</div>
            <div class="svc-price mt-1">{{ $p['price'] }}</div>
            <ul class="mt-3 space-y-2 text-sm">
              @foreach($p['features'] as $f)
                <li class="svc-sub inline-flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-neon-cyan"></span>{{ $f }}</li>
              @endforeach
            </ul>
            <div class="mt-4">
              <a href="{{ route('booking.create') }}" class="svc-btn">Chọn gói</a>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="mt-10" id="testimonials" data-reveal data-reveal-delay="130">
    <header>
      <h2 class="svc-h2">Khách hàng nói gì</h2>
    </header>
    @php
      $testimonials = [
        ['n'=>'Anh Minh','t'=>'Sửa nhanh, báo giá rõ ràng, rất hài lòng.'],
        ['n'=>'Chị Lan','t'=>'Kỹ thuật giải thích cặn kẽ, máy chạy mượt sau sửa.'],
        ['n'=>'Bạn Huy','t'=>'Đặt lịch tiện lợi, hỗ trợ chat rất hữu ích.'],
      ];
    @endphp
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($testimonials as $c)
        <article class="relative overflow-hidden rounded-3xl neo-surface neo-border p-6">
          <div class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_22%_18%,rgba(34,211,238,.10),transparent_45%),radial-gradient(circle_at_82%_70%,rgba(168,85,247,.10),transparent_45%)]"></div>
          <div class="relative">
            <div class="svc-card-title text-sm">{{ $c['n'] }}</div>
            <p class="svc-sub mt-2 text-sm leading-relaxed">“{{ $c['t'] }}”</p>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="mt-10" id="faqs" data-reveal data-reveal-delay="140">
    <header>
      <h2 class="svc-h2">FAQ</h2>
    </header>
    @php
      $faqs = [
        ['q'=>'Thời gian kiểm tra?', 'a'=>'Trong khoảng 15 phút cho lỗi phổ biến.'],
        ['q'=>'Báo giá có phát sinh?', 'a'=>'Không, báo giá trước và xác nhận trước khi làm.'],
        ['q'=>'Bảo hành thế nào?', 'a'=>'Theo hạng mục từ 30 đến 180 ngày.'],
      ];
    @endphp
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($faqs as $f)
        <article class="relative overflow-hidden rounded-3xl neo-surface neo-border p-6">
          <div class="absolute inset-0 pointer-events-none opacity-60 bg-[radial-gradient(circle_at_18%_18%,rgba(34,211,238,.10),transparent_40%),radial-gradient(circle_at_78%_62%,rgba(34,211,238,.10),transparent_45%)]"></div>
          <div class="relative">
            <div class="svc-card-title text-sm">{{ $f['q'] }}</div>
            <p class="svc-sub mt-2 text-sm leading-relaxed">{{ $f['a'] }}</p>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="mt-10" id="cta" data-reveal data-reveal-delay="150">
    <div class="relative overflow-hidden rounded-3xl neo-surface neo-border p-8 sm:p-10">
      <div class="absolute inset-0 pointer-events-none opacity-70 bg-[radial-gradient(circle_at_22%_18%,rgba(34,211,238,.14),transparent_45%),radial-gradient(circle_at_82%_70%,rgba(37,99,235,.14),transparent_45%),radial-gradient(circle_at_55%_45%,rgba(168,85,247,.10),transparent_50%)]"></div>
      <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <header class="max-w-2xl">
          <h2 class="svc-h2 sm:text-3xl">Sẵn sàng tối ưu thiết bị của bạn?</h2>
          <p class="svc-sub mt-2 text-sm leading-relaxed sm:text-base">Đặt lịch để được ưu tiên slot và nhận chẩn đoán nhanh, hoặc mở chat để mô tả lỗi.</p>
        </header>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <a href="{{ route('booking.create') }}" class="svc-btn">Đặt lịch ngay</a>
          <a href="{{ route('contact') }}" class="svc-btn">Chat AI tư vấn</a>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.svc-card').forEach(function(card){
    card.addEventListener('mousemove', function(e){
      var rect = card.getBoundingClientRect();
      var x = ((e.clientX - rect.left) / rect.width) * 100 + '%';
      var y = ((e.clientY - rect.top) / rect.height) * 100 + '%';
      card.style.setProperty('--mx', x);
      card.style.setProperty('--my', y);
    });
    card.addEventListener('mouseleave', function(){
      card.style.setProperty('--mx', '50%');
      card.style.setProperty('--my', '40%');
    });
  });
</script>
@endpush
