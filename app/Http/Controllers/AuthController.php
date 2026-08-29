<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show register form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle registration post request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_num', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:9', 'max:15'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ], [
            'username.unique' => 'Username ini sudah digunakan.',
            'username.alpha_num' => 'Username hanya boleh terdiri dari huruf dan angka.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'referral_code.exists' => 'Kode referral tidak valid atau tidak ditemukan.',
        ]);

        $referredById = null;
        $ipAddress = $request->ip();

        // Clean & Normalize Phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        if (str_starts_with($cleanPhone, '620')) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        if (str_starts_with($cleanPhone, '62')) {
            $cleanPhone = '0'.substr($cleanPhone, 2);
        }
        if (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '0'.$cleanPhone;
        }

        if (User::where('phone', $cleanPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar sebagai member.',
            ]);
        }

        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->referral_code)->first();

            if ($referrer) {
                // Anti-Abuse: Check if the referrer is attempting to use their own referral code
                if ($referrer->email === $request->email || $referrer->phone === $cleanPhone) {
                    throw ValidationException::withMessages([
                        'referral_code' => 'Anda tidak dapat menggunakan kode referral milik Anda sendiri.',
                    ]);
                }

                $referredById = $referrer->id;
            }
        }

        // Generate 6-digit OTP
        $otp = (string) rand(100000, 999999);

        // Put in Session
        session()->put('pending_registration', [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $cleanPhone,
            'password' => Hash::make($request->password),
            'referred_by_id' => $referredById,
            'registration_ip' => $ipAddress,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5)->timestamp,
            'last_sent_at' => now()->timestamp,
        ]);

        // Send OTP via Whatsapp
        $whatsapp = new WhatsappService;
        $whatsapp->sendMessage($cleanPhone, "Kode OTP pendaftaran member Wistek Topup Anda adalah: *{$otp}*.\n\nJangan berikan kode ini kepada siapa pun. Kode berlaku selama 5 menit.");

        return redirect()->route('register.verify')->with('success', 'Kode OTP pendaftaran berhasil dikirim ke nomor WhatsApp Anda!');
    }

    /**
     * Show OTP verify form
     */
    public function showVerifyForm()
    {
        if (! session()->has('pending_registration')) {
            return redirect('/register');
        }

        return view('auth.verify');
    }

    /**
     * Handle verify OTP request
     */
    public function verifyOtp(Request $request)
    {
        if (! session()->has('pending_registration')) {
            return redirect('/register');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $pending = session()->get('pending_registration');

        if (now()->timestamp > $pending['expires_at']) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP ini sudah kedaluwarsa. Silakan minta kode OTP baru.',
            ]);
        }

        if ($request->otp !== $pending['otp']) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP yang Anda masukkan salah.',
            ]);
        }

        // Create User
        $user = User::create([
            'name' => $pending['name'],
            'username' => $pending['username'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => $pending['password'],
            'role' => 'member',
            'referred_by_id' => $pending['referred_by_id'],
            'registration_ip' => $pending['registration_ip'],
            'points_balance' => 0,
        ]);

        Auth::login($user);

        session()->forget('pending_registration');

        return redirect('/dashboard')->with('success', 'Akun member Anda berhasil terverifikasi dan didaftarkan!');
    }

    /**
     * Handle resend OTP request
     */
    public function resendOtp(Request $request)
    {
        if (! session()->has('pending_registration')) {
            return redirect('/register');
        }

        $pending = session()->get('pending_registration');
        $cooldown = 60; // seconds
        $timePassed = now()->timestamp - $pending['last_sent_at'];

        if ($timePassed < $cooldown) {
            $timeLeft = $cooldown - $timePassed;
            throw ValidationException::withMessages([
                'otp' => "Tunggu {$timeLeft} detik lagi sebelum mengirim ulang kode OTP.",
            ]);
        }

        // Generate new OTP
        $otp = (string) rand(100000, 999999);
        $pending['otp'] = $otp;
        $pending['expires_at'] = now()->addMinutes(5)->timestamp;
        $pending['last_sent_at'] = now()->timestamp;
        session()->put('pending_registration', $pending);

        // Send OTP
        $whatsapp = new WhatsappService;
        $whatsapp->sendMessage($pending['phone'], "Kode OTP baru pendaftaran member Wistek Topup Anda adalah: *{$otp}*.\n\nJangan berikan kode ini kepada siapa pun. Kode berlaku selama 5 menit.");

        return back()->with('success', 'Kode OTP baru berhasil dikirim ulang ke nomor WhatsApp Anda!');
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin() || Auth::user()->isCashier()) {
                return redirect('/admin');
            }

            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login post request
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'], // email or username
            'password' => ['required', 'string'],
        ]);

        // Support login by email or username
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isAdmin() || $user->isCashier()) {
                return redirect('/admin')->with('success', 'Selamat datang kembali!');
            }

            return redirect()->intended('/dashboard')->with('success', 'Selamat datang kembali!');
        }

        throw ValidationException::withMessages([
            'login' => 'Email/Username atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda berhasil keluar akun.');
    }

    /**
     * Show forgot password request form
     */
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Handle sending password reset OTP to WhatsApp number
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Clean & Normalize Phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        if (str_starts_with($cleanPhone, '620')) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        if (str_starts_with($cleanPhone, '62')) {
            $cleanPhone = '0'.substr($cleanPhone, 2);
        }
        if (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '0'.$cleanPhone;
        }

        // Find user by phone number
        $user = User::where('phone', $cleanPhone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor WhatsApp yang Anda masukkan tidak terdaftar.',
            ]);
        }

        // Generate 6-digit OTP
        $otp = (string) rand(100000, 999999);

        // Put in Session
        session()->put('password_reset_session', [
            'user_id' => $user->id,
            'phone' => $cleanPhone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10)->timestamp,
            'last_sent_at' => now()->timestamp,
        ]);

        // Send OTP
        $whatsapp = new WhatsappService;
        $whatsapp->sendMessage($cleanPhone, "Kode OTP untuk menyetel ulang kata sandi Wistek Topup Anda adalah: *{$otp}*.\n\nJangan berikan kode ini kepada siapa pun. Kode berlaku selama 10 menit.");

        return redirect()->route('password.reset')->with('success', 'Kode OTP reset kata sandi berhasil dikirim ke WhatsApp Anda!');
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm()
    {
        if (! session()->has('password_reset_session')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * Process resetting password with verification
     */
    public function resetPassword(Request $request)
    {
        if (! session()->has('password_reset_session')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $resetData = session()->get('password_reset_session');

        if (now()->timestamp > $resetData['expires_at']) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP reset kata sandi ini sudah kedaluwarsa. Silakan minta kode OTP baru.',
            ]);
        }

        if ($request->otp !== $resetData['otp']) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP yang Anda masukkan salah.',
            ]);
        }

        $user = User::find($resetData['user_id']);
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        session()->forget('password_reset_session');

        return redirect('/login')->with('success', 'Kata sandi Anda berhasil disetel ulang! Silakan masuk dengan kata sandi baru.');
    }
}
