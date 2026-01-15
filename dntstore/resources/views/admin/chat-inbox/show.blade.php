@extends('layouts.admin')

@section('title', 'Chat Session #' . $chatSession->id)
@section('page-title', 'Chi tiết Chat')

@section('content')
@php
    $displayName = $chatSession->guest_name ?: ($chatSession->user?->name ?? 'Khách vãng lai');
    $email = $chatSession->guest_email ?: ($chatSession->user?->email ?? '');
    $phone = $chatSession->guest_phone ?: ($chatSession->user?->phone ?? '');
    $assignedName = $chatSession->assignedAdmin?->name ?? 'Chưa có';
    $lastMessageAt = $chatSession->last_message_at ? $chatSession->last_message_at->format('H:i d/m/Y') : 'N/A';
@endphp

<div class="h-[calc(100vh-140px)] grid grid-cols-1 lg:grid-cols-4 gap-6">
    
    {{-- Sidebar Info --}}
    <div class="lg:col-span-1 flex flex-col gap-6">
        {{-- Customer Info Card --}}
        <div class="cyber-panel p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-xl font-bold shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                    {{ strtoupper(substr($displayName, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-bl truncate neon" title="{{ $displayName }}">{{ $displayName }}</h3>
                    <p class="text-xs text-bl/60">Khách hàng</p>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                @if($email)
                    <div class="flex items-center gap-2 text-bl/80">
                        <i class="fa-regular fa-envelope w-4 text-center text-bl/40"></i>
                        <span class="truncate" title="{{ $email }}">{{ $email }}</span>
                    </div>
                @endif
                @if($phone)
                    <div class="flex items-center gap-2 text-bl/80">
                        <i class="fa-solid fa-phone w-4 text-center text-bl/40"></i>
                        <span>{{ $phone }}</span>
                    </div>
                @endif
            </div>
            
            <div class="mt-4 pt-4 border-t border-white/10 space-y-3">
                <div>
                    <div class="text-xs text-bl/40 mb-1">Trạng thái</div>
                    <span id="chatStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-white/10 text-bl/80 border border-white/10">
                        {{ strtoupper($chatSession->status) }}
                    </span>
                </div>
                <div>
                    <div class="text-xs text-bl/40 mb-1">Người hỗ trợ</div>
                    <div id="chatAssignedName" class="text-sm font-medium text-bl/80 flex items-center gap-1">
                        <i class="fa-solid fa-headset text-bl/40 text-xs"></i> {{ $assignedName }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-bl/40 mb-1">Tin nhắn cuối</div>
                    <div class="text-sm text-bl/60">{{ $lastMessageAt }}</div>
                </div>
            </div>

            @if($chatSession->status === 'pending')
                <div class="mt-4">
                    <form method="POST" action="{{ route('admin.chat-sessions.takeover', $chatSession) }}">
                        @csrf
                        <button type="submit" class="cyber-btn w-full bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-2">
                            <i class="fa-solid fa-hand-holding-hand"></i> Tiếp nhận
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <a href="{{ route('admin.chat-inbox.index', ['status' => $chatSession->status]) }}" 
           class="flex items-center justify-center gap-2 py-2 px-4 bg-white/5 border border-white/10 text-bl/80 rounded-lg hover:bg-white/10 transition-colors shadow-sm text-sm font-medium">
            <i class="fa-solid fa-arrow-left"></i> Quay lại Inbox
        </a>
    </div>

    {{-- Chat Area --}}
    <div class="lg:col-span-3 cyber-panel flex flex-col overflow-hidden h-[calc(100vh-140px)]">
        {{-- Chat Header --}}
        <div class="px-6 py-3 border-b border-white/10 bg-white/5 flex items-center justify-between flex-shrink-0">
            <div class="text-sm text-bl/60">
                Session ID: <span class="font-mono text-bl/80">{{ $chatSession->session_id }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                <span class="text-xs font-bold text-green-400 neon">Live Connection</span>
            </div>
        </div>

        {{-- Messages --}}
        <div id="adminChatMessages"
             data-last-id="{{ $messages->last()?->id ?? 0 }}"
             class="flex-1 p-6 space-y-4 overflow-y-auto custom-scrollbar">
            @foreach($messages as $message)
                @php
                    $isUser = $message->role === 'user';
                    $isAdmin = $message->role === 'admin';
                @endphp
                <div class="flex {{ $isUser ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-[75%]">
                        <div class="flex items-center gap-2 mb-1 {{ $isUser ? 'justify-start' : 'justify-end' }}">
                            <span class="text-[10px] font-bold text-bl/40 uppercase tracking-wider">{{ $message->role }}</span>
                            <span class="text-[10px] text-bl/40">{{ $message->created_at?->format('H:i') }}</span>
                        </div>
                        <div class="px-4 py-2.5 rounded-2xl text-sm shadow-lg backdrop-blur-sm
                                    {{ $isAdmin 
                                       ? 'bg-blue-600/90 text-white rounded-br-none border border-blue-500/50 shadow-blue-900/20' 
                                       : ($isUser 
                                          ? 'bg-white/10 text-bl rounded-bl-none border border-white/10' 
                                          : 'bg-purple-500/20 text-purple-200 border border-purple-500/30') }}">
                            <div class="whitespace-pre-line leading-relaxed">{{ $message->content }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Input Area --}}
        <form id="adminChatForm" class="p-4 border-t border-white/10 bg-white/5 flex-shrink-0">
            @csrf
            <div class="relative flex gap-3">
                <input id="adminChatInput"
                       type="text"
                       class="flex-1 bg-white/5 text-bl placeholder-white/30 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500 transition-all shadow-inner"
                       placeholder="Nhập tin nhắn phản hồi..." 
                       autocomplete="off" />
                <button id="adminChatSend" type="submit" 
                        class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white whitespace-nowrap">
                    <span>Gửi</span> <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const messagesEl = document.getElementById('adminChatMessages');
    const form = document.getElementById('adminChatForm');
    const input = document.getElementById('adminChatInput');
    const sendBtn = document.getElementById('adminChatSend');
    const statusBadge = document.getElementById('chatStatusBadge');
    const assignedName = document.getElementById('chatAssignedName');
    
    if (!messagesEl || !form) return;

    let lastId = parseInt(messagesEl.getAttribute('data-last-id') || '0', 10);
    const fetchUrl = "{{ route('admin.chat-sessions.show', $chatSession) }}";
    const sendUrl = "{{ route('admin.chat-sessions.messages.store', $chatSession) }}";
    const readUrl = "{{ route('admin.chat-sessions.read', $chatSession) }}";
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const renderedIds = new Set();

    const scrollToBottom = () => {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    const renderMessage = (m) => {
        const id = m?.id ? Number(m.id) : null;
        if (id && renderedIds.has(id)) return;
        if (id) renderedIds.add(id);

        const wrap = document.createElement('div');
        const isUser = m.role === 'user';
        const isAdmin = m.role === 'admin';
        
        wrap.className = `flex ${isUser ? 'justify-start' : 'justify-end'}`;
        
        // Inner content wrapper
        const inner = document.createElement('div');
        inner.className = 'max-w-[75%]';
        
        // Meta header
        const meta = document.createElement('div');
        meta.className = `flex items-center gap-2 mb-1 ${isUser ? 'justify-start' : 'justify-end'}`;
        
        const time = m.created_at ? new Date(m.created_at) : null;
        const hh = time ? String(time.getHours()).padStart(2, '0') : '--';
        const mm = time ? String(time.getMinutes()).padStart(2, '0') : '--';
        
        meta.innerHTML = `
            <span class="text-[10px] font-bold text-bl/40 uppercase tracking-wider">${m.role}</span>
            <span class="text-[10px] text-bl/40">${hh}:${mm}</span>
        `;
        
        // Bubble
        const bubble = document.createElement('div');
        let bubbleClass = '';
        if (isAdmin) {
            bubbleClass = 'bg-blue-600/90 text-white rounded-br-none border border-blue-500/50 shadow-blue-900/20';
        } else if (isUser) {
            bubbleClass = 'bg-white/10 text-bl rounded-bl-none border border-white/10';
        } else {
            bubbleClass = 'bg-purple-500/20 text-purple-200 border border-purple-500/30';
        }
        
        bubble.className = `px-4 py-2.5 rounded-2xl text-sm shadow-lg backdrop-blur-sm ${bubbleClass}`;
        
        const content = document.createElement('div');
        content.className = 'whitespace-pre-line leading-relaxed';
        content.textContent = m.content || '';
        
        bubble.appendChild(content);
        
        inner.appendChild(meta);
        inner.appendChild(bubble);
        wrap.appendChild(inner);
        
        messagesEl.appendChild(wrap);
        scrollToBottom();
    };

    const markRead = async () => {
        if (!lastId) return;
        try {
            await fetch(readUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ last_message_id: lastId })
            });
        } catch (_) {}
    };

    const poll = async () => {
        try {
            const res = await fetch(`${fetchUrl}?since=${lastId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const list = Array.isArray(data.messages) ? data.messages : [];
            if (list.length) {
                list.forEach(renderMessage);
                lastId = list[list.length - 1].id;
                await markRead();
            }
            if (data.session) {
                if (statusBadge) statusBadge.textContent = String(data.session.status || '').toUpperCase();
                if (assignedName) assignedName.innerHTML = `<i class="fa-solid fa-headset text-bl/40 text-xs"></i> ${data.session.assigned_admin_name || 'Chưa có'}`;
            }
        } catch (_) {}
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = (input.value || '').trim();
        if (!content) return;

        sendBtn.disabled = true;
        try {
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ content })
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.message) {
                renderMessage(data.message);
                lastId = data.message.id;
                input.value = '';
                await markRead();
            }
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    });

    scrollToBottom();
    markRead();
    setInterval(poll, 3000);
});
</script>
@endpush
