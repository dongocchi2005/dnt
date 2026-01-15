<?php

namespace App\Services\Chat;

use App\Models\ChatSession;
use App\Models\Product;
use App\Services\ProductRecommenderService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ChatOrchestrator
{
    public function __construct(
        protected NluService $nlu,
        protected IntentDetector $detector,
        protected ReplyBuilder $replyBuilder,
        protected ToolLogger $logger,
        protected ProductRecommenderService $recommender,
        protected ProductComparisonService $comparison,
        protected OrderLookupService $orderLookup,
        protected BookingLookupService $bookingLookup,
        protected ServiceOrderLookupService $serviceOrderLookup,
        protected BookingCreateService $bookingCreate,
        protected KnowledgeBaseSearch $kbSearch
    ) {
    }

    public function handle(ChatSession $session, string $message, array $history = []): array
    {
        $context = is_array($session->context ?? null) ? $session->context : [];
        $pending = is_array($context['pending_action'] ?? null) ? $context['pending_action'] : null;

        // 0) Pending confirm flow
        if ($pending) {
            $confirm = $this->interpretConfirm($message);
            if ($confirm === true) {
                $result = $this->executePending($session, $pending);
                $context['pending_action'] = null;
                $session->update([
                    'context' => $context,
                    'last_intent' => $pending['intent'] ?? $session->last_intent,
                ]);
                return $result;
            }
            if ($confirm === false) {
                $context['pending_action'] = null;
                $session->update([
                    'context' => $context,
                    'last_intent' => $session->last_intent,
                ]);
                return [
                    'reply_text' => 'Ok, bạn muốn chỉnh lại thông tin nào?',
                    'intent' => 'clarify',
                    'slots' => [],
                    'meta' => ['follow_up_needed' => true, 'missing_slots' => []],
                    'products' => [],
                ];
            }
        }

        // 1) Product grounding (DB match) - chạy TRƯỚC NLU để tránh "AI không hiểu"
        // Nếu message giống tên sản phẩm/mô tả sản phẩm => ép intent về product_buy
        $grounded = $this->groundProductFromMessage($message);
        // $grounded = ['products' => Collection|array, 'slots' => array, 'confidence' => float]

        // 2) NLU
        $nlu = $this->nlu->analyze($history, $message);
        $intent = $this->detector->normalizeIntent($nlu['intent'] ?? null);
        $slots = is_array($nlu['slots'] ?? null) ? $nlu['slots'] : [];

        // 3) Keyword fallback detector
        $fallback = $this->detector->detect($message, $context);
        if (!$intent && !empty($fallback['intent'])) {
            $intent = $fallback['intent'];
        }
        if (!empty($fallback['slots'])) {
            $slots = $this->detector->mergeSlots($slots, $fallback['slots']);
        }

        // 4) Merge grounding result (ưu tiên DB match hơn NLU khi có sản phẩm)
        $groundedProducts = $grounded['products'] ?? [];
        $groundedSlots = is_array($grounded['slots'] ?? null) ? $grounded['slots'] : [];
        $groundedConfidence = (float)($grounded['confidence'] ?? 0);

        if (!empty($groundedProducts)) {
            // Nếu NLU không rõ intent hoặc intent không thuộc nhóm tra cứu/đặt lịch, ưu tiên product_buy
            $forceIntent = true;

            // Nếu NLU đã xác định rõ "order_lookup/booking_lookup/service_order_lookup" thì không override
            $noOverrideIntents = ['order_lookup', 'booking_lookup', 'service_order_lookup'];
            if ($intent && in_array($intent, $noOverrideIntents, true)) {
                $forceIntent = false;
            }

            // Nếu intent hiện tại là policy/handover mà groundedConfidence cao, vẫn nên ưu tiên sản phẩm
            // (vì user đang gõ tên sản phẩm)
            if ($forceIntent) {
                $intent = 'product_buy';
                $slots = $this->detector->mergeSlots($slots, $groundedSlots);
            }
        }

        $slots = $this->sanitizeSlots($slots);
        $intent = $this->inferIntentFromContext($intent, $session, $slots);

        // 5) merge context & persist
        $context = $this->mergeContext($context, $slots);
        $session->update([
            'last_intent' => $intent ?? $session->last_intent,
            'context' => $context,
        ]);

        $this->logger->log($session, 'nlu', [
            'message' => $message,
        ], [
            'intent' => $intent,
            'slots' => $slots,
            'confidence' => $nlu['confidence'] ?? null,
            'next_action' => $nlu['next_action'] ?? null,
            'grounding' => [
                'products_count' => is_countable($groundedProducts) ? count($groundedProducts) : 0,
                'confidence' => $groundedConfidence,
            ],
        ]);

        $data = [];
        $products = [];

        switch ($intent) {
            case 'product_buy':
                // Nếu grounding đã match được sản phẩm từ DB => dùng luôn, không recommend "mù"
                if (!empty($groundedProducts)) {
                    $products = is_array($groundedProducts) ? $groundedProducts : $groundedProducts->all();
                    $data['products'] = $products;

                    $this->logger->log($session, 'product_grounding', [
                        'query' => $message,
                        'slots' => $slots,
                    ], [
                        'count' => count($products),
                        'confidence' => $groundedConfidence,
                    ]);
                } else {
                    $products = $this->recommender->recommend($slots ?? []);
                    $data['products'] = $products;
                    $this->logger->log($session, 'product_recommend', $slots, ['count' => count($products)]);
                }
                break;

            case 'product_compare':
                $items = $slots['compare_items'] ?? [];
                if (!is_array($items) || !$items) {
                    $items = $this->extractCompareFallback($message);
                }
                $products = $this->comparison->compare($items ?: [$message], 3);
                $data['products'] = $products;
                $this->logger->log($session, 'product_compare', ['items' => $items], ['count' => count($products)]);
                break;

            case 'order_lookup':
                $orders = $this->orderLookup->lookup($slots['order_code'] ?? null, $slots['phone'] ?? null);
                $data['orders'] = $orders;
                $this->logger->log($session, 'order_lookup', $slots, ['count' => count($orders)]);
                break;

            case 'booking_lookup':
                $bookings = $this->bookingLookup->lookup($slots['booking_code'] ?? null, $slots['phone'] ?? null);
                $data['bookings'] = $bookings;
                $this->logger->log($session, 'booking_lookup', $slots, ['count' => count($bookings)]);
                break;

            case 'service_order_lookup':
                $serviceOrders = $this->serviceOrderLookup->lookup($slots['service_order_code'] ?? null, $slots['phone'] ?? null);
                $data['service_orders'] = $serviceOrders;
                $this->logger->log($session, 'service_order_lookup', $slots, ['count' => count($serviceOrders)]);
                break;

            case 'policy_info':
                $kb = $this->kbSearch->search($message);
                $data['kb'] = $kb;
                $this->logger->log($session, 'knowledge_search', ['query' => $message], ['count' => count($kb)]);
                break;

            case 'handover_admin':
                if (method_exists($session, 'markHandedOver')) {
                    $session->markHandedOver();
                } else {
                    $session->update(['status' => 'pending', 'last_handled_by' => 'admin']);
                }
                $session->update([
                    'conversion_type' => 'handover',
                    'converted_at' => Carbon::now(),
                ]);
                break;
        }

        $reply = $this->replyBuilder->build($intent, $slots, $message, $context, $data);

        if (!empty($reply['requires_confirm']) && !empty($reply['confirm_action'])) {
            $context['pending_action'] = [
                'intent' => $intent,
                'action' => $reply['confirm_action'],
                'payload' => $reply['confirm_payload'] ?? [],
            ];
            $session->update(['context' => $context]);
        }

        if (!empty($reply['follow_up_needed']) && !empty($reply['missing_slots'])) {
            $products = [];
        }

        $normalizedProducts = ProductPayloadNormalizer::normalizeProducts($products);

        return [
            'reply_text' => $reply['reply_text'] ?? 'Mình chưa trả lời được lúc này.',
            'intent' => $intent,
            'slots' => $slots,
            'meta' => [
                'follow_up_needed' => $reply['follow_up_needed'] ?? false,
                'missing_slots' => $reply['missing_slots'] ?? [],
                'requires_confirm' => $reply['requires_confirm'] ?? false,
            ],
            'products' => $normalizedProducts,
        ];
    }

    /**
     * PRODUCT GROUNDING:
     * - Nếu user gõ "quạt giá 400k" => parse budget + keyword
     * - Nếu user gõ "Quạt cầm tay Tubor JF168 ..." => match DB theo name/slug/sku
     * Return: ['products' => array, 'slots' => array, 'confidence' => float]
     */
  private function groundProductFromMessage(string $message): array
{
    $raw = trim($message);
    if ($raw === '' || mb_strlen($raw) < 3) {
        return ['products' => [], 'slots' => [], 'confidence' => 0];
    }

    $ascii = $this->normalizeText($raw);

    $slots = ['keywords' => $raw];

    $budget = $this->parseBudgetVnd($ascii);
    if ($budget !== null) {
        $slots['budget'] = $budget;
    }

    $tokens = $this->extractMeaningfulTokens($ascii);
    $slug = Str::slug($raw);

    $q = Product::query()
        ->where('is_active', 1)
        ->where(function ($qq) use ($raw, $ascii, $slug, $tokens) {
            // match direct
            $qq->where('name', 'LIKE', '%' . $raw . '%')
               ->orWhere('slug', 'LIKE', '%' . $slug . '%')
               ->orWhere('description', 'LIKE', '%' . $raw . '%')
               ->orWhere('category', 'LIKE', '%' . $raw . '%');

            // match ascii (bỏ dấu) - vẫn hữu ích khi data chứa dấu khác cách gõ
            $qq->orWhere('name', 'LIKE', '%' . $ascii . '%')
               ->orWhere('description', 'LIKE', '%' . $ascii . '%');

            // TOKEN OR (chỉ cần 1 token khớp), tránh câu dài bị “AND chết”
            if (!empty($tokens)) {
                $qq->orWhere(function ($qq2) use ($tokens) {
                    foreach ($tokens as $t) {
                        $qq2->orWhere('name', 'LIKE', '%' . $t . '%')
                            ->orWhere('slug', 'LIKE', '%' . $t . '%')
                            ->orWhere('description', 'LIKE', '%' . $t . '%')
                            ->orWhere('category', 'LIKE', '%' . $t . '%');
                    }
                });
            }
        });

    // budget filter: ưu tiên sản phẩm có sale_price <= budget
    if ($budget !== null) {
        $q->orderByRaw('CASE WHEN sale_price IS NULL THEN 1 ELSE 0 END')
          ->orderBy('sale_price', 'asc')
          ->where(function ($qq) use ($budget) {
              $qq->whereNotNull('sale_price')->where('sale_price', '<=', $budget)
                 ->orWhereNull('sale_price');
          });
    }

    $products = $q->limit(3)->get();

    if ($products->isEmpty()) {
        return ['products' => [], 'slots' => $slots, 'confidence' => 0];
    }

    $confidence = 0.75;
    if (preg_match('/\b[a-z]{1,4}\d{2,6}\b/u', $ascii)) $confidence = 0.9;

    return [
        'products' => $products->all(),
        'slots' => $slots,
        'confidence' => $confidence,
    ];
}


    private function executePending(ChatSession $session, array $pending): array
    {
        $action = $pending['action'] ?? null;
        $payload = is_array($pending['payload'] ?? null) ? $pending['payload'] : [];

        if ($action === 'booking_create') {
            $result = $this->bookingCreate->create($payload, $session->user_id);
            $this->logger->log($session, 'booking_create', $payload, $result, $result['status'] ?? 'error');

            if (($result['status'] ?? '') === 'created') {
                $session->update([
                    'conversion_type' => 'booking',
                    'converted_at' => Carbon::now(),
                ]);
                return [
                    'reply_text' => "Đặt lịch thành công! Mã đặt lịch #{$result['booking_id']}.",
                    'intent' => 'booking_create',
                    'slots' => $payload,
                    'meta' => ['follow_up_needed' => false, 'missing_slots' => []],
                    'products' => [],
                ];
            }

            if (($result['status'] ?? '') === 'require_login') {
                return [
                    'reply_text' => 'Vui lòng đăng nhập để tạo đặt lịch. Bạn có thể đăng nhập và gửi lại thông tin giúp mình nhé.',
                    'intent' => 'booking_create',
                    'slots' => $payload,
                    'meta' => ['follow_up_needed' => true, 'missing_slots' => []],
                    'products' => [],
                ];
            }

            return [
                'reply_text' => $result['message'] ?? 'Không thể tạo đặt lịch lúc này. Bạn vui lòng thử lại.',
                'intent' => 'booking_create',
                'slots' => $payload,
                'meta' => ['follow_up_needed' => true, 'missing_slots' => []],
                'products' => [],
            ];
        }

        return [
            'reply_text' => 'Mình chưa thể xử lý yêu cầu này.',
            'intent' => $pending['intent'] ?? 'unknown',
            'slots' => [],
            'meta' => ['follow_up_needed' => false, 'missing_slots' => []],
            'products' => [],
        ];
    }

    private function interpretConfirm(string $message): ?bool
    {
        $text = Str::lower(trim($message));
        $yes = ['ok', 'oke', 'okay', 'đồng ý', 'dong y', 'xác nhận', 'xac nhan', 'yes', 'y'];
        $no = ['không', 'khong', 'hủy', 'huy', 'cancel', 'no', 'không đồng ý', 'tu choi'];

        if (Str::contains($text, $yes)) return true;
        if (Str::contains($text, $no)) return false;
        return null;
    }

    private function mergeContext(array $context, array $slots): array
    {
        $keys = [
            'device', 'problem', 'service', 'product_type', 'brand', 'keywords', 'budget',
            'order_code', 'booking_code', 'service_order_code',
            'name', 'phone', 'email', 'location', 'branch', 'time', 'date',
        ];

        foreach ($keys as $k) {
            $newVal = $slots[$k] ?? null;
            if ($newVal !== null && $newVal !== '') {
                $context[$k] = $newVal;
            } elseif (!array_key_exists($k, $context)) {
                $context[$k] = null;
            }
        }

        return $context;
    }

    private function sanitizeSlots(array $slots): array
    {
        $clean = [];
        foreach ($slots as $k => $v) {
            if (is_string($v)) {
                $clean[$k] = trim($v);
            } else {
                $clean[$k] = $v;
            }
        }
        return $clean;
    }

    private function extractCompareFallback(string $message): array
    {
        $text = Str::lower($message);
        $parts = preg_split('/\b(vs|versus|so sánh|so sanh|với|va|và|,|;)\b/u', $text);
        return collect($parts)
            ->map(fn($p) => trim((string)$p))
            ->filter(fn($p) => $p !== '' && mb_strlen($p) > 2)
            ->values()
            ->all();
    }

    private function inferIntentFromContext(?string $intent, ChatSession $session, array $slots): ?string
    {
        if ($intent) {
            return $intent;
        }

        if (!$slots) {
            return null;
        }

        $last = $session->last_intent;
        $allowed = [
            'booking_create',
            'service_quote',
            'order_lookup',
            'booking_lookup',
            'service_order_lookup',
            'product_buy',
            'product_compare',
        ];

        return in_array($last, $allowed, true) ? $last : null;
    }

    // -------------------------
    // Helpers: normalize/budget/token/sku
    // -------------------------

    private function normalizeText(string $text): string
    {
        // ascii: bỏ dấu tiếng Việt để match ổn hơn
        $t = Str::of($text)->lower()->ascii()->toString();
        $t = preg_replace('/\s+/u', ' ', $t) ?? $t;
        return trim($t);
    }

    private function extractTokens(string $ascii): array
    {
        $tokens = preg_split('/[^a-z0-9]+/u', $ascii) ?: [];
        $tokens = array_values(array_filter($tokens, fn($t) => $t !== '' && mb_strlen($t) >= 3));
        // giới hạn để query không quá nặng
        return array_slice($tokens, 0, 6);
    }

    private function extractSkuCandidate(string $ascii): ?string
    {
        // ví dụ: jf168, t700, pro30... (chữ + số)
        if (preg_match('/\b([a-z]{1,4}\d{2,6})\b/u', $ascii, $m)) {
            return $m[1];
        }
        return null;
    }

    private function parseBudgetVnd(string $ascii): ?int
    {
        // 400k, 400.000, 400000
        if (preg_match('/\b(\d{2,3})(?:\s?)(k|000)\b/u', $ascii, $m)) {
            $n = (int)$m[1];
            return $n * 1000;
        }

        if (preg_match('/\b(\d{1,3})\.(\d{3})\b/u', $ascii, $m)) {
            // 400.000
            return ((int)$m[1]) * 1000 + (int)$m[2];
        }

        if (preg_match('/\b(\d{5,9})\b/u', $ascii, $m)) {
            // 400000
            $v = (int)$m[1];
            // filter số quá nhỏ
            if ($v >= 50000) return $v;
        }

        // 0.4tr, 1tr2
        if (preg_match('/\b(\d+(?:\.\d+)?)\s?tr\b/u', $ascii, $m)) {
            $f = (float)$m[1];
            return (int)round($f * 1000000);
        }
        if (preg_match('/\b(\d{1,2})tr(\d{1,3})\b/u', $ascii, $m)) {
            // 1tr2 => 1.200.000
            return ((int)$m[1]) * 1000000 + ((int)$m[2]) * 1000;
        }

        return null;
    }
    private function extractMeaningfulTokens(string $ascii): array
{
    $stop = [
        'toi','minh','ban','muon','can','tim','kiem','mua','lay','chon',
        'gia','duoi','tren','khoang','tam','loai','hang','nao','gi',
        'cho','voi','nhe','a','ah','uh','ok','oke','okay'
    ];

    $tokens = preg_split('/[^a-z0-9]+/u', $ascii) ?: [];
    $tokens = array_map('trim', $tokens);

    $tokens = array_values(array_filter($tokens, function ($t) use ($stop) {
        if ($t === '' || mb_strlen($t) < 3) return false;
        if (in_array($t, $stop, true)) return false;
        return true;
    }));

    // giới hạn cho nhẹ query
    return array_slice($tokens, 0, 6);
}

}
