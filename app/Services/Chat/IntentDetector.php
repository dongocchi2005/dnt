<?php

namespace App\Services\Chat;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class IntentDetector
{
    public function normalizeIntent(?string $intent): ?string
    {
        if (!$intent) return null;

        $intent = trim((string)$intent);
        return match ($intent) {
            'service_repair', 'repair_booking', 'booking_create' => 'booking_create',
            'service_quote', 'repair_quote' => 'service_quote',
            'product_buy' => 'product_buy',
            'product_compare' => 'product_compare',
            'order_lookup' => 'order_lookup',
            'booking_lookup' => 'booking_lookup',
            'service_order_lookup' => 'service_order_lookup',
            'policy_info' => 'policy_info',
            'store_info' => 'store_info',
            'handover_admin' => 'handover_admin',
            'smalltalk' => 'smalltalk',
            default => null,
        };
    }

    public function detect(string $message, array $context = []): array
    {
        $text = Str::lower(trim($message));
        $intent = null;
        $slots = [];

        if ($this->isHandover($text)) {
            $intent = 'handover_admin';
        } elseif ($this->isServiceOrderLookup($text)) {
            $intent = 'service_order_lookup';
            $slots = array_merge($slots, $this->extractLookupSlots($message));
        } elseif ($this->isBookingLookup($text)) {
            $intent = 'booking_lookup';
            $slots = array_merge($slots, $this->extractLookupSlots($message));
        } elseif ($this->isOrderLookup($text)) {
            $intent = 'order_lookup';
            $slots = array_merge($slots, $this->extractLookupSlots($message));
        } elseif ($this->isServiceQuote($text)) {
            $intent = 'service_quote';
            $slots = array_merge($slots, $this->extractRepairSlots($message));
        } elseif ($this->isRepairBooking($text)) {
            $intent = 'booking_create';
            $slots = array_merge($slots, $this->extractRepairSlots($message));
        } elseif ($this->isProductCompare($text)) {
            $intent = 'product_compare';
            $slots = array_merge($slots, $this->extractCompareSlots($message));
        } elseif ($this->isProductBuy($text)) {
            $intent = 'product_buy';
            $slots = array_merge($slots, $this->extractProductSlots($message));
        } elseif ($this->isPolicyInfo($text)) {
            $intent = 'policy_info';
        } elseif ($this->isStoreInfo($text)) {
            $intent = 'store_info';
        } elseif ($this->isSmalltalk($text)) {
            $intent = 'smalltalk';
        }

        $slots = array_merge($slots, $this->extractCommonSlots($message));

        return [
            'intent' => $intent,
            'slots' => $slots,
        ];
    }

