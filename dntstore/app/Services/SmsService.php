<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendOtp(string $phone, string $code, int $ttlMinutes): bool
    {
        $message = $this->buildOtpMessage($code, $ttlMinutes);
        return $this->send($phone, $message);
    }

    public function send(string $phone, string $message): bool
    {
        $driver = (string)config('sms.driver', 'log');

        if ($driver === 'log') {
            Log::info('SMS (log driver)', [
                'to' => $phone,
                'message' => $message,
            ]);
            return true;
        }

        if ($driver === 'http') {
            $url = config('sms.http.url');
            if (!$url) {
                Log::warning('SMS HTTP URL not configured.');
                return false;
            }

            $payload = [
                'to' => $phone,
                'message' => $message,
                'sender' => config('sms.sender'),
            ];

            $request = Http::timeout((int)config('sms.http.timeout', 10));

            $token = config('sms.http.token');
            if ($token) {
                $request = $request->withToken($token);
            }

            $headers = config('sms.http.headers', []);
            if (is_array($headers) && !empty($headers)) {
                $request = $request->withHeaders($headers);
            }

            $response = $request->post($url, $payload);
            if (!$response->successful()) {
                Log::warning('SMS HTTP send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        }

        Log::warning('SMS driver not supported', ['driver' => $driver]);
        return false;
    }

    private function buildOtpMessage(string $code, int $ttlMinutes): string
    {
        $template = (string)config('sms.otp_template', 'Ma OTP cua ban la :code. Hieu luc :ttl phut.');
        return str_replace([':code', ':ttl'], [$code, (string)$ttlMinutes], $template);
    }
}
