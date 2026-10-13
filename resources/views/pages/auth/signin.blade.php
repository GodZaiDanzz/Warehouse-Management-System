@extends('layouts.fullscreen-layout')

@php
    use App\Models\CompanyProfile;
    $company = CompanyProfile::getProfile();
    $themeColor = $company->theme_color ?? '#275538';
    $btnSaveColor = $company->btn_save_color ?? $themeColor;

    // Ekstrak RGB untuk manipulasi opasitas kanvas
    $hex = ltrim($themeColor, '#');
    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } elseif (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    } else {
        $r = 39; $g = 85; $b = 56;
    }
    $themeRgb = "{$r}, {$g}, {$b}";
@endphp

@section('content')
<div class="relative min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 select-none overflow-hidden">
    <!-- Subtle Energy & Logistics Flow Animated Canvas (antislop-ui compliant) -->
    <canvas id="energyWaveCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0" aria-hidden="true"></canvas>

    <!-- Header / Logo & Title Section -->
    <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo Display -->
        <div class="flex items-center justify-center gap-3 sm:gap-4 mb-4">
            @if($company->hasPrimaryLogo())
                <div class="p-2.5 rounded-2xl bg-white shadow-xs border border-gray-200">
                    <img src="{{ asset($company->logo_path) }}?v={{ filemtime(public_path($company->logo_path)) }}" alt="Logo 1" class="h-12 w-12 sm:h-14 sm:w-14 object-contain rounded-xl" />
                </div>
            @else
                <div class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl text-white font-black text-xl shadow-xs" style="background-color: {{ $themeColor }};">
                    {{ strtoupper(substr($company->name ?? 'ME', 0, 2)) }}
                </div>
            @endif

            @if($company->hasSecondaryLogo())
                <span class="text-gray-300 text-2xl font-light select-none">|</span>
                <div class="p-2.5 rounded-2xl bg-white shadow-xs border border-gray-200">
                    <img src="{{ asset($company->secondary_logo_path) }}?v={{ filemtime(public_path($company->secondary_logo_path)) }}" alt="Logo 2" class="h-12 w-12 sm:h-14 sm:w-14 object-contain rounded-xl" />
                </div>
            @endif
        </div>
        
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 leading-tight">
            {{ $company->name ?? 'PT MUDALAYA ENERGY INDONESIA' }}
        </h1>
        @if($company->secondary_name)
            <h2 class="text-base sm:text-lg font-bold tracking-tight text-gray-700 leading-tight mt-1">
                {{ $company->secondary_name }}
            </h2>
        @endif
        <p class="mt-2 text-xs text-gray-500 font-medium tracking-wide">
            Sistem Informasi Manajemen Pergudangan & Ketersediaan Stok
        </p>
    </div>

    <!-- Login Card Container -->
    <div class="relative z-10 mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white/95 backdrop-blur-xs py-8 px-6 sm:px-8 shadow-sm rounded-2xl border border-gray-200 space-y-6">
            
            <!-- Brand Accent Header Bar -->
            <div class="h-1 w-full rounded-full" style="background-color: {{ $themeColor }};"></div>

            <!-- Alert Notifications -->
            <x-alert />

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-gray-700">
                        Email Akun <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="contoh: user@mudalaya.com"
                            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 shadow-2xs transition">
                    </div>
                </div>

                <div class="space-y-1.5" x-data="{ show: false }">
                    <label for="password" class="block text-xs font-bold text-gray-700">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" id="password" name="password" value="" required
                            placeholder="Masukkan password..."
                            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 shadow-2xs transition">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer transition" :title="show ? 'Sembunyikan password' : 'Lihat password'">
                            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" value="1" checked class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        <span class="text-xs font-medium text-gray-600 group-hover:text-gray-900 transition">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2 rounded-xl py-2.5 px-4 text-sm font-bold text-white shadow-xs transition-all duration-200 cursor-pointer hover:brightness-105 active:scale-[0.99]"
                        style="background-color: {{ $btnSaveColor }};">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Masuk ke Sistem WMS</span>
                    </button>
                </div>
            </form>

            <div class="pt-2 text-center text-xs text-gray-400 border-t border-gray-100 font-medium">
                &copy; {{ date('Y') }} {{ $company->name ?? 'PT Mudalaya Energy Indonesia' }}. Hak cipta dilindungi.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const canvas = document.getElementById('energyWaveCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Palet warna resmi PT Mudalaya Energy Indonesia
    const primaryRgb = '{{ $themeRgb }}'; // Corporate Forest Green (default 39, 85, 56)
    const amberRgb = '245, 158, 11';      // Energy Amber Accent

    let width = 0;
    let height = 0;
    let dpr = 1;
    let animationFrameId = null;
    let isRunning = false;

    // Posisi kursor untuk interaksi paralaks yang halus
    let mouseX = 0;
    let mouseY = 0;
    let targetMouseX = 0;
    let targetMouseY = 0;

    // Konfigurasi lapisan gelombang energi
    const waves = [
        {
            // Lapisan 1: Gelombang dasar Forest Green lembut
            color: `rgba(${primaryRgb}, 0.05)`,
            strokeColor: `rgba(${primaryRgb}, 0.12)`,
            baseYRatio: 0.62,
            amplitude: 45,
            frequency: 0.0018,
            speed: 0.0006,
            phaseOffset: 0
        },
        {
            // Lapisan 2: Gelombang sekunder Forest Green dengan arah berlawanan
            color: `rgba(${primaryRgb}, 0.035)`,
            strokeColor: `rgba(${primaryRgb}, 0.09)`,
            baseYRatio: 0.74,
            amplitude: 55,
            frequency: 0.0012,
            speed: -0.00045,
            phaseOffset: 2.1
        },
        {
            // Lapisan 3: Gelombang aksen Energy Amber (arus hangat energi)
            color: `rgba(${amberRgb}, 0.025)`,
            strokeColor: `rgba(${amberRgb}, 0.08)`,
            baseYRatio: 0.50,
            amplitude: 35,
            frequency: 0.0022,
            speed: 0.0008,
            phaseOffset: 4.2
        },
        {
            // Lapisan 4: Garis kontur atas lembut Forest Green
            color: `rgba(${primaryRgb}, 0.02)`,
            strokeColor: `rgba(${primaryRgb}, 0.07)`,
            baseYRatio: 0.38,
            amplitude: 28,
            frequency: 0.0015,
            speed: -0.00035,
            phaseOffset: 1.2
        }
    ];

    function resize() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = canvas.parentElement ? canvas.parentElement.clientWidth : window.innerWidth;
        height = canvas.parentElement ? canvas.parentElement.clientHeight : window.innerHeight;

        canvas.width = Math.floor(width * dpr);
        canvas.height = Math.floor(height * dpr);
        ctx.scale(dpr, dpr);

        // Jika prefers-reduced-motion aktif, gambar sekali secara statis
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            drawFrame(0);
        }
    }

    function onMouseMove(e) {
        // Normalisasi posisi mouse antara -1 dan 1
        const rect = canvas.getBoundingClientRect();
        targetMouseX = ((e.clientX - rect.left) / width - 0.5) * 2;
        targetMouseY = ((e.clientY - rect.top) / height - 0.5) * 2;
    }

    window.addEventListener('mousemove', onMouseMove, { passive: true });

    function drawWave(wave, timestamp) {
        const baseY = height * wave.baseYRatio + (mouseY * 20);
        const amp = wave.amplitude + (Math.abs(mouseX) * 10);
        const time = timestamp * wave.speed + wave.phaseOffset;

        ctx.beginPath();
        ctx.moveTo(0, height);

        // Titik awal kurva di sisi kiri
        const startY = baseY + Math.sin(time) * amp;
        ctx.lineTo(0, startY);

        const step = 20; // Resolusi kurva per 20px agar mulus namun ringan
        for (let x = 0; x <= width; x += step) {
            // Kombinasi dua sinus untuk bentuk organik non-repetitif
            const mouseOffset = mouseX * Math.sin(x * 0.001) * 15;
            const y = baseY + 
                Math.sin(x * wave.frequency + time) * amp + 
                Math.cos(x * (wave.frequency * 0.6) - time * 0.5) * (amp * 0.4) +
                mouseOffset;

            ctx.lineTo(x, y);
        }

        ctx.lineTo(width, height);
        ctx.closePath();

        // Isi gelombang dengan gradasi transparan
        ctx.fillStyle = wave.color;
        ctx.fill();

        // Garis tepi kontur atas untuk sentuhan presisi teknik
        ctx.beginPath();
        for (let x = 0; x <= width; x += step) {
            const mouseOffset = mouseX * Math.sin(x * 0.001) * 15;
            const y = baseY + 
                Math.sin(x * wave.frequency + time) * amp + 
                Math.cos(x * (wave.frequency * 0.6) - time * 0.5) * (amp * 0.4) +
                mouseOffset;

            if (x === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        ctx.lineWidth = 1.25;
        ctx.strokeStyle = wave.strokeColor;
        ctx.stroke();
    }

    function drawFrame(timestamp) {
        ctx.clearRect(0, 0, width, height);

        // Damping pergerakan kursor mouse agar terasa meluncur lembut
        mouseX += (targetMouseX - mouseX) * 0.035;
        mouseY += (targetMouseY - mouseY) * 0.035;

        // Render setiap lapisan gelombang
        for (let i = 0; i < waves.length; i++) {
            drawWave(waves[i], timestamp);
        }
    }

    function loop(timestamp) {
        if (!isRunning) return;
        drawFrame(timestamp);
        animationFrameId = requestAnimationFrame(loop);
    }

    function start() {
        if (isRunning) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            drawFrame(0);
            return;
        }
        isRunning = true;
        animationFrameId = requestAnimationFrame(loop);
    }

    function stop() {
        isRunning = false;
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
            animationFrameId = null;
        }
    }

    // Tangani tab visibility (hemat daya jika tab diminimalkan/tidak aktif)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else {
            start();
        }
    });

    let resizeTimeout = null;
    window.addEventListener('resize', () => {
        if (resizeTimeout) cancelAnimationFrame(resizeTimeout);
        resizeTimeout = requestAnimationFrame(resize);
    }, { passive: true });

    // Inisialisasi awal
    resize();
    start();
})();
</script>
@endpush

