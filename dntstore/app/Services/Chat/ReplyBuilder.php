<?php

namespace App\Services\Chat;

use Illuminate\Support\Str;

class ReplyBuilder
{
    public function build(?string $intent, array $slots, string $userMessage, array $context = [], array $data = []): array
    {
        $intent = $intent ?: 'unknown';
        $slots = $this->mergeSlots($context, $slots);

        return match ($intent) {
            'store_info' => $this->replyStoreInfo(),
            'booking_create' => $this->replyBookingCreate($slots),
            'service_quote' => $this->replyServiceQuote($slots),
            'product_buy' => $this->replyProductBuy($slots, $data),
            'product_compare' => $this->replyProductCompare($data),
            'order_lookup' => $this->replyOrderLookup($slots, $data),
            'booking_lookup' => $this->replyBookingLookup($slots, $data),
            'service_order_lookup' => $this->replyServiceOrderLookup($slots, $data),
            'policy_info' => $this->replyPolicyInfo($data),
            'handover_admin' => $this->replyHandover(),
            'smalltalk' => $this->replySmalltalk(),
            default => $this->replyDefault(),
        };
    }

    private function mergeSlots(array $context, array $slots): array
    {
        $merged = $context;
        foreach ($slots as $key => $value) {
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    private function replyStoreInfo(): array
    {
        $hours = config('store.opening_hours', '08:00 - 20:00');
        return [
            'reply_text' => "Giờ mở cửa của DNT Store là {$hours}. Bạn cần mình hỗ trợ thêm gì không?",
            'follow_up_needed' => false,
            'missing_slots' => [],
        ];
    }

    private function replyBookingCreate(array $slots): array
    {
        $required = ['device', 'problem', 'date', 'time', 'name', 'phone'];
        $missing = [];
        foreach ($required as $key) {
            $val = $slots[$key] ?? null;
            if ($val === null || $val === '') {
                $missing[] = $key;
            }
        }

        $phone = $this->cleanSlot($slots['phone'] ?? '');
        if ($phone !== '' && !$this->isValidPhone($phone)) {
            $missing = array_values(array_unique(array_merge(['phone'], $missing)));
        }

        if ($missing) {
            $question = match ($missing[0]) {
                'device' => 'Bạn cần sửa thiết bị gì?',
                'problem' => 'Thiết bị đang gặp vấn đề gì?',
                'date' => 'Bạn muốn đặt lịch ngày nào?',
                'time' => 'Bạn muốn đặt lịch vào khung giờ nào?',
                'name' => 'Mình xin tên người liên hệ?',
                'phone' => 'Mình xin số điện thoại liên hệ nhé?',
                default => 'Bạn có thể cho mình biết thêm thông tin không?',
            };

            return [
                'reply_text' => $question,
                'follow_up_needed' => true,
                'missing_slots' => $missing,
            ];
        }

        $device = $this->cleanSlot($slots['device'] ?? 'thiết bị');
        $problem = $this->cleanSlot($slots['problem'] ?? 'lỗi');
        $date = $this->cleanSlot($slots['date'] ?? '');
        $time = $this->cleanSlot($slots['time'] ?? '');
        $name = $this->cleanSlot($slots['name'] ?? '');
        $branch = $this->cleanSlot($slots['branch'] ?? '');

        $summary = "Mình tóm tắt: {$name} ({$phone}) đặt lịch sửa {$device} (vấn đề: {$problem}) vào {$date} {$time}";
        if ($branch !== '') {
            $summary .= " tại {$branch}";
        }
        $summary .= '. Bạn xác nhận giúp mình nhé.';

        return [
            'reply_text' => $summary,
            'follow_up_needed' => true,
            'missing_slots' => [],
            'requires_confirm' => true,
            'confirm_action' => 'booking_create',
            'confirm_payload' => [
                'device' => $device,
                'problem' => $problem,
                'date' => $date,
                'time' => $time,
                'name' => $name,
                'phone' => $phone,
                'email' => $this->cleanSlot($slots['email'] ?? ''),
                'branch' => $branch,
            ],
        ];
    }

    private function replyServiceQuote(array $slots): array
    {
        $required = ['device', 'problem'];
        $missing = [];
        foreach ($required as $key) {
            $val = $slots[$key] ?? null;
            if ($val === null || $val === '') {
                $missing[] = $key;
            }
        }

        if ($missing) {
            $question = match ($missing[0]) {
                'device' => 'Bạn cần sửa thiết bị gì?',
                'problem' => 'Thiết bị đang gặp vấn đề gì (mất tiếng, rè, pin yếu...)?',
                default => 'Bạn có thể cho mình biết thêm thông tin không?',
            };

            return [
                'reply_text' => $question,
                'follow_up_needed' => true,
                'missing_slots' => $missing,
            ];
        }

        $device = $this->cleanSlot($slots['device'] ?? 'thiết bị');
        $problem = $this->cleanSlot($slots['problem'] ?? 'lỗi');

        return [
            'reply_text' => "Giá sửa {$device} phụ thuộc tình trạng và linh kiện. {$device} đang gặp {$problem} đúng không? Mình sẽ báo giá cụ thể sau khi kiểm tra, bạn muốn đặt lịch luôn không?",
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyProductBuy(array $slots, array $data): array
    {
        $productType = trim((string)($slots['product_type'] ?? ''));
        $budget = trim((string)($slots['budget'] ?? ''));

        if ($productType === '') {
            return [
                'reply_text' => 'Bạn đang quan tâm sản phẩm nào (tai nghe/loa/phụ kiện/linh kiện)?',
                'follow_up_needed' => true,
                'missing_slots' => ['product_type'],
            ];
        }

        if ($budget === '') {
            return [
                'reply_text' => 'Ngân sách của bạn khoảng bao nhiêu để mình lọc sản phẩm phù hợp?',
                'follow_up_needed' => true,
                'missing_slots' => ['budget'],
            ];
        }

        $products = $data['products'] ?? [];
        $count = is_array($products) ? count($products) : 0;
        $reply = $count
            ? 'Mình gợi ý vài sản phẩm phù hợp bên dưới. Bạn muốn lọc thêm theo hãng nào không?'
            : 'Hiện mình chưa tìm được sản phẩm phù hợp. Bạn có thể cho mình biết thêm hãng hoặc nhu cầu sử dụng?';

        return [
            'reply_text' => $reply,
            'follow_up_needed' => $count === 0,
            'missing_slots' => $count === 0 ? ['brand'] : [],
        ];
    }

    private function replyProductCompare(array $data): array
    {
        $products = $data['products'] ?? [];
        if (!is_array($products) || count($products) < 2) {
            return [
                'reply_text' => 'Bạn muốn so sánh những mẫu nào? Bạn gửi tên 2–3 sản phẩm nhé.',
                'follow_up_needed' => true,
                'missing_slots' => ['compare_items'],
            ];
        }

        $lines = [];
        foreach ($products as $idx => $p) {
            $name = $p['name'] ?? 'Sản phẩm';
            $price = $p['price'] ?? 0;
            $stock = (int)($p['stock'] ?? 0);
            $lines[] = ($idx + 1) . ". {$name} — " . $this->formatCurrency($price)
                . ($stock > 0 ? ' (còn hàng)' : ' (hết hàng)');
        }

        return [
            'reply_text' => "So sánh nhanh:\n" . implode("\n", $lines) . "\nBạn muốn mình tư vấn kỹ hơn mẫu nào?",
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyOrderLookup(array $slots, array $data): array
    {
        $orders = $data['orders'] ?? [];
        if (!is_array($orders) || !$orders) {
            return [
                'reply_text' => 'Mình chưa tìm thấy đơn hàng. Bạn gửi giúp mình mã đơn hoặc SĐT đặt hàng nhé.',
                'follow_up_needed' => true,
                'missing_slots' => ['order_code', 'phone'],
            ];
        }

        $summary = $this->formatOrderSummary($orders);

        return [
            'reply_text' => $summary,
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyBookingLookup(array $slots, array $data): array
    {
        $bookings = $data['bookings'] ?? [];
        if (!is_array($bookings) || !$bookings) {
            return [
                'reply_text' => 'Mình chưa tìm thấy đơn đặt lịch. Bạn gửi giúp mình mã đặt lịch hoặc SĐT nhé.',
                'follow_up_needed' => true,
                'missing_slots' => ['booking_code', 'phone'],
            ];
        }

        $lines = [];
        foreach ($bookings as $b) {
            $lines[] = "Đặt lịch #{$b['id']}: {$b['status']} — {$b['booking_date']} {$b['time_slot']}";
        }

        return [
            'reply_text' => implode("\n", $lines),
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyServiceOrderLookup(array $slots, array $data): array
    {
        $orders = $data['service_orders'] ?? [];
        if (!is_array($orders) || !$orders) {
            return [
                'reply_text' => 'Mình chưa tìm thấy phiếu sửa. Bạn gửi giúp mình mã SO hoặc SĐT nhé.',
                'follow_up_needed' => true,
                'missing_slots' => ['service_order_code', 'phone'],
            ];
        }

        $lines = [];
        foreach ($orders as $o) {
            $lines[] = "Phiếu {$o['code']}: {$o['status']} — Tổng: " . $this->formatCurrency($o['total_amount']);
        }

        return [
            'reply_text' => implode("\n", $lines),
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyPolicyInfo(array $data): array
    {
        $kb = $data['kb'] ?? [];
        if (!is_array($kb) || !$kb) {
            return [
                'reply_text' => 'Bạn cần hỏi về bảo hành/đổi trả/vận chuyển hay thanh toán? Mình hỗ trợ ngay.',
                'follow_up_needed' => true,
                'missing_slots' => [],
            ];
        }

        $best = $kb[0];
        $content = $best['content'] ?? '';
        $content = trim($content);
        $content = $content !== '' ? Str::limit($content, 450) : 'Mình đã ghi nhận câu hỏi, bạn nói rõ hơn giúp mình nhé.';

        return [
            'reply_text' => $content,
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replyHandover(): array
    {
        return [
            'reply_text' => 'Mình đã chuyển yêu cầu của bạn cho nhân viên hỗ trợ. Bạn vui lòng chờ một chút nhé.',
            'follow_up_needed' => false,
            'missing_slots' => [],
        ];
    }

    private function replyDefault(): array
    {
        return [
            'reply_text' => 'Mình chưa hiểu rõ yêu cầu. Bạn có thể nói rõ hơn không?',
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function replySmalltalk(): array
    {
        return [
            'reply_text' => 'Chào bạn! Mình có thể hỗ trợ đặt lịch sửa chữa, tư vấn sản phẩm hoặc tra cứu đơn. Bạn cần giúp gì?',
            'follow_up_needed' => true,
            'missing_slots' => [],
        ];
    }

    private function formatOrderSummary(array $orders): string
    {
        $lines = [];
        foreach ($orders as $o) {
            $label = "Đơn #{$o['id']}";
            if (!empty($o['payment_status'])) {
                $label .= " ({$o['payment_status']})";
            }
            $status = $o['order_status'] ? " - {$o['order_status']}" : '';
            $lines[] = $label . $status . ' — ' . $this->formatCurrency($o['total_amount'] ?? 0);
        }
        return implode("\n", $lines);
    }

    private function formatCurrency($amount): string
    {
        try {
            return number_format((float)$amount, 0, ',', '.') . ' ₫';
        } catch (\Throwable $e) {
            return (string)$amount;
        }
    }

    private function cleanSlot(string $value): string
    {
        return trim(str_replace('-', ' ', $value));
    }

    private function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^0\\d{9,10}$/', $phone);
    }
}
