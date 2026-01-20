{{-- booking.blade.php: FIX VITE MANIFEST ERROR --}}
{{-- Bạn KHÔNG có entry resources/css/booking.css trong Vite manifest vì CSS của bạn đang nằm trong app.css (có @import "./pages/booking.css"). --}}
{{-- Vì vậy: XÓA booking.css khỏi @vite. Chỉ load app.css/app.js + booking.js (nếu cần). --}}

@extends('frontend.layouts.app')

@section('title', 'Đặt Lịch Sửa Chữa - DNT Store')

@section('styles')
  <style>
    html[data-theme="light"] .booking-page{
      --bk-text: rgba(18,20,28,.92);
      --bk-soft: rgba(18,20,28,.82);
      --bk-muted: rgba(18,20,28,.62);
      --bk-faint: rgba(18,20,28,.55);
      color: var(--bk-text);
    }

    html[data-theme="light"] .booking-badge,
    html[data-theme="light"] .booking-btn,
    html[data-theme="light"] .booking-qa-q{
      color: var(--bk-text) !important;
    }

    html[data-theme="light"] .booking-label,
    html[data-theme="light"] .booking-file,
    html[data-theme="light"] .booking-radio,
    html[data-theme="light"] .booking-alert-list{
      color: var(--bk-soft) !important;
    }

    html[data-theme="light"] .booking-trust-label,
    html[data-theme="light"] .booking-step-desc,
    html[data-theme="light"] .booking-mini-note,
    html[data-theme="light"] .booking-section-sub,
    html[data-theme="light"] .booking-help,
    html[data-theme="light"] .booking-summary-sub,
    html[data-theme="light"] .booking-summary-k,
    html[data-theme="light"] .booking-summary-note,
    html[data-theme="light"] .booking-qa-a,
    html[data-theme="light"] .booking-final-sub{
      color: var(--bk-muted) !important;
    }

    html[data-theme="light"] .booking-legal{
      color: var(--bk-faint) !important;
    }

    html[data-theme="light"] .booking-input,
    html[data-theme="light"] .booking-textarea{
      color: var(--bk-text) !important;
    }
  </style>
@endsection

