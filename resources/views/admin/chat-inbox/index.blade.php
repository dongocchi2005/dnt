@extends('layouts.admin')

@section('title', 'Chat Inbox - DNT Store')
@section('page-title', 'Chat Inbox')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col cyber-panel overflow-hidden">
    {{-- Tabs --}}
    <div class="px-6 py-4 border-b border-white/10 flex items-center gap-2 bg-white/5">
        @php
            $tabs = [
                'pending' => ['label' => 'Chờ xử lý', 'icon' => 'fa-clock', 'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/20'],
                'assigned' => ['label' => 'Đang hỗ trợ', 'icon' => 'fa-user-headset', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/20'],
                'closed' => ['label' => 'Đã đóng', 'icon' => 'fa-check-circle', 'color' => 'text-gray-400', 'bg' => 'bg-white/10'],
            ];
        @endphp
        @foreach($tabs as $key => $config)
            @php
                $isActive = $status === $key;
                $count = $counts[$key] ?? 0;
            @endphp
            <a href="{{ route('admin.chat-inbox.index', ['status' => $key]) }}"
               class="relative flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                      {{ $isActive 
                         ? 'bg-white/10 text-bl shadow-[0_0_15px_rgba(0,255,255,0.15)] border border-white/20' 
                         : 'text-bl/60 hover:text-bl hover:bg-white/5' }}">
                <i class="fa-solid {{ $config['icon'] }} {{ $isActive ? $config['color'] : 'text-bl/40' }}"></i>
                {{ $config['label'] }}
                @if($count > 0)
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $isActive ? $config['bg'] . ' ' . $config['color'] : 'bg-white/10 text-bl/50' }}">
                        {{ $count }}
                    </span>
                @endif
                
                @if($isActive)
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-blue-500 rounded-t-full -mb-[1px] shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
        @if(session('success'))
            <div class="m-4 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-check mr-2"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="m-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 flex items-center shadow-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="divide-y divide-white/5">
            @forelse($sessions as $session)
                @php
                    $displayName = $session->guest_name ?: ($session->user?->name ?? 'Khách vãng lai');
                    $email = $session->guest_email ?: ($session->user?->email ?? '');
                    $phone = $session->guest_phone ?: ($session->user?->phone ?? '');
                    $contact = collect([$email, $phone])->filter()->implode(' • ');
                    $lastMessageAt = $session->last_message_at ? $session->last_message_at->format('H:i d/m/Y') : 'N/A';
                    $assignedName = $session->assignedAdmin?->name;
                    $unread = ($session->unread_count ?? 0);
                @endphp
                <div class="group relative p-5 hover:bg-white/5 transition-colors duration-200 flex flex-col sm:flex-row gap-4 sm:items-center justify-between {{ $unread > 0 ? 'bg-blue-500/5' : '' }}">
                    
                    {{-- Left Info --}}
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold border border-white/10
                                        {{ $unread > 0 ? 'bg-blue-500/20 text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.3)]' : 'bg-white/5 text-bl/40' }}">
                                {{ strtoupper(substr($displayName, 0, 1)) }}
                            </div>
                            @if($unread > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full border border-white/20 shadow-sm animate-pulse">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base font-bold text-bl truncate {{ $unread > 0 ? 'neon text-blue-400' : '' }}">
                                    {{ $displayName }}
                                </h3>
                                @if($assignedName)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-white/10 text-bl/60 border border-white/10 flex items-center gap-1" title="Được hỗ trợ bởi {{ $assignedName }}">
                                        <i class="fa-solid fa-headset text-bl/40"></i> {{ $assignedName }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-3 text-sm text-bl/60 mb-1.5">
                                @if($email)
                                    <span class="flex items-center gap-1.5 truncate" title="{{ $email }}">
                                        <i class="fa-regular fa-envelope text-bl/40"></i> {{ $email }}
                                    </span>
                                @endif
                                @if($phone)
                                    <span class="flex items-center gap-1.5 truncate">
                                        <i class="fa-solid fa-phone text-bl/40 text-xs"></i> {{ $phone }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-bl/40 flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i> Tin nhắn cuối: {{ $lastMessageAt }}
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pl-16 sm:pl-0">
                        @if($status === 'pending')
                            <form method="POST" action="{{ route('admin.chat-sessions.takeover', $session) }}">
                                @csrf
                                <button type="submit" 
                                        class="px-4 py-2 rounded-lg bg-white/5 border border-blue-500/30 text-blue-400 text-sm font-medium hover:bg-blue-500/10 hover:border-blue-400/50 transition-all shadow-sm flex items-center gap-2">
                                    <i class="fa-solid fa-hand-holding-hand"></i> Tiếp nhận
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.chat-sessions.show', $session) }}"
                           class="cyber-btn bg-blue-600 text-white text-sm font-medium flex items-center gap-2">
                            <span>Chi tiết</span> <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Hover Indicator --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity shadow-[0_0_10px_#3b82f6]"></div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-bl/40">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-4 border border-white/10">
                        <i class="fa-regular fa-comments text-3xl text-bl/20"></i>
                    </div>
                    <p class="text-lg font-medium text-bl/60">Chưa có phiên chat nào</p>
                    <p class="text-sm">Hiện tại không có tin nhắn nào trong mục này</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Footer/Pagination --}}
    @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-white/10 bg-white/5">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection
