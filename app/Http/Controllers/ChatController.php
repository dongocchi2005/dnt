<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\Chat\ChatOrchestrator;
use App\Models\User;
use App\Notifications\NewChatSessionOrMessage;
use App\Events\ChatMessageCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function startSession(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        $session = ChatSession::create([
            'session_id'      => (string) Str::uuid(),
            'user_id'         => auth()->id(),
            'guest_name'      => $request->name,
            'guest_email'     => $request->email,
            'guest_phone'     => $request->phone,
            'status'          => 'pending',
            'last_activity'   => now(),
            'last_message_at' => now(),
            'last_handled_by' => 'bot'
        ]);

        $welcomeMessage = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role'            => 'assistant',
            'content'         => 'Xin chào! Tôi là DNT Assistant. Tôi có thể giúp bạn với thông tin về dịch vụ sửa chữa, đặt lịch, sản phẩm, hoặc đơn hàng. Bạn cần hỗ trợ gì nào?',
        ]);

        event(new ChatMessageCreated($welcomeMessage));

        return response()->json([
            'success'        => true,
            'session_id'     => $session->session_id,
            'session_status' => $session->status,
            'reply_text'     => $welcomeMessage->content,
            'intent'         => 'welcome',
            'slots'          => [],
            'meta'           => ['follow_up_needed' => false, 'missing_slots' => []],
            'messages'       => [
                [
                    'id'         => $welcomeMessage->id,
                    'role'       => $welcomeMessage->role,
                    'content'    => $welcomeMessage->content,
                    'created_at' => $welcomeMessage->created_at->toISOString()
                ]
            ]
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $startedAt = microtime(true);
        $requestId = (string) Str::uuid();

        // Hỗ trợ client gửi "message" hoặc "content"
        $inputContent = $request->input('content') ?? $request->input('message');
        $request->merge(['content' => $inputContent]);

        $request->validate([
            'session_id'       => 'required|string|exists:chat_sessions,session_id',
            'content'          => 'required|string|max:2000',
            'idempotency_key'  => 'nullable|string|max:255'
        ]);

        /** @var ChatSession $session */
        $session = ChatSession::where('session_id', $request->session_id)->firstOrFail();

        // Idempotency: nếu đã có user message với key này, trả lại user + assistant (nếu có) để frontend không bị treo
        if ($request->idempotency_key) {
            $existingUserMessage = ChatMessage::where('chat_session_id', $session->id)
                ->where('role', 'user')
                ->where('idempotency_key', $request->idempotency_key)
                ->first();

            if ($existingUserMessage) {
                $assistant = $this->findAssistantReplyAfter($session, $existingUserMessage);

                return response()->json([
                    'success'   => true,
                    'message'   => 'Message already processed',
                    'reply_text'=> $assistant?->content,
                    'meta'      => [
                        'deduped'   => true,
                        'has_reply' => (bool) $assistant,
                    ],
                    'messages'  => array_values(array_filter([
                        [
                            'id'         => $existingUserMessage->id,
                            'role'       => $existingUserMessage->role,
                            'content'    => $existingUserMessage->content,
                            'created_at' => $existingUserMessage->created_at?->toISOString(),
                        ],
                        $assistant ? [
                            'id'         => $assistant->id,
                            'role'       => $assistant->role,
                            'content'    => $assistant->content,
                            'created_at' => $assistant->created_at?->toISOString(),
                        ] : null,
                    ])),
                ])->withHeaders([
                    'X-DNT-Request-Id' => $requestId,
                ]);
            }
        }

        if (!$session->user_id && auth()->check()) {
            $session->update(['user_id' => auth()->id()]);
        }

        if (method_exists($session, 'isClosed') && $session->isClosed()) {
            return response()->json([
                'success' => false,
                'code' => 'CHAT_SESSION_CLOSED',
                'message' => 'Phiên chat đã kết thúc.'
            ], 409);
        }

        // Lưu message của user (có bắt duplicate bằng unique index)
        $wasDuplicate = false;
        $userMessage = $this->createUserMessage($session, $request->content, $request->idempotency_key, $wasDuplicate);

        if ($wasDuplicate) {
            $assistant = $this->findAssistantReplyAfter($session, $userMessage);

            logger()->info('[DNT Chat] sendMessage duplicate', [
                'request_id'       => $requestId,
                'session_id'       => $session->session_id,
                'idempotency_key'  => $request->idempotency_key,
                'duration_ms'      => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Message already processed',
                'reply_text' => $assistant?->content,
                'meta'       => [
                    'deduped'   => true,
                    'has_reply' => (bool) $assistant,
                ],
                'session'    => [
                    'status' => $session->status,
                    'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                    'pending_close_at' => $session->pending_close_at?->toISOString(),
                ],
                'messages'   => array_values(array_filter([
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at?->toISOString(),
                    ],
                    $assistant ? [
                        'id'         => $assistant->id,
                        'role'       => $assistant->role,
                        'content'    => $assistant->content,
                        'created_at' => $assistant->created_at?->toISOString(),
                    ] : null,
                ])),
            ])->withHeaders([
                'X-DNT-Request-Id' => $requestId,
            ]);
        }

        event(new ChatMessageCreated($userMessage));

        $session->update([
            'last_activity'   => now(),
            'last_message_at' => now(),
            'last_customer_message_at' => now(),
            'waiting_customer_reply' => false,
            'pending_close_at' => null,
            'pending_close_reason' => null,
        ]);

        if (method_exists($session, 'shouldSuppressBot') && $session->shouldSuppressBot()) {
            $this->notifyAdminsOnPendingMessage($session, $userMessage);

            return response()->json([
                'success'    => true,
                'message'    => 'Phiên chat đang được nhân viên hỗ trợ.',
                'reply_text' => 'Phiên chat đang được nhân viên hỗ trợ. Vui lòng chờ phản hồi từ nhân viên.',
                'meta'       => [
                    'bot_disabled'   => true,
                    'session_status' => $session->status,
                ],
                'session'    => [
                    'status' => $session->status,
                    'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                    'pending_close_at' => $session->pending_close_at?->toISOString(),
                ],
                'messages'   => [
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at->toISOString(),
                    ]
                ]
            ]);
        }

        // Set title cho session nếu chưa có
        if (empty($session->title)) {
            $session->update(['title' => Str::limit($request->content, 60)]);
        }

        // Build history (limit để tránh chậm/timeout)
        $history = $session->messages()
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        $orchestrator = app(ChatOrchestrator::class);

        try {
            $result = $orchestrator->handle($session, $request->content, $history);
        } catch (\Throwable $e) {
            logger()->error('[DNT Chat] Orchestrator failed', [
                'session_id' => $session->session_id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            $assistantFallback = ChatMessage::create([
                'chat_session_id' => $session->id,
                'role'            => 'assistant',
                'content'         => 'Xin lỗi, hệ thống gặp lỗi xử lý. Vui lòng thử lại.',
            ]);

            event(new ChatMessageCreated($assistantFallback));

            $session->update([
                'last_activity'   => now(),
                'last_message_at' => now(),
                'last_handled_by' => 'bot',
            ]);

            $this->notifyAdminsOnPendingMessage($session, $userMessage);

            return response()->json([
                'success'    => false,
                'message'    => $assistantFallback->content,
                'reply_text' => $assistantFallback->content,
                'intent'     => 'system_error',
                'slots'      => [],
                'meta'       => ['follow_up_needed' => false, 'missing_slots' => []],
                'messages'   => [
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at->toISOString()
                    ],
                    [
                        'id'         => $assistantFallback->id,
                        'role'       => $assistantFallback->role,
                        'content'    => $assistantFallback->content,
                        'created_at' => $assistantFallback->created_at->toISOString()
                    ]
                ]
            ], 500)->withHeaders([
                'X-DNT-Request-Id' => $requestId,
            ]);
        }

        $replyText = $result['reply_text'] ?? null;
        $intent    = $result['intent'] ?? null;
        $slots     = $result['slots'] ?? [];
        $products  = $result['products'] ?? [];
        $meta      = $result['meta'] ?? ['follow_up_needed' => false, 'missing_slots' => []];

        $assistantMessage = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role'            => 'assistant',
            'content'         => $replyText ?: 'Mình chưa trả lời được lúc này.',
        ]);

        event(new ChatMessageCreated($assistantMessage));

        $session->update([
            'last_activity'   => now(),
            'last_message_at' => now(),
            'last_handled_by' => ($intent === 'handover_admin') ? 'admin' : 'bot',
        ]);

        $this->notifyAdminsOnPendingMessage($session, $userMessage);

        logger()->info('[DNT Chat] sendMessage done', [
            'request_id'  => $requestId,
            'session_id'  => $session->session_id,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return response()->json([
            'success'     => true,
            'intent'      => $intent,
            'slots'       => $slots,
            'next_action' => null,
            'reply_text'  => $assistantMessage->content,
            'meta'        => $meta,
            'products'    => $products,
            'session'     => [
                'status' => $session->status,
                'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                'pending_close_at' => $session->pending_close_at?->toISOString(),
            ],
            'messages'    => [
                [
                    'id'         => $userMessage->id,
                    'role'       => $userMessage->role,
                    'content'    => $userMessage->content,
                    'created_at' => $userMessage->created_at->toISOString()
                ],
                [
                    'id'         => $assistantMessage->id,
                    'role'       => $assistantMessage->role,
                    'content'    => $assistantMessage->content,
                    'created_at' => $assistantMessage->created_at->toISOString()
                ]
            ]
        ])->withHeaders([
            'X-DNT-Request-Id' => $requestId,
        ]);
    }

    public function getMessages(Request $request, ?string $sessionId = null): JsonResponse
    {
        $sessionId = $sessionId ?? $request->input('session_id');
        $request->merge(['session_id' => $sessionId]);

        $request->validate([
            'session_id' => 'required|string|exists:chat_sessions,session_id'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)->firstOrFail();

        // Include admin messages if the session is assigned
        $allowedRoles = ['user', 'assistant'];
        if ($session->status === 'assigned') {
            $allowedRoles[] = 'admin';
        }

        $messages = $session->messages()
            ->whereIn('role', $allowedRoles)
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id'         => $message->id,
                    'role'       => $message->role,
                    'content'    => $message->content,
                    'meta'       => $message->meta,
                    'created_at' => $message->created_at->toISOString()
                ];
            });

        return response()->json([
            'success'        => true,
            'session_status' => $session->status,
            'messages'       => $messages
        ]);
    }

    public function handoverToAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string|exists:chat_sessions,session_id'
        ]);

        $session = ChatSession::where('session_id', $request->session_id)->firstOrFail();

        if (method_exists($session, 'markHandedOver')) {
            $session->markHandedOver();
        } else {
            // fallback nếu chưa có method
            $session->update(['status' => 'pending', 'last_handled_by' => 'admin']);
        }

        $this->notifyAdmins($session, $request->input('message'));

        return response()->json([
            'success' => true,
            'message' => 'Đã chuyển phiên chat cho admin. Admin sẽ liên hệ với bạn sớm nhất có thể.'
        ]);
    }

    private function notifyAdminsOnPendingMessage(ChatSession $session, ChatMessage $message): void
    {
        // Notify if session is pending (not assigned yet)
        if (method_exists($session, 'isPending') && $session->isPending() && !$session->assigned_admin_id) {
            $this->notifyAdmins($session, $message->content);
            return;
        }

        // Notify assigned admin if session is assigned
        if ($session->assigned_admin_id && ($session->status === 'assigned' || $session->last_handled_by === 'admin')) {
            $assignedAdmin = User::find($session->assigned_admin_id);
            if ($assignedAdmin && $assignedAdmin->is_admin) {
                try {
                    $assignedAdmin->notify(new NewChatSessionOrMessage($session, $message->content));
                } catch (\Throwable $e) {
                    // ignore notification errors
                }
            }
        }
    }

    private function notifyAdmins(ChatSession $session, ?string $message = null): void
    {
        try {
            $admins = User::query()->where('is_admin', true)->get();
            if ($admins->isEmpty()) {
                return;
            }
            Notification::send($admins, new NewChatSessionOrMessage($session, $message));
        } catch (\Throwable $e) {
            // ignore notification errors
        }
    }

    private function createUserMessage(ChatSession $session, string $content, ?string $idempotencyKey, bool &$wasDuplicate = false): ChatMessage
    {
        $wasDuplicate = false;

        if (!$idempotencyKey) {
            return ChatMessage::create([
                'chat_session_id'  => $session->id,
                'role'             => 'user',
                'content'          => $content,
                'idempotency_key'  => null,
            ]);
        }

        try {
            return ChatMessage::create([
                'chat_session_id'  => $session->id,
                'role'             => 'user',
                'content'          => $content,
                'idempotency_key'  => $idempotencyKey,
            ]);
        } catch (QueryException $e) {
            $errorInfo  = $e->errorInfo ?? [];
            $sqlState   = $errorInfo[0] ?? null;
            $driverCode = $errorInfo[1] ?? null;

            // Duplicate key
            if ($sqlState === '23000' || $driverCode === 1062) {
                $existing = ChatMessage::where('chat_session_id', $session->id)
                    ->where('role', 'user')
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    $wasDuplicate = true;
                    return $existing;
                }
            }

            throw $e;
        }
    }

    /**
     * Trả về assistant message đầu tiên sau user message (nếu có).
     * Dùng để idempotency trả đủ reply_text, tránh frontend bị "đang gửi mãi".
     */
    private function findAssistantReplyAfter(ChatSession $session, ChatMessage $userMessage): ?ChatMessage
    {
        return ChatMessage::where('chat_session_id', $session->id)
            ->where('role', 'assistant')
            ->where('id', '>', $userMessage->id)
            ->orderBy('id')
            ->first();
    }

    // New polling API methods for chat widget

    public function getSessionMeta(Request $request, ChatSession $session): JsonResponse
    {
        return response()->json([
            'session_id'          => $session->session_id,
            'status'              => $session->status,
            'assigned_admin_id'   => $session->assigned_admin_id,
            'assigned_admin_name' => $session->assignedAdmin?->name,
            'last_handled_by'     => $session->last_handled_by,
        ]);
    }

    public function sessionStatus(Request $request): JsonResponse
    {
        $request->validate([
            'session_key' => 'required|string|exists:chat_sessions,session_id',
        ]);

        $session = ChatSession::where('session_id', $request->query('session_key'))->firstOrFail();

        $status = (method_exists($session, 'isClosed') && $session->isClosed()) ? 'closed' : 'active';

        return response()->json([
            'success' => true,
            'status' => $status,
            'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
            'pending_close_at' => $session->pending_close_at?->toISOString(),
            'server_now' => now()->toISOString(),
        ]);
    }

    public function pollMessages(Request $request, ChatSession $session): JsonResponse
    {
        $since = (int) $request->query('since', 0);

        // Return ALL message roles: user, admin, assistant, system
        $query = $session->messages()
            ->when($since > 0, fn ($q) => $q->where('id', '>', $since))
            ->orderBy('id');

        $messages = $query->get()->map(function ($message) {
            return [
                'id'              => $message->id,
                'role'            => $message->role,
                'content'         => $message->content,
                'meta'            => $message->meta,
                'idempotency_key' => $message->idempotency_key,
                'created_at'      => $message->created_at?->toISOString(),
            ];
        });

        $lastId = $session->messages()->max('id') ?? 0;

        return response()->json([
            'messages'       => $messages,
            'session_status' => $session->status,
            'last_id'        => $lastId,
        ]);
    }

    public function sendUserMessage(Request $request, ChatSession $session): JsonResponse
    {
        $startedAt = microtime(true);
        $requestId = (string) Str::uuid();

        $request->validate([
            'content'         => 'required|string|max:2000',
            'idempotency_key' => 'nullable|string|max:255'
        ]);

        // Idempotency: trả lại user + assistant (nếu có)
        if ($request->idempotency_key) {
            $existingUserMessage = ChatMessage::where('chat_session_id', $session->id)
                ->where('role', 'user')
                ->where('idempotency_key', $request->idempotency_key)
                ->first();

            if ($existingUserMessage) {
                $assistant = $this->findAssistantReplyAfter($session, $existingUserMessage);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Message already processed',
                    'reply_text' => $assistant?->content,
                    'meta'       => [
                        'deduped'   => true,
                        'has_reply' => (bool) $assistant,
                    ],
                    'messages'   => array_values(array_filter([
                        [
                            'id'         => $existingUserMessage->id,
                            'role'       => $existingUserMessage->role,
                            'content'    => $existingUserMessage->content,
                            'created_at' => $existingUserMessage->created_at?->toISOString(),
                        ],
                        $assistant ? [
                            'id'         => $assistant->id,
                            'role'       => $assistant->role,
                            'content'    => $assistant->content,
                            'created_at' => $assistant->created_at?->toISOString(),
                        ] : null,
                    ])),
                ])->withHeaders([
                    'X-DNT-Request-Id' => $requestId,
                ]);
            }
        }

        if (!$session->user_id && auth()->check()) {
            $user = auth()->user();
            if ($user) {
                $session->update(['user_id' => $user->id]);
            }
        }

        if (method_exists($session, 'isClosed') && $session->isClosed()) {
            return response()->json([
                'success' => false,
                'code' => 'CHAT_SESSION_CLOSED',
                'message' => 'Phiên chat đã kết thúc.'
            ], 409);
        }

        // Save user message
        $wasDuplicate = false;
        $userMessage = $this->createUserMessage($session, $request->content, $request->idempotency_key, $wasDuplicate);

        if ($wasDuplicate) {
            $assistant = $this->findAssistantReplyAfter($session, $userMessage);

            logger()->info('[DNT Chat] sendUserMessage duplicate', [
                'request_id'      => $requestId,
                'session_id'      => $session->session_id,
                'idempotency_key' => $request->idempotency_key,
                'duration_ms'     => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Message already processed',
                'reply_text' => $assistant?->content,
                'meta'       => [
                    'deduped'   => true,
                    'has_reply' => (bool) $assistant,
                ],
                'session'    => [
                    'status' => $session->status,
                    'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                    'pending_close_at' => $session->pending_close_at?->toISOString(),
                ],
                'messages'   => array_values(array_filter([
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at?->toISOString(),
                    ],
                    $assistant ? [
                        'id'         => $assistant->id,
                        'role'       => $assistant->role,
                        'content'    => $assistant->content,
                        'created_at' => $assistant->created_at?->toISOString(),
                    ] : null,
                ])),
            ])->withHeaders([
                'X-DNT-Request-Id' => $requestId,
            ]);
        }

        event(new ChatMessageCreated($userMessage));

        $session->update([
            'last_activity'   => now(),
            'last_message_at' => now(),
            'last_customer_message_at' => now(),
            'waiting_customer_reply' => false,
            'pending_close_at' => null,
            'pending_close_reason' => null,
        ]);

        // If session is assigned, don't call AI, just notify admin
        if (method_exists($session, 'shouldSuppressBot') && $session->shouldSuppressBot()) {
            $this->notifyAdminsOnPendingMessage($session, $userMessage);

            logger()->info('[DNT Chat] sendUserMessage done', [
                'request_id'  => $requestId,
                'session_id'  => $session->session_id,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Tin nhắn đã được gửi.',
                'session'  => [
                    'status' => $session->status,
                    'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                    'pending_close_at' => $session->pending_close_at?->toISOString(),
                ],
                'messages' => [
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at->toISOString(),
                    ]
                ]
            ])->withHeaders([
                'X-DNT-Request-Id' => $requestId,
            ]);
        }

        // If pending, proceed with AI as before
        if (empty($session->title)) {
            $session->update(['title' => Str::limit($request->content, 60)]);
        }

        // Build history (limit để tránh chậm/timeout)
        $history = $session->messages()
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->sortBy('id')
            ->values()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        $orchestrator = app(ChatOrchestrator::class);

        try {
            $result = $orchestrator->handle($session, $request->content, $history);
        } catch (\Throwable $e) {
            logger()->error('[DNT Chat] Orchestrator failed', [
                'session_id' => $session->session_id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            $assistantFallback = ChatMessage::create([
                'chat_session_id' => $session->id,
                'role'            => 'assistant',
                'content'         => 'Xin lỗi, hệ thống gặp lỗi xử lý. Vui lòng thử lại.',
            ]);

            event(new ChatMessageCreated($assistantFallback));

            $session->update([
                'last_activity'   => now(),
                'last_message_at' => now(),
                'last_handled_by' => 'bot',
            ]);

            $this->notifyAdminsOnPendingMessage($session, $userMessage);

            return response()->json([
                'success'    => false,
                'message'    => $assistantFallback->content,
                'reply_text' => $assistantFallback->content,
                'intent'     => 'system_error',
                'slots'      => [],
                'meta'       => ['follow_up_needed' => false, 'missing_slots' => []],
                'messages'   => [
                    [
                        'id'         => $userMessage->id,
                        'role'       => $userMessage->role,
                        'content'    => $userMessage->content,
                        'created_at' => $userMessage->created_at->toISOString()
                    ],
                    [
                        'id'         => $assistantFallback->id,
                        'role'       => $assistantFallback->role,
                        'content'    => $assistantFallback->content,
                        'created_at' => $assistantFallback->created_at->toISOString()
                    ]
                ]
            ], 500)->withHeaders([
                'X-DNT-Request-Id' => $requestId,
            ]);
        }

        $replyText = $result['reply_text'] ?? null;
        $intent    = $result['intent'] ?? null;
        $slots     = $result['slots'] ?? [];
        $products  = $result['products'] ?? [];
        $meta      = $result['meta'] ?? ['follow_up_needed' => false, 'missing_slots' => []];

        $assistantMessage = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role'            => 'assistant',
            'content'         => $replyText ?: 'Mình chưa trả lời được lúc này.',
        ]);

        event(new ChatMessageCreated($assistantMessage));

        $session->update([
            'last_activity'   => now(),
            'last_message_at' => now(),
            'last_handled_by' => ($intent === 'handover_admin') ? 'admin' : 'bot',
        ]);

        $this->notifyAdminsOnPendingMessage($session, $userMessage);

        logger()->info('[DNT Chat] sendUserMessage done', [
            'request_id'  => $requestId,
            'session_id'  => $session->session_id,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);

        return response()->json([
            'success'     => true,
            'intent'      => $intent,
            'slots'       => $slots,
            'next_action' => null,
            'reply_text'  => $assistantMessage->content,
            'meta'        => $meta,
            'products'    => $products,
            'session'     => [
                'status' => $session->status,
                'waiting_customer_reply' => (bool) ($session->waiting_customer_reply ?? false),
                'pending_close_at' => $session->pending_close_at?->toISOString(),
            ],
            'messages'    => [
                [
                    'id'         => $userMessage->id,
                    'role'       => $userMessage->role,
                    'content'    => $userMessage->content,
                    'created_at' => $userMessage->created_at->toISOString()
                ],
                [
                    'id'         => $assistantMessage->id,
                    'role'       => $assistantMessage->role,
                    'content'    => $assistantMessage->content,
                    'created_at' => $assistantMessage->created_at->toISOString()
                ]
            ]
        ])->withHeaders([
            'X-DNT-Request-Id' => $requestId,
        ]);
    }
}
