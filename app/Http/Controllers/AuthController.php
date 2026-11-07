<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Autentikasi Tingkat Tertinggi: Rate Limiting & Proteksi Brute Force (Maksimal 5x percobaan per IP & Akun)
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            ActivityLog::record(
                'login_blocked',
                "Percobaan login diblokir sementara karena terlalu banyak kegagalan untuk username: {$request->input('email')} dari IP {$request->ip()}"
            );

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login yang gagal. Demi keamanan tingkat tinggi, akses dibekukan sementara. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Berhasil login: reset hitungan rate limiter
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                ActivityLog::record(
                    'login_inactive',
                    "Percobaan login ke akun non-aktif: {$user->email} dari IP {$request->ip()}"
                );

                return back()->withErrors([
                    'email' => 'Akun Anda saat ini dinonaktifkan oleh Administrator.',
                ]);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Regenerasi session ID untuk mencegah serangan Session Fixation
            $request->session()->regenerate();

            ActivityLog::record(
                'login',
                "User {$user->name} ({$user->email}) role {$user->role} berhasil login ke sistem",
                'User',
                $user->id
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        // Gagal login: tambah hitungan percobaan dan kunci selama 60 detik jika mencapai batas
        RateLimiter::hit($throttleKey, 60);

        $remaining = RateLimiter::remaining($throttleKey, 5);

        ActivityLog::record(
            'login_failed',
            "Percobaan login gagal untuk username: {$request->input('email')} dari IP {$request->ip()} (Sisa percobaan: {$remaining})"
        );

        $errorMessage = $remaining > 0
            ? "Email / username atau password yang Anda masukkan salah. (Sisa percobaan: {$remaining}x)"
            : "Email / username atau password yang Anda masukkan salah. Batas percobaan habis.";

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record('logout', 'User ' . Auth::user()->name . ' (' . Auth::user()->email . ') logout dari sistem');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil logout.');
    }
}

