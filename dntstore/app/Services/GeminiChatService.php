<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    public function respond(array $history, string $userMessage): string
    {
        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-3-flash-preview');

        if (!$apiKey) {
            Log::error('Gemini API key not configured');
            return 'Xin lỗi, hệ thống AI chưa được cấu hình.';
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $parts = [];
        $parts[] = ['text' => $this->systemPrompt()];

        foreach ($history as $m) {
            $role = $m['role'] ?? 'user';
            $content = $m['content'] ?? '';
            $prefix = $role === 'assistant' ? '[ASSISTANT] ' : '[USER] ';
            $parts[] = ['text' => $prefix . $content];
        }
        $parts[] = ['text' => '[USER] ' . $userMessage];

        $payload = [
            'contents' => [
                ['parts' => $parts]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];

        try {
            $response = Http::timeout(15)->connectTimeout(5)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    return $text;
                }
            }

            Log::error('Gemini API response error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return 'Xin lỗi, AI đang bận. Vui lòng thử lại.';
        } catch (\Throwable $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage());
            return 'Xin lỗi, tôi đang gặp sự cố kỹ thuật. Vui lòng thử lại sau.';
        }
    }

  protected function systemPrompt(): string
  {
        return "Bạn là DNT Assistant của DNT Store. Chỉ trả về JSON hợp lệ theo đúng schema sau, không thêm bất kỳ ký tự nào ngoài JSON:
{
  \"intent\": \"service_repair|service_quote|booking_create|product_buy|product_compare|order_lookup|booking_lookup|service_order_lookup|policy_info|store_info|handover_admin|smalltalk|unknown\",
  \"confidence\": 0.0,
  \"slots\": {
    \"device\": \"laptop|pc|dien-thoai|tai-nghe|loa|may-giat|tu-lanh|khac|null\",
    \"problem\": \"string|null\",
    \"service\": \"sua-chua|thay-pin|ve-sinh|nang-cap|khac|null\",
    \"product_type\": \"tai-nghe|loa|phu-kien|linh-kien|khac|null\",
    \"brand\": \"string|null\",
    \"keywords\": \"string|null\",
    \"order_code\": \"string|null\",
    \"booking_code\": \"string|null\",
    \"service_order_code\": \"string|null\",
    \"name\": \"string|null\",
    \"phone\": \"string|null\",
    \"email\": \"string|null\",
    \"location\": \"string|null\",
    \"time\": \"string|null\",
    \"date\": \"string|null\",
    \"branch\": \"string|null\",
    \"budget\": \"string|null\"
  },
  \"next_action\": \"ask_missing|suggest_services|suggest_products|create_booking|lookup_order|lookup_booking|lookup_service_order|handover_admin\",
  \"reply\": \"string\"
}
Yêu cầu:
- Trả lời bằng tiếng Việt, ngắn gọn 2–4 câu, không markdown, có 1 câu hỏi dẫn dắt.
- Nếu thiếu thông tin bắt buộc theo intent, chọn next_action=ask_missing và đặt câu hỏi phù hợp.
- Nếu khách muốn gặp người thật, intent=handover_admin và next_action=handover_admin.
- Nếu người dùng nói về \"mua\", \"giá\", \"còn hàng\", \"sản phẩm\", \"tai nghe\", \"loa\", \"phụ kiện\" thì intent=product_buy. Với intent=product_buy, ưu tiên hỏi rõ nhu cầu (loại sản phẩm, thương hiệu, mức ngân sách) và chọn next_action=suggest_products nếu đã đủ thông tin cơ bản.
- Nếu người dùng muốn so sánh, intent=product_compare.
- Nếu người dùng hỏi bảo hành/đổi trả/vận chuyển/thanh toán, intent=policy_info.
- Nếu người dùng hỏi giá sửa chữa, intent=service_quote.
- Chỉ trả JSON, không được thêm mô tả, markdown hoặc văn bản ngoài JSON.";
    }
}
