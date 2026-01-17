<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập • Cyber Access</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-cyber h-screen flex items-center justify-center font-sans text-white">

<div class="w-full max-w-md cyber-card p-10">

    <!-- LOGO -->
    <div class="flex justify-center mb-8">
        <img
            src="/image/logo.png"
            alt="DNT Store"
            class="h-20 w-auto
                   drop-shadow-[0_0_25px_rgba(34,211,238,0.65)]
                   hover:drop-shadow-[0_0_45px_rgba(167,139,250,0.85)]
                   transition duration-300"
        >
    </div>

    {{-- HIỂN THỊ LỖI --}}
    @if ($errors->any())
        <div class="bg-red-500/20 text-red-300 p-3 rounded-lg mb-4 text-sm">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        {{-- EMAIL / SỐ ĐIỆN THOẠI --}}
        <div class="floating-label">
            <input
                type="text"
                name="login"
                placeholder=" "
                required
                autofocus
                value="{{ old('login') }}"
            >
            <label>Email hoặc số điện thoại</label>
        </div>

        {{-- MẬT KHẨU --}}
        <div class="floating-label">
            <input
                type="password"
                name="password"
                placeholder=" "
                required
            >
            <label>Mật khẩu</label>
        </div>

        <div class="flex items-center justify-between text-sm text-white/80">
            <label class="flex items-center gap-1">
                <input type="checkbox" name="remember">
                Ghi nhớ đăng nhập
            </label>

            <a href="{{ route('password.request') }}" class="cyber-link">
                Quên mật khẩu?
            </a>
        </div>

        <button type="submit" class="cyber-btn w-full text-center">
            ĐĂNG NHẬP
        </button>
    </form>

    <p class="text-center text-white/70 mt-6">
        Chưa có tài khoản?
        <a href="{{ route('register') }}" class="cyber-link font-medium">
            Đăng ký ngay
        </a>
    </p>

</div>

<!-- CYBER CIRCUIT BACKGROUND -->
<canvas id="circuitCanvas"></canvas>

<script>
const canvas = document.getElementById('circuitCanvas');
const ctx = canvas.getContext('2d');

let w, h;
function resize() {
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
}
window.addEventListener('resize', resize);
resize();

const nodes = Array.from({ length: 80 }, () => ({
    x: Math.random() * w,
    y: Math.random() * h,
    dir: Math.random() > 0.5 ? 'h' : 'v',
    speed: Math.random() * 0.6 + 0.2,
    pulse: Math.random() * 100
}));

function drawCircuit() {
    ctx.clearRect(0, 0, w, h);

    nodes.forEach(n => {
        n.pulse += 0.02;
        const glow = Math.abs(Math.sin(n.pulse)) * 0.8 + 0.2;

        ctx.strokeStyle = `rgba(34,211,238,${0.15 + glow * 0.45})`;
        ctx.lineWidth = 1;

        ctx.beginPath();
        ctx.moveTo(n.x, n.y);

        if (n.dir === 'h') {
            ctx.lineTo(n.x + 60, n.y);
            ctx.lineTo(n.x + 60, n.y + 40);
        } else {
            ctx.lineTo(n.x, n.y + 60);
            ctx.lineTo(n.x + 40, n.y + 60);
        }

        ctx.stroke();

        ctx.fillStyle = `rgba(167,139,250,${glow})`;
        ctx.beginPath();
        ctx.arc(n.x, n.y, 2.2, 0, Math.PI * 2);
        ctx.fill();

        if (n.dir === 'h') {
            n.x += n.speed;
            if (n.x > w) n.x = -80;
        } else {
            n.y += n.speed;
            if (n.y > h) n.y = -80;
        }
    });

    requestAnimationFrame(drawCircuit);
}

drawCircuit();
</script>

</body>
</html>
