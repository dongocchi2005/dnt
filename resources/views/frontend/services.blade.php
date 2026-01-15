@extends('frontend.layouts.app')

@section('content')
<div class="particles-container">
    @for ($i = 0; $i < 30; $i++)
        <div class="particle" style="left: {{ rand(0, 100) }}%; animation-duration: {{ rand(5, 15) }}s; animation-delay: {{ rand(0, 5) }}s;"></div>
    @endfor
</div>

<!-- Hero -->
<div class="cyber-hero-small">
    <div>
        <h1 class="glitch text-5xl md:text-7xl font-bold mb-4" data-text="SERVICES">SERVICES</h1>
        <p class="text-xl md:text-2xl text-gray-300 neon-text-cyan">System Maintenance & Upgrades</p>
    </div>
</div>

<!-- Service Grid -->
<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Service 1 -->
        <div class="cyber-card text-center group">
            <div class="mb-6 text-6xl text-cyan-400 neon-text-cyan group-hover:scale-110 transition-transform">
                <i class="fas fa-microchip"></i>
            </div>
            <h3 class="text-2xl font-bold mb-4 text-white">HARDWARE REPAIR</h3>
            <p class="text-gray-400 mb-6">Advanced diagnostic and repair protocols for all cyber-decks and mobile terminals.</p>
            <a href="#" class="cyber-btn-pink inline-block">INITIATE</a>
        </div>

        <!-- Service 2 -->
        <div class="cyber-card text-center group">
            <div class="mb-6 text-6xl text-pink-500 neon-text-pink group-hover:scale-110 transition-transform">
                <i class="fas fa-code-branch"></i>
            </div>
            <h3 class="text-2xl font-bold mb-4 text-white">SOFTWARE FLASH</h3>
            <p class="text-gray-400 mb-6">OS recovery, firmware updates, and malware purging services.</p>
            <a href="#" class="cyber-btn-pink inline-block">INITIATE</a>
        </div>

        <!-- Service 3 -->
        <div class="cyber-card text-center group">
            <div class="mb-6 text-6xl text-cyan-400 neon-text-cyan group-hover:scale-110 transition-transform">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="text-2xl font-bold mb-4 text-white">WARRANTY EXTENSION</h3>
            <p class="text-gray-400 mb-6">Secure your hardware investment with our extended protection nodes.</p>
            <a href="#" class="cyber-btn-pink inline-block">INITIATE</a>
        </div>
    </div>
</div>

<!-- Process Section -->
<div class="container mx-auto px-4 py-16 border-t border-cyan-900/30">
    <h2 class="text-3xl font-bold text-center mb-12 neon-text-cyan">WORKFLOW PROTOCOL</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
        <div>
            <div class="text-4xl font-bold text-gray-700 mb-2">01</div>
            <h4 class="text-xl font-bold text-white mb-2">DIAGNOSE</h4>
            <p class="text-sm text-gray-500">Scan for errors</p>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-700 mb-2">02</div>
            <h4 class="text-xl font-bold text-white mb-2">PROPOSE</h4>
            <p class="text-sm text-gray-500">Generate quote</p>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-700 mb-2">03</div>
            <h4 class="text-xl font-bold text-white mb-2">EXECUTE</h4>
            <p class="text-sm text-gray-500">Perform repairs</p>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-700 mb-2">04</div>
            <h4 class="text-xl font-bold text-white mb-2">DEPLOY</h4>
            <p class="text-sm text-gray-500">Return to user</p>
        </div>
    </div>
</div>
@endsection
