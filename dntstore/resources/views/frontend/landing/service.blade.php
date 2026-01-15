@extends('frontend.layouts.landing')

@section('title', 'Sửa chữa công nghệ 5.0 – Đặt lịch nhanh | DNT Store')
@section('meta_description', 'Sửa chữa công nghệ 5.0: nhanh – chuẩn – bảo hành. Đặt lịch online, chọn dịch vụ và thời gian, nhận mã xác nhận ngay.')

@section('landing_brand_subtitle', 'Sửa chữa công nghệ 5.0')
@section('landing_primary_cta_label', 'Đặt lịch ngay')
@section('landing_primary_cta_href', '#booking')

@section('landing_nav')
  <a class="text-sm text-ink-muted hover:text-white/90 transition" href="#services">Dịch vụ</a>
  <a class="text-sm text-ink-muted hover:text-white/90 transition" href="#process">Quy trình</a>
  <a class="text-sm text-ink-muted hover:text-white/90 transition" href="#pricing">Bảng giá</a>
  <a class="text-sm text-ink-muted hover:text-white/90 transition" href="#faq">FAQ</a>
@endsection

@section('landing_secondary_cta')
  <a href="#booking" class="lp-btn lp-btn-ghost hidden sm:inline-flex" data-tilt data-glow>
    Chat tư vấn
  </a>
@endsection

@section('landing_footer_links')
  <a href="#pricing" class="hover:text-white/90 transition">Xem bảng giá</a>
  <span class="px-2 text-white/10">•</span>
  <a href="#booking" class="hover:text-white/90 transition">Nhận tư vấn</a>
@endsection

@push('styles')
  @vite(['resources/css/landing-service.css'])
@endpush

@push('scripts')
  @vite(['resources/js/landing-service.js'])
@endpush

@php
  $bookingRouteName = \Illuminate\Support\Facades\Route::has('bookings.store')
    ? 'bookings.store'
    : (\Illuminate\Support\Facades\Route::has('booking.store') ? 'booking.store' : null);

  $bookingAction = $bookingRouteName ? route($bookingRouteName) : '#';
@endphp

@section('content')
  <div class="lp-service">
    <section class="lp-hero relative overflow-hidden">
      <div class="lp-hero-bg" aria-hidden="true">
        <div class="lp-hero-grid"></div>
        <div class="lp-hero-scan"></div>
        <div class="lp-hero-glow"></div>
      </div>

      <div class="mx-auto max-w-7xl px-4 py-16 md:py-24">
        <div class="grid items-center gap-10 lg:grid-cols-12">
          <div class="lg:col-span-7">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-ink-muted">
              <span class="h-1.5 w-1.5 rounded-full bg-neon-cyan"></span>
              Chuẩn hoá quy trình – theo dõi minh bạch – bảo hành rõ ràng
            </div>

            <h1 class="mt-5 font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">
              Sửa chữa công nghệ 5.0 – Nhanh, Chuẩn, Bảo hành
            </h1>

            <p class="mt-4 max-w-2xl text-base leading-relaxed text-ink-muted sm:text-lg">
              Đặt lịch trong 60 giây, chọn dịch vụ phù hợp, nhận mã xác nhận ngay. Kỹ thuật viên giàu kinh nghiệm, linh kiện chuẩn, báo giá minh bạch.
            </p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
              <a href="#booking" class="lp-btn lp-btn-primary inline-flex" data-tilt data-glow>
                Đặt lịch ngay
                <span class="lp-btn-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M5 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </a>

              <a href="#booking" class="lp-btn lp-btn-ghost inline-flex" data-tilt data-glow data-chat>
                Chat tư vấn
              </a>

              <a href="#pricing" class="text-sm font-semibold text-white/80 hover:text-white transition">
                Xem bảng giá
              </a>
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-2">
              <div class="lp-pill">
                <span class="lp-pill-ic" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
                <span>Chẩn đoán nhanh – báo giá trước khi làm</span>
              </div>
              <div class="lp-pill">
                <span class="lp-pill-ic" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M12 3l7 4v6c0 5-3 8-7 9-4-1-7-4-7-9V7l7-4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  </svg>
                </span>
                <span>Bảo hành rõ ràng – theo dõi đơn dễ dàng</span>
              </div>
            </div>
          </div>

          <div class="lg:col-span-5">
            <div class="lp-hero-visual lp-glass p-6 sm:p-7" data-tilt data-glow>
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-white/90">Bảng điều khiển sửa chữa</div>
                <div class="lp-chip">LIVE</div>
              </div>

              <div class="mt-5 grid gap-3">
                <div class="lp-mini-row">
                  <span class="lp-mini-k">Tình trạng</span>
                  <span class="lp-mini-v">Tiếp nhận → Chẩn đoán → Sửa chữa</span>
                </div>
                <div class="lp-mini-row">
                  <span class="lp-mini-k">Cam kết</span>
                  <span class="lp-mini-v">Minh bạch – Không phát sinh bất ngờ</span>
                </div>
                <div class="lp-mini-row">
                  <span class="lp-mini-k">Bảo hành</span>
                  <span class="lp-mini-v">Từ 30–180 ngày tuỳ gói</span>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-2 gap-3">
                <a href="#booking" class="lp-btn lp-btn-primary w-full justify-center" data-tilt data-glow>Nhận tư vấn</a>
                <a href="#pricing" class="lp-btn lp-btn-ghost w-full justify-center" data-tilt data-glow>Xem bảng giá</a>
              </div>

              <div class="mt-6 lp-meter">
                <div class="lp-meter-top">
                  <span class="text-xs text-ink-muted">Độ ưu tiên xử lý</span>
                  <span class="text-xs font-semibold text-white/85">Tự động tối ưu</span>
                </div>
                <div class="lp-meter-bar" aria-hidden="true">
                  <span class="lp-meter-fill"></span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-ink-muted">
                  <span>Tiêu chuẩn</span>
                  <span>Nhanh</span>
                  <span>VIP</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="lp-section border-y border-white/10 bg-white/[0.02]">
      <div class="mx-auto grid max-w-7xl grid-cols-2 gap-4 px-4 py-8 sm:grid-cols-4">
        <div class="lp-trust">
          <div class="lp-trust-n">12.500+</div>
          <div class="lp-trust-l">Khách hàng đã phục vụ</div>
        </div>
        <div class="lp-trust">
          <div class="lp-trust-n">98%</div>
          <div class="lp-trust-l">Tỉ lệ hài lòng</div>
        </div>
        <div class="lp-trust">
          <div class="lp-trust-n">30–90'</div>
          <div class="lp-trust-l">Thời gian xử lý phổ biến</div>
        </div>
        <div class="lp-trust">
          <div class="lp-trust-n">30–180</div>
          <div class="lp-trust-l">Ngày bảo hành</div>
        </div>
      </div>
    </section>

    <section id="services" class="lp-section">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">Dịch vụ nổi bật</h2>
          <p class="lp-sub">Chọn đúng dịch vụ, tối ưu chi phí, tăng độ bền thiết bị.</p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          @php
            $cards = [
              ['k' => 'thay-pin', 't' => 'Thay pin', 'd' => 'Dung lượng chuẩn, ổn định lâu dài', 'ic' => 'battery'],
              ['k' => 'thay-man', 't' => 'Thay màn', 'd' => 'Hiển thị sắc nét, cảm ứng mượt', 'ic' => 'screen'],
              ['k' => 've-sinh', 't' => 'Vệ sinh máy', 'd' => 'Giảm nhiệt, tăng hiệu năng', 'ic' => 'spark'],
              ['k' => 'sua-main', 't' => 'Sửa main', 'd' => 'Chẩn đoán sâu, xử lý tận gốc', 'ic' => 'chip'],
              ['k' => 'nang-cap', 't' => 'Nâng cấp', 'd' => 'Tối ưu RAM/SSD/OS theo nhu cầu', 'ic' => 'upgrade'],
              ['k' => 'phu-kien', 't' => 'Phụ kiện', 'd' => 'Sạc, cáp, tai nghe, ốp chính hãng', 'ic' => 'link'],
            ];
          @endphp

          @foreach($cards as $card)
            <button
              type="button"
              class="lp-card lp-glass text-left"
              data-tilt
              data-glow
              data-service-pick="{{ $card['t'] }}"
              aria-label="Chọn dịch vụ {{ $card['t'] }}"
            >
              <div class="lp-card-ic" aria-hidden="true">
                @if($card['ic'] === 'battery')
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M17 7H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" stroke="currentColor" stroke-width="2"/>
                    <path d="M22 11v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M7.5 12.5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                @elseif($card['ic'] === 'screen')
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 7h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                @elseif($card['ic'] === 'spark')
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M13 2l-2 7H4l6 4-2 9 8-11h6l-7-9z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  </svg>
                @elseif($card['ic'] === 'chip')
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M9 9h6v6H9V9z" stroke="currentColor" stroke-width="2"/>
                    <path d="M4 9h2M4 15h2M18 9h2M18 15h2M9 4v2M15 4v2M9 18v2M15 18v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M7 7h10v10H7V7z" stroke="currentColor" stroke-width="2"/>
                  </svg>
                @elseif($card['ic'] === 'upgrade')
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M12 3l4 4-4 4-4-4 4-4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M8 13l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 21h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                @else
                  <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M10 13a5 5 0 0 1 0-7l.6-.6a5 5 0 0 1 7 7l-1.2 1.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M14 11a5 5 0 0 1 0 7l-.6.6a5 5 0 0 1-7-7l1.2-1.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                @endif
              </div>

              <div class="mt-4">
                <div class="flex items-center justify-between gap-4">
                  <div class="text-base font-extrabold text-white/95">{{ $card['t'] }}</div>
                  <span class="lp-card-tag">Tối ưu</span>
                </div>
                <div class="mt-2 text-sm text-ink-muted">{{ $card['d'] }}</div>
              </div>

              <div class="mt-5 flex items-center justify-between text-xs text-ink-muted">
                <span>Nhấn để chọn</span>
                <span class="text-white/75">→</span>
              </div>
            </button>
          @endforeach
        </div>
      </div>
    </section>

    <section id="process" class="lp-section bg-white/[0.02] border-y border-white/10">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">Quy trình 3 bước</h2>
          <p class="lp-sub">Tối giản thao tác – tối đa tốc độ xử lý.</p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div class="lp-step lp-glass" data-tilt data-glow>
            <div class="lp-step-n">01</div>
            <div class="mt-3 text-lg font-extrabold text-white/95">Chọn dịch vụ</div>
            <div class="mt-2 text-sm text-ink-muted">Chọn đúng nhu cầu để tối ưu chi phí và thời gian.</div>
          </div>
          <div class="lp-step lp-glass" data-tilt data-glow>
            <div class="lp-step-n">02</div>
            <div class="mt-3 text-lg font-extrabold text-white/95">Chọn thời gian</div>
            <div class="mt-2 text-sm text-ink-muted">Đặt lịch linh hoạt, ưu tiên slot phù hợp nhất.</div>
          </div>
          <div class="lp-step lp-glass" data-tilt data-glow>
            <div class="lp-step-n">03</div>
            <div class="mt-3 text-lg font-extrabold text-white/95">Xác nhận & nhận mã</div>
            <div class="mt-2 text-sm text-ink-muted">Gửi thông tin, nhận mã xác nhận và hướng dẫn tiếp nhận.</div>
          </div>
        </div>
      </div>
    </section>

    <section id="pricing" class="lp-section">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">Bảng giá tham khảo</h2>
          <p class="lp-sub">Chọn gói theo tốc độ và mức ưu tiên xử lý.</p>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-3">
          <div class="lp-price lp-glass" data-tilt data-glow>
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-semibold text-white/85">Tiêu chuẩn</div>
                <div class="mt-1 text-3xl font-extrabold text-white/95">Từ 199k</div>
                <div class="mt-1 text-sm text-ink-muted">Phù hợp nhu cầu cơ bản</div>
              </div>
            </div>
            <ul class="mt-5 space-y-2 text-sm text-ink-muted">
              <li class="lp-li">Chẩn đoán & tư vấn</li>
              <li class="lp-li">Linh kiện chuẩn tuỳ hạng mục</li>
              <li class="lp-li">Bảo hành 30–90 ngày</li>
              <li class="lp-li">Nhận máy tại cửa hàng</li>
            </ul>
            <button type="button" class="mt-6 lp-btn lp-btn-ghost w-full justify-center" data-tilt data-glow data-plan="Tiêu chuẩn">Chọn gói</button>
          </div>

          <div class="lp-price lp-glass lp-price-pop" data-tilt data-glow>
            <div class="lp-badge">Phổ biến</div>
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-semibold text-white/85">Nhanh</div>
                <div class="mt-1 text-3xl font-extrabold text-white/95">Từ 349k</div>
                <div class="mt-1 text-sm text-ink-muted">Ưu tiên xử lý trong ngày</div>
              </div>
            </div>
            <ul class="mt-5 space-y-2 text-sm text-ink-muted">
              <li class="lp-li">Ưu tiên slot kỹ thuật viên</li>
              <li class="lp-li">Báo giá trong 15 phút</li>
              <li class="lp-li">Bảo hành 60–120 ngày</li>
              <li class="lp-li">Theo dõi trạng thái đơn</li>
            </ul>
            <button type="button" class="mt-6 lp-btn lp-btn-primary w-full justify-center" data-tilt data-glow data-plan="Nhanh">Chọn gói</button>
          </div>

          <div class="lp-price lp-glass" data-tilt data-glow>
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-semibold text-white/85">VIP</div>
                <div class="mt-1 text-3xl font-extrabold text-white/95">Từ 599k</div>
                <div class="mt-1 text-sm text-ink-muted">Ưu tiên cao nhất, hỗ trợ nhanh</div>
              </div>
            </div>
            <ul class="mt-5 space-y-2 text-sm text-ink-muted">
              <li class="lp-li">Kỹ thuật viên senior</li>
              <li class="lp-li">Ưu tiên linh kiện & test sâu</li>
              <li class="lp-li">Bảo hành 90–180 ngày</li>
              <li class="lp-li">Hỗ trợ nhanh sau sửa chữa</li>
            </ul>
            <button type="button" class="mt-6 lp-btn lp-btn-ghost w-full justify-center" data-tilt data-glow data-plan="VIP">Chọn gói</button>
          </div>
        </div>
      </div>
    </section>

    <section class="lp-section bg-white/[0.02] border-y border-white/10">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">Lý do chọn chúng tôi</h2>
          <p class="lp-sub">Tập trung chất lượng – tối ưu trải nghiệm – giảm rủi ro.</p>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          @php
            $reasons = [
              'Linh kiện chuẩn, nguồn gốc rõ ràng',
              'Kỹ thuật viên giàu kinh nghiệm',
              'Bảo hành rõ ràng theo hạng mục',
              'Minh bạch báo giá, hạn chế phát sinh',
              'Theo dõi đơn theo trạng thái',
              'Hỗ trợ nhanh, tư vấn thân thiện',
            ];
          @endphp
          @foreach($reasons as $r)
            <div class="lp-reason lp-glass" data-tilt data-glow>
              <span class="lp-check" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                  <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <span class="text-sm font-semibold text-white/90">{{ $r }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="lp-section">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">Khách hàng nói gì</h2>
          <p class="lp-sub">Một vài phản hồi gần đây (dữ liệu demo).</p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          @php
            $feedbacks = [
              ['name' => 'Minh Khang', 'role' => 'iPhone 13', 'msg' => 'Thay pin xong máy ổn định hẳn. Báo giá rõ ràng, làm nhanh và có bảo hành.', 'rate' => 5],
              ['name' => 'Ngọc Anh', 'role' => 'Laptop văn phòng', 'msg' => 'Vệ sinh + nâng cấp SSD rất mượt. Kỹ thuật tư vấn đúng trọng tâm, không chèo kéo.', 'rate' => 5],
              ['name' => 'Tuấn Phát', 'role' => 'Samsung', 'msg' => 'Màn hình thay đẹp, cảm ứng nhạy. Có mã theo dõi nên yên tâm.', 'rate' => 4],
            ];
          @endphp
          @foreach($feedbacks as $fb)
            <div class="lp-testimonial lp-glass" data-tilt data-glow>
              <div class="flex items-center justify-between gap-4">
                <div>
                  <div class="text-sm font-extrabold text-white/95">{{ $fb['name'] }}</div>
                  <div class="text-xs text-ink-muted">{{ $fb['role'] }}</div>
                </div>
                <div class="flex items-center gap-1 text-neon-cyan" aria-label="Đánh giá {{ $fb['rate'] }} trên 5">
                  @for($i=0;$i<5;$i++)
                    <svg viewBox="0 0 24 24" fill="{{ $i < $fb['rate'] ? 'currentColor' : 'none' }}" class="h-4 w-4">
                      <path d="M12 2l3 7h7l-5.6 4.1L18.5 21 12 16.9 5.5 21l2.1-7.9L2 9h7l3-7z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                  @endfor
                </div>
              </div>
              <p class="mt-4 text-sm leading-relaxed text-ink-muted">“{{ $fb['msg'] }}”</p>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section id="faq" class="lp-section bg-white/[0.02] border-y border-white/10">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-heading">
          <h2 class="lp-h2">FAQ</h2>
          <p class="lp-sub">Giải đáp nhanh để bạn tự tin đặt lịch.</p>
        </div>

        @php
          $faqs = [
            ['q' => 'Tôi đặt lịch xong thì làm gì tiếp?', 'a' => 'Bạn sẽ nhận hướng dẫn tiếp nhận và mã xác nhận. Mang thiết bị đến cửa hàng đúng giờ, kỹ thuật viên sẽ kiểm tra và báo giá trước khi sửa.'],
            ['q' => 'Có phát sinh chi phí ngoài báo giá không?', 'a' => 'Chúng tôi ưu tiên minh bạch: chỉ thực hiện khi bạn đồng ý báo giá. Nếu có phát sinh do lỗi khác, kỹ thuật sẽ thông báo trước.'],
            ['q' => 'Thời gian sửa chữa thường mất bao lâu?', 'a' => 'Tùy hạng mục và gói ưu tiên. Các lỗi phổ biến có thể xử lý 30–90 phút. Gói Nhanh/VIP được ưu tiên slot và linh kiện.'],
            ['q' => 'Bảo hành áp dụng như thế nào?', 'a' => 'Bảo hành theo hạng mục và loại linh kiện, từ 30–180 ngày. Bạn giữ mã xác nhận/hoá đơn để được hỗ trợ nhanh.'],
            ['q' => 'Tôi muốn theo dõi tình trạng đơn sửa chữa?', 'a' => 'Sau khi tiếp nhận, bạn có thể theo dõi qua trạng thái đơn (tư vấn viên cung cấp link/mã).'],
            ['q' => 'Thiết bị bị vào nước có xử lý được không?', 'a' => 'Có thể, nhưng cần kiểm tra sớm để giảm rủi ro oxy hoá. Bạn nên đặt lịch và mang thiết bị đến càng sớm càng tốt.'],
          ];
        @endphp

        <div class="mt-8 grid gap-3 md:grid-cols-2">
          @foreach($faqs as $idx => $f)
            <div class="lp-faq lp-glass" data-accordion>
              <button
                type="button"
                class="lp-faq-q"
                aria-expanded="false"
                aria-controls="faq-{{ $idx }}"
                data-accordion-btn
              >
                <span class="text-sm font-extrabold text-white/90">{{ $f['q'] }}</span>
                <span class="lp-faq-ic" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
              </button>
              <div id="faq-{{ $idx }}" class="lp-faq-a" hidden>
                <p class="pt-3 text-sm leading-relaxed text-ink-muted">{{ $f['a'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section id="booking" class="lp-section">
      <div class="mx-auto max-w-7xl px-4 py-14 md:py-18">
        <div class="lp-cta lp-glass overflow-hidden">
          <div class="lp-cta-bg" aria-hidden="true"></div>

          <div class="relative grid gap-10 p-6 sm:p-8 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-5">
              <h2 class="font-display text-3xl font-extrabold text-white/95">Đặt lịch nhanh</h2>
              <p class="mt-3 text-sm leading-relaxed text-ink-muted">
                Điền thông tin cơ bản, chúng tôi sẽ xác nhận sớm nhất. Nếu cần gấp, chọn gói Nhanh/VIP để ưu tiên xử lý.
              </p>

              <div class="mt-6 grid gap-3">
                <div class="lp-mini-row">
                  <span class="lp-mini-k">Cam kết</span>
                  <span class="lp-mini-v">Không treo form – có trạng thái rõ ràng</span>
                </div>
                <div class="lp-mini-row">
                  <span class="lp-mini-k">CTA</span>
                  <span class="lp-mini-v">Đặt lịch ngay / Nhận tư vấn / Xem bảng giá</span>
                </div>
              </div>

              <div class="mt-6 flex flex-wrap gap-2">
                <a href="#pricing" class="lp-btn lp-btn-ghost" data-tilt data-glow>Xem bảng giá</a>
                <a href="#main" class="lp-btn lp-btn-ghost" data-tilt data-glow>Quay lại đầu trang</a>
              </div>
            </div>

            <div class="lg:col-span-7">
              <form
                class="lp-form"
                method="POST"
                action="{{ $bookingAction }}"
                novalidate
                data-lp-form
              >
                @csrf
                <input type="hidden" name="source" value="lp_services">
                <input type="hidden" name="plan" value="{{ old('plan') }}" data-plan-input>

                @if(session('status') || session('success') || session('booking_success'))
                  <div class="lp-alert lp-alert-ok" role="status">
                    <div class="font-extrabold text-white/90">Đã nhận thông tin.</div>
                    <div class="mt-1 text-sm text-ink-muted">
                      {{ session('status') ?? session('success') ?? session('booking_success') }}
                      <span class="text-white/70">Nếu bạn chưa nhận được phản hồi, vui lòng thử lại sau ít phút.</span>
                    </div>
                  </div>
                @endif

                @if($errors->any())
                  <div class="lp-alert lp-alert-err" role="alert">
                    <div class="font-extrabold text-white/90">Vui lòng kiểm tra lại thông tin.</div>
                    <ul class="mt-2 list-disc pl-5 text-sm text-ink-muted">
                      @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label class="lp-label" for="lp_name">Họ tên</label>
                    <input id="lp_name" name="name" type="text" autocomplete="name" value="{{ old('name') }}" class="lp-input" placeholder="Ví dụ: Nguyễn Văn A" required>
                    @error('name')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div>
                    <label class="lp-label" for="lp_phone">SĐT</label>
                    <input id="lp_phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" class="lp-input" placeholder="Ví dụ: 090xxxxxxx" required>
                    @error('phone')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div>
                    <label class="lp-label" for="lp_device">Thiết bị</label>
                    <input id="lp_device" name="device" type="text" value="{{ old('device') }}" class="lp-input" placeholder="Ví dụ: iPhone 13 / Laptop Dell" required>
                    @error('device')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div>
                    <label class="lp-label" for="lp_service">Chọn dịch vụ</label>
                    <select id="lp_service" name="service" class="lp-input" required>
                      @php $sv = old('service'); @endphp
                      <option value="" {{ $sv ? '' : 'selected' }} disabled>Chọn dịch vụ…</option>
                      <option value="Thay pin" {{ $sv === 'Thay pin' ? 'selected' : '' }}>Thay pin</option>
                      <option value="Thay màn" {{ $sv === 'Thay màn' ? 'selected' : '' }}>Thay màn</option>
                      <option value="Vệ sinh máy" {{ $sv === 'Vệ sinh máy' ? 'selected' : '' }}>Vệ sinh máy</option>
                      <option value="Sửa main" {{ $sv === 'Sửa main' ? 'selected' : '' }}>Sửa main</option>
                      <option value="Nâng cấp" {{ $sv === 'Nâng cấp' ? 'selected' : '' }}>Nâng cấp</option>
                      <option value="Phụ kiện" {{ $sv === 'Phụ kiện' ? 'selected' : '' }}>Phụ kiện</option>
                      <option value="Tư vấn" {{ $sv === 'Tư vấn' ? 'selected' : '' }}>Tư vấn</option>
                    </select>
                    @error('service')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div class="sm:col-span-2">
                    <label class="lp-label" for="lp_issue">Vấn đề</label>
                    <textarea id="lp_issue" name="issue" rows="3" class="lp-input" placeholder="Mô tả ngắn tình trạng (máy nóng, pin tụt nhanh, liệt cảm ứng…)" required>{{ old('issue') }}</textarea>
                    @error('issue')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div>
                    <label class="lp-label" for="lp_datetime">Ngày/giờ</label>
                    <input id="lp_datetime" name="datetime" type="datetime-local" value="{{ old('datetime') }}" class="lp-input" required>
                    @error('datetime')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>

                  <div>
                    <label class="lp-label" for="lp_note">Ghi chú</label>
                    <input id="lp_note" name="note" type="text" value="{{ old('note') }}" class="lp-input" placeholder="Ví dụ: cần gấp / có thể đến sau 18:00">
                    @error('note')<div class="lp-err">{{ $message }}</div>@enderror
                  </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div class="text-xs text-ink-muted">
                    Bằng việc gửi form, bạn đồng ý để chúng tôi liên hệ xác nhận lịch.
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="submit" class="lp-btn lp-btn-primary" data-tilt data-glow data-submit>
                      Đặt lịch ngay
                    </button>
                    <a href="#pricing" class="lp-btn lp-btn-ghost" data-tilt data-glow>
                      Xem bảng giá
                    </a>
                  </div>
                </div>

                @if(!$bookingRouteName)
                  <div class="mt-4 text-xs text-ink-muted">
                    Chưa cấu hình route <span class="text-white/80">bookings.store</span> hoặc <span class="text-white/80">booking.store</span>. Form đang ở chế độ demo.
                  </div>
                @endif
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="lp-sticky-cta" data-sticky-cta hidden>
      <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3">
        <div class="min-w-0">
          <div class="truncate text-sm font-extrabold text-white/90">Sửa chữa 5.0 – Đặt lịch trong 60 giây</div>
          <div class="truncate text-xs text-ink-muted">Nhanh • Chuẩn • Bảo hành</div>
        </div>
        <div class="flex items-center gap-2">
          <a href="#booking" class="lp-btn lp-btn-ghost" data-tilt data-glow>Nhận tư vấn</a>
          <a href="#booking" class="lp-btn lp-btn-primary" data-tilt data-glow>Đặt lịch ngay</a>
        </div>
      </div>
    </div>
  </div>
@endsection
