<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneOtpService;
use App\Services\SmsService;
use Illuminate\Http\Request;

class PhoneOtpController extends Controller
{
    public function send(Request $request, PhoneOtpService $otpService, SmsService $smsService)
    {
        $phoneInput = $request->input('phone');
        if ($phoneInput) {
            $request->merge(['phone' => preg_replace('/[^0-9]/', '', (string)$phoneInput)]);
        }

        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^0\\d{9,10}$/'],
        ], [
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        $phone = (string)$data['phone'];

        if (User::where('phone', $phone)->exists()) {
            return response()->json([
                'message' => 'Số điện thoại đã được đăng ký.',
            ], 422);
        }

        $result = $otpService->send($phone);
        if ($result['status'] === 'cooldown') {
            return response()->json([
                'message' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau.',
                'wait' => (int)($result['wait'] ?? 0),
            ], 429);
        }

        $sent = $smsService->sendOtp($phone, $result['code'], (int)$result['ttl']);
        if (!$sent) {
            return response()->json([
                'message' => 'Không gửi được OTP, vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'status' => 'sent',
            'ttl' => (int)$result['ttl'],
            'cooldown' => (int)$result['cooldown'],
        ]);
    }
}