    public function mergeSlots(array $base, array $extra): array
    {
        foreach ($extra as $key => $value) {
            if (!isset($base[$key]) || $base[$key] === null || $base[$key] === '') {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    private function isStoreInfo(string $text): bool
    {
        $keywords = ['giờ mở cửa', 'mở cửa', 'đóng cửa', 'giờ làm việc', 'giờ hoạt động', 'giờ'];
        return Str::contains($text, $keywords);
    }

    private function isRepairBooking(string $text): bool
    {
        $keywords = ['đặt lịch', 'dat lich', 'đặt lịch sửa', 'sửa', 'sua', 'đặt hẹn', 'dat hen'];
        return Str::contains($text, $keywords);
    }

    private function isSmalltalk(string $text): bool
    {
        $keywords = ['hello', 'hi', 'xin chào', 'chào', 'chao', 'hey'];
        return Str::contains($text, $keywords);
    }

    private function isHandover(string $text): bool
    {
        $keywords = ['gặp người', 'gặp nhân viên', 'gặp tư vấn', 'người thật', 'chuyển admin', 'hỗ trợ viên'];
        return Str::contains($text, $keywords);
    }

    private function isOrderLookup(string $text): bool
    {
        if ($this->extractOrderCode($text)) {
            return true;
        }
        if ($this->containsAll($text, ['tra cứu', 'đơn'])) {
            return true;
        }
        return Str::contains($text, ['đơn hàng', 'order', 'mã đơn', 'trạng thái đơn']);
    }

    private function isServiceOrderLookup(string $text): bool
    {
        if ($this->extractServiceOrderCode($text)) {
            return true;
        }
        $keywords = ['phiếu tiếp nhận', 'đơn sửa', 'service order', 'mã so', 'ma so'];
        return Str::contains($text, $keywords);
    }

    private function isBookingLookup(string $text): bool
    {
        if ($this->extractBookingCode($text)) {
            return true;
        }
        $keywords = ['đặt lịch', 'booking', 'lịch sửa', 'trạng thái đặt lịch', 'tra cứu lịch'];
        return Str::contains($text, $keywords) && Str::contains($text, ['tra cứu', 'trạng thái', 'xem']);
    }

    private function isPolicyInfo(string $text): bool
    {
        $keywords = [
            'bảo hành', 'doi tra', 'đổi trả', 'hoàn tiền',
            'vận chuyển', 'giao hàng', 'ship', 'phí ship',
            'cod', 'vietqr', 'thanh toán', 'chuyển khoản'
        ];
        return Str::contains($text, $keywords);
    }

    private function isServiceQuote(string $text): bool
    {
        $priceKeywords = ['giá', 'bao nhiêu', 'bao nhieu', 'báo giá', 'bao gia', 'chi phí', 'chi phi', 'phí'];
        $repairKeywords = ['sửa', 'sua', 'sửa chữa', 'sua chua', 'thay', 'thay pin', 'vệ sinh', 've sinh'];
        return Str::contains($text, $priceKeywords) && Str::contains($text, $repairKeywords);
    }

    private function isProductBuy(string $text): bool
    {
        $keywords = ['mua', 'giá', 'còn hàng', 'sản phẩm', 'tai nghe', 'loa', 'phụ kiện', 'linh kiện'];
        return Str::contains($text, $keywords);
    }

    private function isProductCompare(string $text): bool
    {
        return Str::contains($text, ['so sánh', 'compare', 'so sanh', 'vs', 'versus']);
    }

    private function extractRepairSlots(string $message): array
    {
        $text = Str::lower($message);
        $slots = [];

        $deviceMap = [
            'tai nghe' => ['tai nghe', 'tai-nghe', 'airpod', 'airpods', 'headphone', 'earphone'],
            'loa' => ['loa', 'speaker'],
            'điện thoại' => ['điện thoại', 'dien thoai', 'phone', 'iphone', 'android'],
            'laptop' => ['laptop', 'macbook', 'notebook'],
            'pc' => ['pc', 'máy tính bàn', 'may tinh ban', 'desktop'],
        ];
        foreach ($deviceMap as $label => $keys) {
            if (Str::contains($text, $keys)) {
                $slots['device'] = $label;
                break;
            }
        }

        if (!isset($slots['device']) && Str::contains($text, ['thiết bị', 'thiet bi'])) {
            $slots['device'] = 'thiết bị';
        }

        if (preg_match('/\b(bị|bi)\s+([^,.]+)/iu', $message, $m)) {
            $slots['problem'] = trim($m[2]);
        } elseif (preg_match('/\b(lỗi|loi)\s+([^,.]+)/iu', $message, $m)) {
            $slots['problem'] = trim($m[2]);
        }

        $date = $this->extractDate($message);
        if ($date) {
            $slots['date'] = $date;
        }

        $time = $this->extractTime($message);
        if ($time) {
            $slots['time'] = $time;
        }

        return $slots;
    }

    private function extractProductSlots(string $message): array
    {
        $slots = [];
        $text = Str::lower($message);

        $typeMap = [
            'tai-nghe' => ['tai nghe', 'tai-nghe', 'airpod', 'airpods', 'headphone', 'earphone'],
            'loa' => ['loa', 'speaker'],
            'phu-kien' => ['phụ kiện', 'phu kien', 'accessory'],
            'linh-kien' => ['linh kiện', 'linh kien', 'parts'],
        ];
        foreach ($typeMap as $type => $keys) {
            if (Str::contains($text, $keys)) {
                $slots['product_type'] = $type;
                break;
            }
        }

        $slots['keywords'] = trim((string)$message);
        return $slots;
    }

    private function extractCompareSlots(string $message): array
    {
        $slots = $this->extractProductSlots($message);
        $text = Str::lower($message);
        $parts = preg_split('/\b(vs|versus|so sánh|so sanh|với|va|và|,|;)\b/u', $text);
        $items = collect($parts)
            ->map(fn($p) => trim((string)$p))
            ->filter(fn($p) => $p !== '' && mb_strlen($p) > 2)
            ->values()
            ->all();
        if ($items) {
            $slots['compare_items'] = $items;
        }
        return $slots;
    }

    private function extractLookupSlots(string $message): array
    {
        $slots = [];

        $orderCode = $this->extractOrderCode($message);
        if ($orderCode) {
            $slots['order_code'] = $orderCode;
        }

        $serviceCode = $this->extractServiceOrderCode($message);
        if ($serviceCode) {
            $slots['service_order_code'] = $serviceCode;
        }

        $bookingCode = $this->extractBookingCode($message);
        if ($bookingCode) {
            $slots['booking_code'] = $bookingCode;
        }

        return $slots;
    }

    private function extractCommonSlots(string $message): array
    {
        $slots = [];

        if ($phone = $this->extractPhone($message)) {
            $slots['phone'] = $phone;
        }

        if ($email = $this->extractEmail($message)) {
            $slots['email'] = $email;
        }

        if ($name = $this->extractName($message)) {
            $slots['name'] = $name;
        }

        if ($branch = $this->extractBranch($message)) {
            $slots['branch'] = $branch;
        }

        if ($date = $this->extractDate($message)) {
            $slots['date'] = $date;
        }

        if ($time = $this->extractTime($message)) {
            $slots['time'] = $time;
        }

        if ($budget = $this->extractBudget($message)) {
            $slots['budget'] = $budget;
        }

        return $slots;
    }

    private function extractDate(string $message): ?string
    {
        $text = Str::lower($message);
        if (Str::contains($text, ['hôm nay', 'hom nay'])) {
            return Carbon::now()->format('d/m/Y');
        }
        if (Str::contains($text, ['ngày mai', 'ngay mai', 'mai'])) {
            return Carbon::now()->addDay()->format('d/m/Y');
        }
        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\b/', $message, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3] ?? Carbon::now()->format('Y');
            if (strlen($year) === 2) {
                $year = '20' . $year;
            }
            return "{$day}/{$month}/{$year}";
        }
        return null;
    }

    private function extractTime(string $message): ?string
    {
        $text = Str::lower($message);
        if (Str::contains($text, ['sáng', 'sang'])) {
            return 'buổi sáng';
        }
        if (Str::contains($text, ['chiều', 'chieu'])) {
            return 'buổi chiều';
        }
        if (Str::contains($text, ['tối', 'toi'])) {
            return 'buổi tối';
        }
        if (preg_match('/\b(\d{1,2})(?::(\d{2}))?\s*(h|giờ|gio)\b/iu', $message, $m)) {
            $hour = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $minute = isset($m[2]) ? str_pad($m[2], 2, '0', STR_PAD_LEFT) : '00';
            return "{$hour}:{$minute}";
        }
        return null;
    }

    private function extractPhone(string $message): ?string
    {
        if (preg_match('/\b(0\d{9,10})\b/u', $message, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractEmail(string $message): ?string
    {
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $message, $m)) {
            return $m[0];
        }
        return null;
    }

    private function extractName(string $message): ?string
    {
        if (preg_match('/\b(tôi là|ten tôi là|tên tôi là)\s+([^,.]+)/iu', $message, $m)) {
            return trim($m[2]);
        }
        return null;
    }

    private function extractBranch(string $message): ?string
    {
        if (preg_match('/\b(chi nhánh|chi nhanh|cơ sở|co so|branch)\s+([^,.]+)/iu', $message, $m)) {
            return trim($m[2]);
        }
        return null;
    }

    private function extractOrderCode(string $message): ?string
    {
        if (preg_match('/\bORDER[_\- ]?(\d+)\b/i', $message, $m)) {
            return 'ORDER_' . $m[1];
        }
        if (preg_match('/\bđơn\s*#?\s*(\d+)\b/iu', $message, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractServiceOrderCode(string $message): ?string
    {
        if (preg_match('/\bSO[0-9A-Z]{6,}\b/i', $message, $m)) {
            return strtoupper($m[0]);
        }
        return null;
    }

    private function extractBookingCode(string $message): ?string
    {
        if (preg_match('/\bbooking\s*#?\s*(\d+)\b/iu', $message, $m)) {
            return $m[1];
        }
        if (preg_match('/\blịch\s*#?\s*(\d+)\b/iu', $message, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractBudget(string $message): ?string
    {
        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*(triệu|tr|m|nghìn|ngan|k|vnd|đ)\b/iu', $message, $m)) {
            return $m[0];
        }
        return null;
    }

    private function containsAll(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (!Str::contains($text, $kw)) {
                return false;
            }
        }
        return true;
    }
}
