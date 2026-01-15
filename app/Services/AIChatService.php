<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatService
{
    /**
     * Generate an assistant reply given chat history and the latest user message.
     *
     * @param array $history Array of message dicts: ['role' => 'system|user|assistant', 'content' => string]
     * @param string $userMessage The latest user message to respond to
     * @return string Assistant reply text
     */
    public function respond(array $history, string $userMessage): string
    {
        $provider = config('ai.provider', 'openai');
        $model = config('ai.model', 'gpt-4o-mini');
        $apiKey = config('ai.api_key');

        if (!$apiKey) {
            Log::error('AI API key not configured');
            return 'Xin lỗi, hệ thống AI chưa được cấu hình.';
        }

        $promptSystem = $this->defaultSystemPrompt();
        $messages = array_merge(
            [['role' => 'system', 'content' => $promptSystem]],
            $history,
            [['role' => 'user', 'content' => $userMessage]]
        );

        try {
            if ($provider === 'gemini') {
                return $this->callGemini($apiKey, $model, $messages);
            }
            return $this->callOpenAI($apiKey, $model, $messages);
        } catch (\Throwable $e) {
            Log::error('AI respond failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return 'Xin lỗi, tôi đang gặp sự cố kỹ thuật. Vui lòng thử lại sau.';
        }
    }

    protected function callOpenAI(string $apiKey, string $model, array $messages): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'top_p' => 0.9,
            'max_tokens' => 800,
        ];

        $res = Http::timeout(30)
            ->withToken($apiKey)
            ->post($url, $payload);

        if ($res->successful()) {
            $data = $res->json();
            $text = $data['choices'][0]['message']['content'] ?? null;
            if ($text) return $text;
        }

        Log::error('OpenAI API error', ['status' => $res->status(), 'body' => $res->body()]);
        return 'Xin lỗi, AI đang bận. Vui lòng thử lại.';
    }

    protected function callGemini(string $apiKey, string $model, array $messages): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $parts = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $content = $m['content'] ?? '';
            $prefix = $role === 'system' ? '[SYSTEM] ' : ($role === 'assistant' ? '[ASSISTANT] ' : '[USER] ');
            $parts[] = ['text' => $prefix . $content];
        }

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

        $res = Http::timeout(30)->post($url, $payload);

        if ($res->successful()) {
            $data = $res->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) return $text;
        }

        Log::error('Gemini API error', ['status' => $res->status(), 'body' => $res->body()]);
        return 'Xin lỗi, AI đang bận. Vui lòng thử lại.';
    }

    protected function defaultSystemPrompt(): string
    {
        return "Bạn là DNT Assistant, trợ lý AI của DNT Store - cửa hàng sửa chữa và bán lẻ thiết bị công nghệ.

Thông tin về DNT Store:
- Chuyên sửa chữa điện thoại, laptop, máy tính bảng và thiết bị công nghệ
- Bán phụ kiện chính hãng: ốp lưng, kính cường lực, sạc, tai nghe
- Đội ngũ kỹ thuật viên giàu kinh nghiệm hơn 5 năm
- Bảo hành 6 tháng cho linh kiện thay thế, 12 tháng cho thiết bị mới
- Thời gian sửa chữa trung bình: 30 phút - 2 giờ
- Giờ làm việc: 8:00 - 18:00 (Thứ 2 - Chủ nhật)
- Địa chỉ: 24/25 Nguyễn Sáng, Phường Tây Thạnh,Quận Tân Phú, TP.HCM
- Hotline: 098733560
- Website: www.dntstore.vn

Hướng dẫn:
- Trả lời bằng tiếng Việt, thân thiện và chuyên nghiệp
- Cung cấp thông tin chính xác về dịch vụ, giá cả, bảo hành
- Nếu không biết thông tin cụ thể, hướng dẫn khách hàng liên hệ trực tiếp
- Khuyến khích khách đặt lịch sửa chữa hoặc mua hàng qua website
";
    }
}

