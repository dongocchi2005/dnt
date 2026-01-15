<?php

    namespace App\Http\Controllers\Api;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Log;
    use App\Services\GeminiChatService;
    use App\Services\Chat\ReplyBuilder;
    use App\Services\Chat\IntentDetector;

    class ChatController extends Controller
    {
        public function send(Request $request): JsonResponse
        {
            try {
                $request->validate([
                    'message' => 'required|string|max:1000'
                ]);

                $service = app(GeminiChatService::class);
                $response = $service->respond([], $request->message);

                if (!$response) {
                    return response()->json([
                        'reply_text' => 'Xin lỗi, tôi đang gặp sự cố kỹ thuật. Vui lòng thử lại sau.',
                        'intent' => 'system_error',
                        'slots' => [],
                        'meta' => ['follow_up_needed' => false, 'missing_slots' => []],
                    ], 200);
                }

                $intent = null;
                $slots = [];
                $parsed = json_decode($response, true);
                if (is_array($parsed) && (isset($parsed['intent']) || isset($parsed['slots']))) {
                    $intent = $parsed['intent'] ?? null;
                    $slots = is_array($parsed['slots'] ?? null) ? $parsed['slots'] : [];
                }

                $detector = app(IntentDetector::class);
                $intent = $detector->normalizeIntent($intent);
                $fallback = $detector->detect($request->message, []);
                if (!$intent && !empty($fallback['intent'])) {
                    $intent = $fallback['intent'];
                }
                if (!empty($fallback['slots'])) {
                    $slots = $detector->mergeSlots($slots, $fallback['slots']);
                }

                $replyBuilder = app(ReplyBuilder::class);
                $reply = $replyBuilder->build($intent, $slots, $request->message);

                Log::info('Chat API intent/slots', [
                    'intent' => $intent,
                    'slots' => $slots,
                ]);

                return response()->json([
                    'reply_text' => $reply['reply_text'] ?? 'Mình chưa trả lời được lúc này.',
                    'intent' => $intent,
                    'slots' => $slots,
                    'meta' => [
                        'follow_up_needed' => $reply['follow_up_needed'] ?? false,
                        'missing_slots' => $reply['missing_slots'] ?? [],
                    ],
                ]);

            } catch (\Exception $e) {
                Log::error('Chat API Error: ' . $e->getMessage());

                return response()->json([
                    'reply_text' => 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.',
                    'intent' => 'system_error',
                    'slots' => [],
                    'meta' => ['follow_up_needed' => false, 'missing_slots' => []],
                ], 500);
            }
        }
    }
