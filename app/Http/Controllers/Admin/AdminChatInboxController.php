<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ChatSessionRead;
use App\Models\ChatToolLog;
use App\Events\ChatMessageCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AdminChatInboxController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $allowed = ['pending', 'assigned', 'closed'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $adminId = $request->user()->id;

        $sessionsQuery = ChatSession::query()
            ->with(['assignedAdmin', 'user'])
            ->withCount(['messages as unread_count' => function ($query) use ($adminId) {
                $query->where('role', 'user')
                    ->whereRaw(
                        'chat_messages.id > COALESCE((SELECT last_read_message_id FROM chat_session_reads WHERE chat_session_reads.chat_session_id = chat_sessions.id AND chat_session_reads.admin_id = ?), 0)',
                        [$adminId]
                    );
            }]);

        if ($status === 'pending') {
            $sessionsQuery->where('status', 'pending')->whereNull('assigned_admin_id');
        } elseif ($status === 'assigned') {
            $sessionsQuery->where('status', 'assigned');
        } else {
            $sessionsQuery->where('status', 'closed');
        }

        $sessions = $sessionsQuery
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => ChatSession::where('status', 'pending')->whereNull('assigned_admin_id')->count(),
            'assigned' => ChatSession::where('status', 'assigned')->count(),
            'closed' => ChatSession::where('status', 'closed')->count(),
        ];

        return view('admin.chat-inbox.index', compact('sessions', 'status', 'counts'));
    }

    public function show(Request $request, ChatSession $chatSession)
    {
        $chatSession->load(['assignedAdmin', 'user']);

        if ($request->wantsJson()) {
            $sinceId = (int) $request->query('since', 0);

            $messages = $chatSession->messages()
                ->when($sinceId > 0, fn ($q) => $q->where('id', '>', $sinceId))
                ->orderBy('id')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'role' => $message->role,
                        'content' => $message->content,
                        'meta' => $message->meta,
                        'created_at' => $message->created_at?->toISOString(),
                    ];
                });

            return response()->json([
                'messages' => $messages,
                'session' => [
                    'status' => $chatSession->status,
                    'assigned_admin_id' => $chatSession->assigned_admin_id,
                    'assigned_admin_name' => $chatSession->assignedAdmin?->name,
                    'last_handled_by' => $chatSession->last_handled_by,
                ],
            ]);
        }

        $messages = $chatSession->messages()->orderBy('id')->get();

        return view('admin.chat-inbox.show', compact('chatSession', 'messages'));
    }

    public function takeover(Request $request, ChatSession $chatSession)
    {
        $admin = $request->user();

        try {
            DB::transaction(function () use ($chatSession, $admin) {
                $session = ChatSession::where('id', $chatSession->id)->lockForUpdate()->firstOrFail();

                if ($session->status !== 'pending' && $session->status !== 'ai' || $session->assigned_admin_id) {
                    $name = $session->assignedAdmin?->name ?? 'admin khác';
                    throw new \RuntimeException("Phiên đã được tiếp nhận bởi {$name}.");
                }

                $session->assignToAdmin($admin->id);

                $systemMessage = ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'system',
                    'content' => 'Phiên chat đã được nhân viên tiếp nhận. Bạn đang được hỗ trợ.',
                ]);

                event(new ChatMessageCreated($systemMessage));

                $session->update([
                    'last_activity' => now(),
                    'last_message_at' => now(),
                    'last_handled_by' => 'admin',
                ]);

                ChatToolLog::create([
                    'chat_session_id' => $session->id,
                    'type' => 'admin_takeover',
                    'status' => 'success',
                    'input' => ['admin_id' => $admin->id],
                    'output' => ['assigned_admin_id' => $session->assigned_admin_id],
                    'meta' => ['assigned_at' => $session->assigned_at?->toISOString()],
                ]);
            });
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Đã tiếp nhận phiên chat.']);
        }

        return back()->with('success', 'Đã tiếp nhận phiên chat.');
    }

    public function release(Request $request, ChatSession $chatSession)
    {
        $admin = $request->user();

        try {
            DB::transaction(function () use ($chatSession, $admin) {
                $session = ChatSession::where('id', $chatSession->id)->lockForUpdate()->firstOrFail();

                if ($session->status !== 'assigned') {
                    return;
                }

                $session->update([
                    'status' => 'ai',
                    'assigned_admin_id' => null,
                    'last_handled_by' => 'bot',
                    'last_activity' => now(),
                ]);

                $systemMessage = ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'role' => 'system',
                    'content' => 'Nhân viên đã kết thúc hỗ trợ. Chatbot sẽ tiếp tục hỗ trợ bạn.',
                ]);

                event(new ChatMessageCreated($systemMessage));

                ChatToolLog::create([
                    'chat_session_id' => $session->id,
                    'type' => 'admin_release',
                    'status' => 'success',
                    'input' => ['admin_id' => $admin->id],
                    'output' => ['status' => 'ai'],
                ]);
            });
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Đã trả phiên chat về cho AI.']);
        }

        return back()->with('success', 'Đã trả phiên chat về cho AI.');
    }

    public function storeMessage(Request $request, ChatSession $chatSession): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $admin = $request->user();

        if (method_exists($chatSession, 'isClosed') && $chatSession->isClosed()) {
            return response()->json([
                'success' => false,
                'code' => 'CHAT_SESSION_CLOSED',
                'message' => 'Phiên chat đã kết thúc.',
            ], 409);
        }

        if ($chatSession->status !== 'assigned') {
            return response()->json(['message' => 'Vui lòng tiếp nhận phiên chat trước khi gửi tin.'], 400);
        }

        if ($chatSession->assigned_admin_id && $chatSession->assigned_admin_id !== $admin->id) {
            $name = $chatSession->assignedAdmin?->name ?? 'admin khác';
            return response()->json(['message' => "Phiên đã được tiếp nhận bởi {$name}."], 409);
        }

        $message = ChatMessage::create([
            'chat_session_id' => $chatSession->id,
            'role' => 'admin',
            'content' => $request->content,
        ]);

        event(new ChatMessageCreated($message));

        $timeoutSeconds = (int) config('chat.staff_reply_timeout_seconds', 900);
        $countdownSeconds = (int) config('chat.auto_close_countdown_seconds', 60);

        $chatSession->update([
            'last_activity' => now(),
            'last_message_at' => now(),
            'last_handled_by' => 'admin',
            'last_staff_message_at' => now(),
            'waiting_customer_reply' => true,
            'pending_close_at' => now()->addSeconds($timeoutSeconds + $countdownSeconds),
            'pending_close_reason' => 'NO_CUSTOMER_REPLY',
        ]);

        ChatSessionRead::updateOrCreate(
            ['chat_session_id' => $chatSession->id, 'admin_id' => $admin->id],
            ['last_read_message_id' => $message->id]
        );

        return response()->json([
            'message' => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at?->toISOString(),
            ],
            'session' => [
                'status' => $chatSession->status,
                'waiting_customer_reply' => (bool) ($chatSession->waiting_customer_reply ?? false),
                'pending_close_at' => $chatSession->pending_close_at?->toISOString(),
            ],
        ]);
    }

    public function markRead(Request $request, ChatSession $chatSession): JsonResponse
    {
        $adminId = $request->user()->id;
        $lastMessageId = (int) $request->input('last_message_id', 0);

        if (!$lastMessageId) {
            $lastMessageId = (int) $chatSession->messages()->max('id');
        }

        ChatSessionRead::updateOrCreate(
            ['chat_session_id' => $chatSession->id, 'admin_id' => $adminId],
            ['last_read_message_id' => $lastMessageId ?: null]
        );

        return response()->json(['ok' => true]);
    }
}
