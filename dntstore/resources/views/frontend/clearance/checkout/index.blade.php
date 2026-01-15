@extends('frontend.layouts.app')

@section('title','Thanh toán | DNT Store')

@section('content')
<div class="checkout-wrap min-h-screen py-14">
  <div class="max-w-6xl mx-auto px-4">

    {{-- Header --}}
    <div class="cyber-panel">
      <div class="cyber-head">
        <div>
          <h1 class="cyber-title">Thanh toán</h1>
          <p class="cyber-sub">
            Bạn đang ở luồng <strong class="cy-strong">Mua ngay</strong> — thanh toán trực tiếp, không qua giỏ hàng.
          </p>
        </div>
        <span class="badge">Checkout • DNT Store</span>
      </div>

      @if(session('success'))
        <div class="section cy-section">
          <div class="sec-title">Thông báo</div>
          <div class="note">{{ session('success') }}</div>
        </div>
      @endif

      <div class="grid">

        {{-- LEFT: Customer + Shipping + Payment --}}
        <div>
          <form action="{{ route('checkout.place') }}" method="POST" class="space-y-0">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="qty" value="{{ $qty }}">
            <input type="hidden" name="variant_id" value="{{ $variantId }}">

            <div class="section">
              <h3 class="sec-title">Thông tin nhận hàng <span class="small">(* bắt buộc)</span></h3>

              <div class="row2">
                <div class="field">
                  <label class="label">Họ và tên *</label>
                  <input class="input" name="name" value="{{ old('name') }}" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="field">
                  <label class="label">Số điện thoại *</label>
                  <input class="input" name="phone" value="{{ old('phone') }}" placeholder="0xxxxxxxxx" required>
                </div>
              </div>

              <div class="field">
                <label class="label">Email (không bắt buộc)</label>
                <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="email@dntstore.vn">
              </div>

              <div class="field">
                <label class="label">Địa chỉ *</label>
                <input class="input" name="address" value="{{ old('address') }}" placeholder="Số nhà, đường, phường/xã..." required>
              </div>

              <div class="row2">
                <div class="field">
                  <label class="label">Tỉnh/Thành *</label>
                  <input class="input" name="province" value="{{ old('province') }}" placeholder="TP.HCM / Hà Nội ..." required>
                </div>
                <div class="field">
                  <label class="label">Quận/Huyện *</label>
                  <input class="input" name="district" value="{{ old('district') }}" placeholder="Quận 1 ..." required>
                </div>
              </div>

              <div class="field">
                <label class="label">Ghi chú</label>
                <textarea class="textarea" name="note" placeholder="Ví dụ: Giao giờ hành chính...">{{ old('note') }}</textarea>
              </div>
            </div>

            <div class="section">
              <h3 class="sec-title">Phương thức thanh toán</h3>

              <div class="pay-method">
                <label class="pay-item">
                  <input type="radio" name="payment_method" value="cod" checked>
                  <div>
                    <div class="pay-name">COD (Thanh toán khi nhận hàng)</div>
                    <div class="pay-desc">Bạn trả tiền khi nhận được hàng. Phù hợp đơn giao nhanh.</div>
                  </div>
                </label>

                <label class="pay-item">
                  <input type="radio" name="payment_method" value="vietqr">
                  <div>
                    <div class="pay-name">VietQR / Chuyển khoản</div>
                    <div class="pay-desc">Sau khi đặt hàng, hệ thống hiển thị QR để bạn quét thanh toán.</div>
                  </div>
                </label>
              </div>

              <p class="note">Lưu ý: Luồng “Mua ngay” thường tạo đơn với 1 sản phẩm và số lượng mặc định 1 (trừ khi bạn cho phép chọn số lượng).</p>
            </div>

            <div class="section">
              <h3 class="sec-title">Xác nhận</h3>
              <button type="submit" class="btn-cyber">
                Đặt hàng ngay
              </button>
              <div class="note">Bấm “Đặt hàng ngay” đồng nghĩa bạn đồng ý với điều khoản mua hàng và chính sách vận chuyển của DNT Store.</div>
            </div>
          </form>
        </div>

        {{-- RIGHT: Summary --}}
        <div>
          <div class="section">
            <h3 class="sec-title">Đơn hàng (Mua ngay) <span class="badge">1 sản phẩm</span></h3>

            {{-- Bạn render dữ liệu từ controller: $item, $product, $qty, $price --}}
            <div class="summary-item">
              <div class="sum-img">
                <img src="{{ $product->image_url ?? asset('images/placeholder.png') }}" alt="{{ $product->name }}">
              </div>

              <div>
                <div class="sum-name">{{ $product->name }}</div>
                <div class="sum-meta">
                  @if(!empty($variantLabel))
                    Biến thể: {{ $variantLabel }}
                  @else
                    Không có biến thể
                  @endif
                </div>
              </div>

              <div class="sum-price">
                <div class="p">{{ number_format($unitPrice) }}₫</div>
                <div class="q">x {{ $qty }}</div>
              </div>
            </div>

            <div class="hr"></div>

            <div class="tot-row">
              <span>Tạm tính</span>
              <strong>{{ number_format($subTotal) }}₫</strong>
            </div>
            <div class="tot-row">
              <span>Phí vận chuyển</span>
              <strong>{{ number_format($shippingFee) }}₫</strong>
            </div>

            <div class="hr"></div>

            <div class="tot-row total">
              <span>Tổng thanh toán</span>
              <strong>{{ number_format($grandTotal) }}₫</strong>
            </div>

            <p class="note">Nếu bạn dùng VietQR: sau khi tạo đơn, hệ thống có thể chuyển đến trang QR thanh toán và tự đối soát theo mã đơn.</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection
