<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatToolLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChatAnalyticsController extends Controller
{
    public function index()
    {
        $totalSessions = ChatSession::count();
        $conversionEnabled = Schema::hasColumn('chat_sessions', 'conversion_type');
        $convertedSessions = $conversionEnabled
            ? ChatSession::whereNotNull('conversion_type')->count()
            : 0;
        $conversionRate = $totalSessions > 0
            ? round(($convertedSessions / $totalSessions) * 100, 1)
            : 0;

        $intentsEnabled = Schema::hasColumn('chat_sessions', 'last_intent');
        $topIntents = $intentsEnabled
            ? ChatSession::select('last_intent', DB::raw('count(*) as total'))
                ->groupBy('last_intent')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
            : collect();

        $toolsEnabled = Schema::hasTable('chat_tools_log');
        $toolCounts = $toolsEnabled
            ? ChatToolLog::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->orderByDesc('total')
                ->limit(8)
                ->get()
            : collect();

        $recentSessions = ChatSession::latest()->limit(10)->get();

        return view('admin.chat-analytics.index', compact(
            'totalSessions',
            'convertedSessions',
            'conversionRate',
            'topIntents',
            'toolCounts',
            'recentSessions',
            'conversionEnabled',
            'intentsEnabled',
            'toolsEnabled'
        ));
    }
}
