@extends('layouts.admin')

@section('title', 'Chat Analytics - DNT Store')
@section('page-title', 'Chat Analytics')

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <span class="inline-block w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_18px_rgba(34,211,238,.7)] animate-pulse"></span>
            <h1 class="text-2xl font-bold text-bl font-display neon">Chat Analytics</h1>
        </div>
        <p class="text-bl/60 mt-2">Theo dõi hiệu quả và hành vi người dùng trong chat.</p>
    </div>

    @if(!$conversionEnabled || !$intentsEnabled || !$toolsEnabled)
        <div class="mb-6 rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-4 text-sm text-yellow-400 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                Một số dữ liệu chưa hiển thị vì thiếu migration:
                @if(!$conversionEnabled) <span class="font-bold">conversion_type</span>@endif
                @if(!$intentsEnabled) <span class="font-bold">last_intent</span>@endif
                @if(!$toolsEnabled) <span class="font-bold">chat_tools_log</span>@endif
                . Vui lòng chạy `php artisan migrate`.
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="cyber-panel p-6">
            <div class="text-sm text-bl/60">Tổng phiên chat</div>
            <div class="text-3xl font-bold text-bl neon mt-2">{{ $totalSessions }}</div>
        </div>
        <div class="cyber-panel p-6">
            <div class="text-sm text-bl/60">Phiên chuyển đổi</div>
            <div class="text-3xl font-bold text-emerald-400 neon mt-2">{{ $convertedSessions }}</div>
        </div>
        <div class="cyber-panel p-6">
            <div class="text-sm text-bl/60">Tỉ lệ chuyển đổi</div>
            <div class="text-3xl font-bold text-blue-400 neon mt-2">{{ $conversionRate }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="cyber-panel p-6">
            <h3 class="text-lg font-bold text-bl mb-4 flex items-center gap-2">
                <i class="fa-solid fa-brain text-purple-400"></i> Top intents
            </h3>
            <div class="space-y-3">
                @forelse($topIntents as $intent)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                        <span class="text-bl/80">{{ $intent->last_intent ?? 'unknown' }}</span>
                        <span class="font-bold text-purple-400">{{ $intent->total }}</span>
                    </div>
                @empty
                    <div class="text-bl/40 italic text-center py-4">Chưa có dữ liệu.</div>
                @endforelse
            </div>
        </div>

        <div class="cyber-panel p-6">
            <h3 class="text-lg font-bold text-bl mb-4 flex items-center gap-2">
                <i class="fa-solid fa-toolbox text-blue-400"></i> Top tool calls
            </h3>
            <div class="space-y-3">
                @forelse($toolCounts as $tool)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                        <span class="text-bl/80">{{ $tool->type }}</span>
                        <span class="font-bold text-blue-400">{{ $tool->total }}</span>
                    </div>
                @empty
                    <div class="text-bl/40 italic text-center py-4">Chưa có dữ liệu.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="cyber-panel p-6 mt-6 overflow-hidden">
        <h3 class="text-lg font-bold text-bl mb-4">Phiên chat gần đây</h3>
        <div class="admin-table-mobile-hide overflow-x-auto hidden md:block">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Session</th>
                        <th class="px-4 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Last Intent</th>
                        <th class="px-4 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Conversion</th>
                        <th class="px-4 py-3 text-right font-bold text-bl/50 uppercase tracking-wider">Last Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentSessions as $s)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3 font-mono text-bl/80">{{ $s->session_id }}</td>
                            <td class="px-4 py-3 text-bl/80">{{ $s->last_intent ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-bold bg-white/10 text-bl/80 border border-white/10">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-bl/80">{{ $s->conversion_type ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-bl/60">{{ optional($s->last_activity)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-bl/40 italic" colspan="5">Chưa có dữ liệu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-cards block md:hidden">
            @forelse($recentSessions as $s)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            {{ $s->session_id }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            {{ optional($s->last_activity)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Intent</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $s->last_intent ?? '-' }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Status</div>
                            <div class="admin-mobile-field__value">
                                <span class="px-2 py-1 rounded text-xs font-bold bg-white/10 text-bl/80 border border-white/10">
                                    {{ $s->status }}
                                </span>
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Conversion</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $s->conversion_type ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="admin-mobile-card">
                    <div class="text-center text-bl/40 italic">Chưa có dữ liệu.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
