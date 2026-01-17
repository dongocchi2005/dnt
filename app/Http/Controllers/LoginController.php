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

    $login = trim($request->login);
    $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    if ($field === 'phone') {
        $login = preg_replace('/\D/', '', $login);
    }

    if (Auth::attempt([
        $field => $login,
        'password' => $request->password,
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'login' => 'Email / SĐT hoặc mật khẩu không đúng',
    ]);
}


    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
