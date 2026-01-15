<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PhoneOtpService
{
    public function send(string $phone): array
    {
        $phone = $this->normalizePhone($phone);
        $now = now();

        $payload = Cache::get($this->key($phone));
        $cooldown = (int)config('otp.cooldown_seconds', 60);

        if ($payload && isset($payload['last_sent_at'])) {
            $lastSent = $this->asTime($payload['last_sent_at']);
            $diff = $now->diffInSeconds($lastSent);
            if ($diff < $cooldown) {
                return [
                    'status' => 'cooldown',
                    'wait' => $cooldown - $diff,
                    'cooldown' => $cooldown,
                ];
            }
        }

        $length = (int)config('otp.length', 6);
        $ttlMinutes = (int)config('otp.ttl_minutes', 5);
        $code = $this->generateCode($length);

        $data = [
            'code_hash' => Hash::make($code),
            'expires_at' => $now->copy()->addMinutes($ttlMinutes)->getTimestamp(),
            'attempts' => 0,
            'last_sent_at' => $now->getTimestamp(),
        ];

        Cache::put($this->key($phone), $data, $ttlMinutes * 60);

        return [
            'status' => 'sent',
            'code' => $code,
            'ttl' => $ttlMinutes,
            'cooldown' => $cooldown,
        ];
    }

    public function verify(string $phone, string $code): bool
    {
        $phone = $this->normalizePhone($phone);
        $payload = Cache::get($this->key($phone));
        if (!$payload) {
            return false;
        }

        $now = now();
        $expiresAt = $this->asTime($payload['expires_at'] ?? null);
        if (!$expiresAt || $now->greaterThan($expiresAt)) {
            Cache::forget($this->key($phone));
            return false;
        }

        $maxAttempts = (int)config('otp.max_attempts', 5);
        $attempts = (int)($payload['attempts'] ?? 0);
        if ($attempts >= $maxAttempts) {
            Cache::forget($this->key($phone));
            return false;
        }

        if (Hash::check($code, $payload['code_hash'] ?? '')) {
            Cache::forget($this->key($phone));
            return true;
        }

        $payload['attempts'] = $attempts + 1;
        $ttlSeconds = max(30, $expiresAt->diffInSeconds($now));
        Cache::put($this->key($phone), $payload, $ttlSeconds);
        return false;
    }

    private function key(string $phone): string
    {
        return 'otp:phone:' . $phone;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    private function generateCode(int $length): string
    {
        $max = (10 ** $length) - 1;
        return str_pad((string)random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function asTime($value)
    {
        if (!$value) return null;
        if ($value instanceof \DateTimeInterface) return $value;
        return now()->createFromTimestamp((int)$value);
    }
}
