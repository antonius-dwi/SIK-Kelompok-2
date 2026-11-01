<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Akun nonaktif ditolak lewat kondisi is_active; role selalu dibaca dari database.
        if (! Auth::attempt($credentials + ['is_active' => true])) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages(['username' => 'Kredensial salah atau akun tidak aktif.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $audit->record($request->user(), 'Login');

        return redirect()->intended(route($request->user()->role->homeRoute()));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
