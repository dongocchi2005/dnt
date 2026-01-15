<footer class="mt-20 cyber-footer">
  <div class="footer-wrap">
    <div class="max-w-7xl mx-auto px-4 py-12">

      <!-- Top: Brand + CTA -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-3">
          @if(!empty($logo))
            <img src="{{ asset($logo->path) }}" alt="DNT Store" class="h-10 w-auto footer-logo">
          @endif
          <div>
            <div class="text-xl font-extrabold leading-tight">
              <span class="footer-brand" style="color: var(--cyber-orange, #F37021) !important;">DNT</span><span class="footer-title">Store</span>
            </div>
            <div class="footer-muted text-sm">Trung tâm sửa chữa đồ công nghệ – Uy tín, nhanh chóng, minh bạch</div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
          <a href="{{ route('booking.create') }}" class="footer-cta">
            <i class="fa-solid fa-calendar-check mr-2"></i> Đặt lịch sửa ngay
          </a>
          <a href="{{ route('contact') }}" class="footer-ghost">
            <i class="fa-solid fa-headset mr-2"></i> Tư vấn miễn phí
          </a>
        </div>
      </div>

      <div class="footer-divider my-10"></div>

      <!-- Main grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-10">

        <!-- Column 1: About -->
        <div class="md:col-span-4">
          <h4 class="footer-heading">Về DNT Store</h4>
          <p class="footer-muted leading-relaxed">
            Chúng tôi chuyên sửa chữa thiết bị công nghệ & đồ chơi công nghệ: tai nghe, loa, tay cầm, phụ kiện,
            thiết bị thông minh... Kiểm tra rõ lỗi – báo giá minh bạch – bảo hành đầy đủ.
          </p>

          <div class="mt-5 flex flex-wrap gap-2">
            <span class="footer-badge"><i class="fa-solid fa-shield-halved mr-2"></i>Bảo hành rõ ràng</span>
            <span class="footer-badge"><i class="fa-solid fa-bolt mr-2"></i>Sửa nhanh</span>
            <span class="footer-badge"><i class="fa-solid fa-magnifying-glass mr-2"></i>Kiểm tra kỹ</span>
          </div>
        </div>

        <!-- Column 2: Services -->
        <div class="md:col-span-3">
          <h4 class="footer-heading">Dịch vụ nổi bật</h4>
          <ul class="footer-list">
            <li><a href="{{ route('services') }}">Sửa tai nghe – loa</a></li>
            <li><a href="{{ route('services') }}">Thay pin – nâng cấp linh kiện</a></li>
            <li><a href="{{ route('services') }}">Sửa sạc – cổng kết nối</a></li>
            <li><a href="{{ route('services') }}">Sửa tay cầm / thiết bị gaming</a></li>
            <li><a href="{{ route('services') }}">Vệ sinh – phục hồi thiết bị</a></li>
          </ul>
        </div>

        <!-- Column 3: Links -->
        <div class="md:col-span-2">
          <h4 class="footer-heading">Liên kết</h4>
          <ul class="footer-list">
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('blog.index') }}">Tin tức</a></li>
            <li><a href="{{ route('clearance.index') }}">Sản Phẩm</a></li>
            <li><a href="{{ route('contact') }}">Liên hệ</a></li>
          </ul>
        </div>

        <!-- Column 4: Contact -->
        <div class="md:col-span-3">
          <h4 class="footer-heading">Thông tin liên hệ</h4>

          <div class="space-y-3 footer-muted">
            <div class="footer-item">
              <i class="fa-solid fa-location-dot"></i>
              <span>24/25 Nguyễn Sáng,Phường Tây Thạnh Quận Tân Phú TP. Hồ Chí Minh </span>
            </div>
            <div class="footer-item">
              <i class="fa-solid fa-phone"></i>
              <a class="footer-link" href="tel:0123456789">0987 833 560</a>
            </div>
            <div class="footer-item">
              <i class="fa-solid fa-envelope"></i>
              <a class="footer-link" href="https://mail.google.com/mail/u/0/#inbox">ngocthan.org@gmail.com</a>
            </div>
            <div class="footer-item">
              <i class="fa-solid fa-clock"></i>
              <span>09:00 – 17:00 (T2–CN)</span>
            </div>
          </div>

          <div class="mt-5 flex items-center gap-3">
            <a class="footer-social" href="https://www.facebook.com/share/1AZfnWobbG/?mibextid=wwXIfr" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>

            <a class="footer-social" href="https://www.tiktok.com/@dntstore030295?_r=1&_t=ZS-92mLMjta7U0" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
<a class="footer-social"
   href="https://zalo.me/84987833560"
   target="_blank"
   aria-label="Zalo">
    <svg width="20" height="20" viewBox="0 0 48 48" fill="currentColor">
        <path d="M24 4C13.5 4 5 11.6 5 21c0 5.1 2.7 9.6 7 12.7L10 44l10.5-6.4c1.1.2 2.3.4 3.5.4 10.5 0 19-7.6 19-17S34.5 4 24 4z"/>
    </svg>
</a>

            <a class="footer-social" href="https://www.youtube.com/@dnt_store" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          </div>

          <!-- Mini subscribe -->
     
        </div>

      </div>

      <div class="footer-divider my-10"></div>

      <!-- Bottom -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
        <div class="footer-muted">
          © {{ date('Y') }} DNT Store. All rights reserved.
        </div>

        <div class="flex flex-wrap items-center gap-4 footer-muted">
          <a class="footer-mini" href="#">Chính sách bảo hành</a>
          <a class="footer-mini" href="#">Điều khoản dịch vụ</a>
          <a class="footer-mini" href="#">Chính sách bảo mật</a>
        </div>
      </div>

    </div>
  </div>
</footer>