@section('content')
<div class="booking-page" data-page="booking">

  <section class="booking-hero">
    <div class="booking-container">
      <div class="booking-hero-grid">
        <div class="booking-hero-left">
          <div class="booking-badge">DNT Store</div>
          <h1 class="booking-hero-title">Đặt Lịch Sửa Chữa</h1>
          <p class="booking-hero-sub">
            Nhanh - Minh bạch - Bảo hành. Nhận máy tận nơi hoặc mang trực tiếp tới cửa hàng.
          </p>

          <div class="booking-hero-actions">
            <a class="booking-btn booking-btn-primary" href="#bookingForm">Đặt lịch ngay</a>
            <a class="booking-btn booking-btn-ghost" href="#bookingFaq">Xem FAQ</a>
          </div>

          <div class="booking-trustbar">
            <div class="booking-trust-item">
              <div class="booking-trust-kpi">12K+</div>
              <div class="booking-trust-label">Đơn đã xử lý</div>
            </div>
            <div class="booking-trust-item">
              <div class="booking-trust-kpi">45'</div>
              <div class="booking-trust-label">Chuẩn đoán nhanh</div>
            </div>
            <div class="booking-trust-item">
              <div class="booking-trust-kpi">4.9/5</div>
              <div class="booking-trust-label">Hài lòng</div>
            </div>
          </div>
        </div>

        <div class="booking-hero-right">
          <div class="booking-glowcard">
            <div class="booking-glowcard-title">Quy trình 4 bước</div>
            <ol class="booking-steps">
              <li class="booking-step">
                <span class="booking-step-dot"></span>
                <div>
                  <div class="booking-step-title">Tiếp nhận</div>
                  <div class="booking-step-desc">Ghi nhận thông tin & hẹn lịch</div>
                </div>
              </li>
              <li class="booking-step">
                <span class="booking-step-dot"></span>
                <div>
                  <div class="booking-step-title">Kiểm tra</div>
                  <div class="booking-step-desc">Chẩn đoán lỗi & đề xuất phương án</div>
                </div>
              </li>
              <li class="booking-step">
                <span class="booking-step-dot"></span>
                <div>
                  <div class="booking-step-title">Báo giá</div>
                  <div class="booking-step-desc">Minh bạch chi phí, chỉ sửa khi bạn đồng ý</div>
                </div>
              </li>
              <li class="booking-step">
                <span class="booking-step-dot"></span>
                <div>
                  <div class="booking-step-title">Hoàn tất</div>
                  <div class="booking-step-desc">Test kỹ, bàn giao & bảo hành</div>
                </div>
              </li>
            </ol>

            <div class="booking-mini-note">
              Mẹo: Mô tả lỗi càng rõ, xử lý càng nhanh.
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="booking-scanline" aria-hidden="true"></div>
  </section>

  <section class="booking-main" id="bookingForm">
    <div class="booking-container">
      <div class="booking-section-head">
        <h2 class="booking-section-title">Thông tin đặt lịch</h2>
        <p class="booking-section-sub">Vui lòng điền đầy đủ thông tin để chúng tôi liên hệ và xử lý nhanh nhất.</p>
      </div>

      @if(session('success'))
        <div class="booking-alert booking-alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="booking-alert booking-alert-error">
          <div class="booking-alert-title">Có lỗi xảy ra:</div>
          <ul class="booking-alert-list">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="booking-grid">
        <div class="booking-card">
          <form id="booking-form"
                class="booking-form"
                method="POST"
                action="{{ route('booking.store') }}"
                enctype="multipart/form-data">
            @csrf

            <div class="booking-form-grid">
              <div class="booking-field">
                <label class="booking-label" for="name">Họ tên</label>
                <input class="booking-input"
                       id="name"
                       name="name"
                       type="text"
                       autocomplete="name"
                       value="{{ old('name') }}"
                       placeholder="Nguyễn Văn A"
                       required>
                @error('name') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field">
                <label class="booking-label" for="phone">Số điện thoại</label>
                <input class="booking-input"
                       id="phone"
                       name="phone"
                       type="tel"
                       inputmode="tel"
                       autocomplete="tel"
                       value="{{ old('phone') }}"
                       placeholder="09xx xxx xxx"
                       required>
                @error('phone') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full">
                <label class="booking-label" for="device">Thiết bị</label>
                <input class="booking-input"
                       id="device"
                       name="device"
                       type="text"
                       value="{{ old('device') }}"
                       placeholder="iPhone 13 / AirPods Pro / Laptop..."
                       required>
                @error('device') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full">
                <label class="booking-label" for="issue_description">Mô tả lỗi</label>
                <textarea class="booking-textarea"
                          id="issue_description"
                          name="issue_description"
                          rows="4"
                          placeholder="Mô tả hiện tượng, thời điểm xảy ra, tình trạng..."
                          required>{{ old('issue_description') }}</textarea>
                @error('issue_description') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field">
                <label class="booking-label" for="appointment_at">Ngày giờ hẹn</label>
                <input class="booking-input"
                       id="appointment_at"
                       name="appointment_at"
                       type="datetime-local"
                       value="{{ old('appointment_at') }}">
                @error('appointment_at') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field">
                <label class="booking-label">Cách nhận</label>

                <div class="booking-radio-group">
                  <label class="booking-radio">
                    <input type="radio" name="receive_method" value="store" {{ old('receive_method','store') === 'store' ? 'checked' : '' }}>
                    <span>Mang tới cửa hàng</span>
                  </label>

                  {{-- <label class="booking-radio">
                    <input type="radio" name="receive_method" value="pickup" {{ old('receive_method') === 'pickup' ? 'checked' : '' }}>
                    <span>Nhận tận nơi</span>
                  </label> --}}

                  <label class="booking-radio">
                    <input type="radio" name="receive_method" value="ship" {{ old('receive_method') === 'ship' ? 'checked' : '' }}>
                    <span>Gửi ship</span>
                  </label>
                </div>

                @error('receive_method') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full booking-ship-only" data-ship="ship">
                <label class="booking-label" for="shipping_provider">Đơn vị vận chuyển</label>
                <input class="booking-input"
                       id="shipping_provider"
                       name="shipping_provider"
                       type="text"
                       value="{{ old('shipping_provider') }}"
                       placeholder="SPX / GHN / GHTK...">
                @error('shipping_provider') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full booking-ship-only" data-ship="ship">
                <label class="booking-label" for="pickup_address">Địa chỉ nhận</label>
                <input class="booking-input"
                       id="pickup_address"
                       name="pickup_address"
                       type="text"
                       value="{{ old('pickup_address') }}"
                       placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành...">
                @error('pickup_address') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full booking-ship-only" data-ship="ship">
                <label class="booking-label" for="shipping_code">Mã vận đơn</label>
                <input class="booking-input"
                       id="shipping_code"
                       name="shipping_code"
                       type="text"
                       value="{{ old('shipping_code') }}"
                       placeholder="VD: SPX123456789...">
                @error('shipping_code') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full">
                <label class="booking-label" for="notes">Ghi chú</label>
                <textarea class="booking-textarea"
                          id="notes"
                          name="notes"
                          rows="3"
                          placeholder="Ghi chú thêm (tùy chọn)">{{ old('notes') }}</textarea>
                @error('notes') <div class="booking-error">{{ $message }}</div> @enderror
              </div>

              <div class="booking-field booking-field-full">
                <label class="booking-label" for="photos">Ảnh lỗi (có thể chọn nhiều ảnh)</label>
                <input class="booking-file"
                       id="photos"
                       name="photos[]"
                       type="file"
                       accept="image/*"
                       multiple>
                <div class="booking-help">Hỗ trợ JPG/PNG/WebP. Chọn tối đa theo cấu hình server.</div>
                @error('photos') <div class="booking-error">{{ $message }}</div> @enderror
                @error('photos.*') <div class="booking-error">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="booking-form-actions">
              <button class="booking-btn booking-btn-primary booking-submit" type="submit">
                Gửi yêu cầu đặt lịch
              </button>
              <div class="booking-legal">
                Bằng cách gửi, bạn đồng ý để DNT liên hệ xác nhận lịch hẹn.
              </div>
            </div>
          </form>
        </div>

        <aside class="booking-card booking-summary">
          <div class="booking-summary-head">
            <div class="booking-summary-title">Tóm tắt</div>
            <div class="booking-summary-sub">Kiểm tra nhanh trước khi gửi</div>
          </div>

          <div class="booking-summary-list">
            <div class="booking-summary-row">
              <div class="booking-summary-k">Họ tên</div>
              <div class="booking-summary-v" data-summary="name">—</div>
            </div>
            <div class="booking-summary-row">
              <div class="booking-summary-k">SĐT</div>
              <div class="booking-summary-v" data-summary="phone">—</div>
            </div>
            <div class="booking-summary-row">
              <div class="booking-summary-k">Thiết bị</div>
              <div class="booking-summary-v" data-summary="device">—</div>
            </div>
            <div class="booking-summary-row">
              <div class="booking-summary-k">Hẹn</div>
              <div class="booking-summary-v" data-summary="appointment_at">—</div>
            </div>
            <div class="booking-summary-row">
              <div class="booking-summary-k">Nhận</div>
              <div class="booking-summary-v" data-summary="receive_method">—</div>
            </div>
            <div class="booking-summary-row booking-summary-ship" data-summary-wrap="shipping_provider">
              <div class="booking-summary-k">Vận chuyển</div>
              <div class="booking-summary-v" data-summary="shipping_provider">—</div>
            </div>
            <div class="booking-summary-row booking-summary-ship" data-summary-wrap="pickup_address">
              <div class="booking-summary-k">Địa chỉ</div>
              <div class="booking-summary-v" data-summary="pickup_address">—</div>
            </div>
            <div class="booking-summary-row booking-summary-ship" data-summary-wrap="shipping_code">
              <div class="booking-summary-k">Mã vận đơn</div>
              <div class="booking-summary-v" data-summary="shipping_code">—</div>
            </div>
          </div>

          <div class="booking-summary-foot">
            <div class="booking-summary-note">
              Phí dự kiến sẽ được báo sau khi kiểm tra (placeholder).
            </div>
            <a class="booking-btn booking-btn-ghost booking-summary-cta" href="#bookingForm">Chỉnh sửa</a>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="booking-faq" id="bookingFaq">
    <div class="booking-container">
      <div class="booking-section-head">
        <h2 class="booking-section-title">Câu hỏi thường gặp</h2>
        <p class="booking-section-sub">Một vài thông tin giúp bạn đặt lịch nhanh hơn.</p>
      </div>

      <div class="booking-accordion" data-accordion>
        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Tôi có cần đặt lịch trước không?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Nên đặt lịch để được ưu tiên tiếp nhận và chuẩn bị kỹ thuật viên/phụ tùng phù hợp.
          </div>
        </div>

        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Nếu tôi chọn “Nhận tận nơi” thì cần gì?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Bạn chỉ cần điền địa chỉ nhận. Chúng tôi sẽ liên hệ xác nhận thời gian và phí vận chuyển (nếu có).
          </div>
        </div>

        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Gửi ship thì nhập thông tin nào?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Điền đơn vị vận chuyển và mã vận đơn (nếu đã có). Nếu chưa có, bạn có thể bổ sung sau qua hotline/chat.
          </div>
        </div>

        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Tôi có được báo giá trước không?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Có. Sau khi kiểm tra, chúng tôi báo giá minh bạch. Chỉ tiến hành sửa khi bạn đồng ý.
          </div>
        </div>

        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Có bảo hành không?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Có bảo hành theo từng hạng mục. Thời hạn cụ thể sẽ được ghi rõ khi bàn giao.
          </div>
        </div>

        <div class="booking-qa">
          <button class="booking-qa-q" type="button" data-acc-btn>
            Tôi cần tư vấn gấp thì làm sao?
            <span class="booking-qa-ic" aria-hidden="true"></span>
          </button>
          <div class="booking-qa-a" data-acc-panel>
            Bạn có thể gọi hotline hoặc mở chat hỗ trợ. Thông tin liên hệ có thể đặt ở footer hoặc nút nổi.
          </div>
        </div>
      </div>

      <div class="booking-final-cta">
        <div class="booking-final-card">
          <div class="booking-final-title">Cần tư vấn nhanh?</div>
          <div class="booking-final-sub">Liên hệ để được hướng dẫn đóng gói/nhận máy và tư vấn lỗi.</div>
          <div class="booking-final-actions">
            <a class="booking-btn booking-btn-primary" href="#bookingForm">Đặt lịch</a>
            <a class="booking-btn booking-btn-ghost" href="tel:0900000000">Gọi hotline</a>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>
@endsection

@push('scripts')
  @vite(['resources/js/app.js','resources/js/pages/booking.js'])
@endpush
