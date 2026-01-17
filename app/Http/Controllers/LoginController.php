<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('login'));

        // Tìm user theo email hoặc phone
        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        // Không tồn tại user
        if (!$user) {
            return back()->withErrors([
                'login' => 'Tài khoản không tồn tại',
            ]);
        }

        // Sai mật khẩu
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'login' => 'Mật khẩu không đúng',
            ]);
        }

        // Đăng nhập
        Auth::login($user, $request->filled('remember'));

        return redirect()->intended(route('dashboard'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
