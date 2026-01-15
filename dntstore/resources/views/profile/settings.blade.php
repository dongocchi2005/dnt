{{-- resources/views/profile/settings.blade.php --}}
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <script>
        (function () {
            let t = localStorage.getItem('dnt_theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="profile-settings ui-scale" data-page="settings">
@include('frontend.partials.header')
<div class="container">
    <div class="topbar">
        <h1 class="title">Cài đặt tài khoản</h1>

        <div class="actions">
            <a class="btn btn-light" href="{{ route('dashboard') }}">← Dashboard</a>

            <form method="POST" action="{{ route('settings') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Đăng xuất</button>
            </form>
        </div>
    </div>

    <div class="card">
        @if (session('warning'))
            <div class="alert alert-error" style="border-color: rgba(251, 191, 36, 0.35); background: linear-gradient(180deg, rgba(251, 191, 36, 0.14), rgba(251, 191, 36, 0.06));">
                <strong>Thông báo:</strong> {{ session('warning') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Vui lòng kiểm tra lại:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf

            <p class="section-title">Thông tin cơ bản</p>
            <p class="muted">Cập nhật tên hiển thị và đổi mật khẩu nếu cần.</p>

            <div class="row">
                <div>
                    <label for="name">Họ và tên</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                    >
                </div>

                <div>
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ $user->email }}"
                        readonly
                    >
                  
                </div>
            </div>

            <div class="divider"></div>

            <p class="section-title">Thông tin giao hàng</p>
            <p class="muted">Cập nhật thông tin giao hàng để nhận hàng thuận tiện hơn.</p>

            <div class="row">
                <div>
                    <label for="phone">Số điện thoại</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="Ví dụ: 0123456789"
                    >
                </div>

                <div>
                    <label for="address">Địa chỉ</label>
                    <input
                        id="address"
                        name="address"
                        type="text"
                        value="{{ old('address', $user->address) }}"
                        placeholder="Ví dụ: 123 Đường ABC"
                    >
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="city">Tỉnh/Thành phố</label>
                    <input
                        id="city"
                        name="city"
                        type="text"
                        value="{{ old('city', $user->city) }}"
                        placeholder="Ví dụ: Hà Nội"
                    >
                </div>

                <div>
                    <label for="district">Quận/Huyện</label>
                    <input
                        id="district"
                        name="district"
                        type="text"
                        value="{{ old('district', $user->district) }}"
                        placeholder="Ví dụ: Hoàn Kiếm"
                    >
                </div>
            </div>

            <div class="row-1">
                <div>
                    <label for="ward">Phường/Xã</label>
                    <input
                        id="ward"
                        name="ward"
                        type="text"
                        value="{{ old('ward', $user->ward) }}"
                        placeholder="Ví dụ: Phúc Tân"
                    >
                </div>
            </div>

            <div class="divider"></div>

            <p class="section-title">Đổi mật khẩu</p>
            <p class="muted">Nếu không muốn đổi, để trống 3 ô dưới.</p>

            <div class="row-1">
                <div>
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Nhập mật khẩu hiện tại"
                    >
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="password">Mật khẩu mới</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Tối thiểu 6 ký tự"
                    >
                </div>

                <div>
                    <label for="password_confirmation">Nhập lại mật khẩu mới</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Nhập lại mật khẩu mới"
                    >
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Lưu thay đổi</button>
                <a class="btn btn-light" href="{{ route('dashboard') }}">Về trang chủ</a>
            </div>
        </form>
    </div>

   
</div>
</body>
</html>
