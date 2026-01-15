<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');

    }

    public function register(Request $request)
    {
        $phoneInput = $request->input('phone');
        if ($phoneInput) {
            $request->merge(['phone' => preg_replace('/[^0-9]/', '', (string)$phoneInput)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'required|string|regex:/^0\\d{9,10}$/|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        $email = trim((string)$request->input('email'));
        $phone = trim((string)$request->input('phone'));

        $user = User::create([
            'name' => $request->name,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }
}
