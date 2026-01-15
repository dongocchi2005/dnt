<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng ký • Cyber Access</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-cyber h-screen flex items-center justify-center font-sans text-white">

<!-- CIRCUIT BACKGROUND -->
<canvas id="circuitCanvas"></canvas>

<div class="w-full max-w-md cyber-card p-10">

    <!-- LOGO -->
    <div class="flex justify-center mb-8">
        <img src="/image/logo.png"
             class="h-20 w-auto drop-shadow-[0_0_25px_rgba(34,211,238,0.65)]
                    hover:drop-shadow-[0_0_45px_rgba(167,139,250,0.85)]
                    transition">
    </div>

    @if($errors->any())
        <div class="bg-red-500/20 text-red-300 p-3 rounded-lg mb-4 text-sm">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="floating-label">
            <input type="text" name="name" placeholder=" " required value="{{ old('name') }}">
            <label>Họ và tên</label>
        </div>
        <div class="floating-label">
            <input type="tel" name="phone" placeholder=" " inputmode="numeric" value="{{ old('phone') }}">
            <label>Số điện thoại *</label>
        </div>
        <div class="floating-label">
            <input type="email" name="email" placeholder=" " value="{{ old('email') }}">
            <label>Email (không bắt buộc)</label>
        </div>
        <div class="text-xs text-white/60">
            Gmail không bắt buộc.
        </div>

        <div class="floating-label">
            <input type="password" name="password" placeholder=" " required>
            <label>Mật khẩu</label>
        </div>

        <div class="floating-label">
            <input type="password" name="password_confirmation" placeholder=" " required>
            <label>Xác nhận mật khẩu</label>
        </div>

        <button type="submit" class="cyber-btn w-full">
            TẠO TÀI KHOẢN
        </button>
    </form>

    <p class="text-center text-white/70 mt-6">
        Đã có tài khoản?
        <a href="{{ route('login') }}" class="cyber-link font-medium">Đăng nhập</a>
    </p>
</div>

<!-- ===== CIRCUIT PULSE SCRIPT ===== -->
<script>
const canvas = document.getElementById('circuitCanvas');
const ctx = canvas.getContext('2d');
let w,h;

function resize(){
    w = canvas.width = window.innerWidth;
    h = canvas.height = window.innerHeight;
}
window.addEventListener('resize', resize);
resize();

const nodes = Array.from({length:80},()=>({
    x:Math.random()*w,
    y:Math.random()*h,
    dir:Math.random()>.5?'h':'v',
    speed:Math.random()*.6+.2,
    pulse:Math.random()*100
}));

function draw(){
    ctx.clearRect(0,0,w,h);
    nodes.forEach(n=>{
        n.pulse+=.02;
        const glow=Math.abs(Math.sin(n.pulse))*.8+.2;

        ctx.strokeStyle=`rgba(34,211,238,${.15+glow*.45})`;
        ctx.lineWidth=1;

        ctx.beginPath();
        ctx.moveTo(n.x,n.y);
        if(n.dir==='h'){
            ctx.lineTo(n.x+60,n.y);
            ctx.lineTo(n.x+60,n.y+40);
            n.x+=n.speed;
            if(n.x>w) n.x=-80;
        }else{
            ctx.lineTo(n.x,n.y+60);
            ctx.lineTo(n.x+40,n.y+60);
            n.y+=n.speed;
            if(n.y>h) n.y=-80;
        }
        ctx.stroke();

        ctx.fillStyle=`rgba(167,139,250,${glow})`;
        ctx.beginPath();
        ctx.arc(n.x,n.y,2.2,0,Math.PI*2);
        ctx.fill();
    });
    requestAnimationFrame(draw);
}
draw();
</script>

</body>
</html>
