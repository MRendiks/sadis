<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Login extends Controller
{
    public function index()
    {
          if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return view('content.authentications.auth-login-basic');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // cek role dari trait
            $user = Auth::user();
            if ($user->hasRole('super_admin')) {
                return redirect()->route('dashboard'); // super admin ke dashboard global
            } elseif ($user->hasRole('admin_arsip')) {
                return redirect()->route('dashboard'); // admin ke dashboard global
            } else {
                return redirect()->route('dashboard'); // user divisi ke dashboard divisi
            }
        }

        return back()->withErrors([
          'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('content.authentications.auth-login-basic');
    }
}
