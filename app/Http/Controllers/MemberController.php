<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    /**
     * Dashboard overview
     */
    public function index()
    {
        $user = Auth::user();

        // Calculate statistics
        $totalTransactions = $user->transactions()
            ->where('payment_status', 'paid')
            ->count();

        $totalSpent = $user->transactions()
            ->where('payment_status', 'paid')
            ->sum('price');

        // Expiring points in next 30 days
        $expiringPoints = $user->pointLogs()
            ->where('amount', '>', 0)
            ->where('is_expired', false)
            ->where('expired_at', '>', now())
            ->where('expired_at', '<=', now()->addDays(30))
            ->sum('amount');

        // Recent 5 transactions
        $recentTransactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'totalTransactions',
            'totalSpent',
            'expiringPoints',
            'recentTransactions'
        ));
    }

    /**
     * View all user transactions
     */
    public function transactions()
    {
        $user = Auth::user();

        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('dashboard.transactions', compact('transactions'));
    }

    /**
     * View point mutation logs
     */
    public function pointLogs()
    {
        $user = Auth::user();

        $pointLogs = $user->pointLogs()
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.points', compact('pointLogs'));
    }

    /**
     * Show edit profile form
     */
    public function showProfile()
    {
        $user = Auth::user();

        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update user profile details
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'min:9', 'max:15'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'current_password' => ['nullable', 'string', 'required_with:password'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'Alamat email ini sudah terdaftar oleh pengguna lain.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus berupa JPG, JPEG, atau PNG.',
            'photo.max' => 'Ukuran gambar maksimal adalah 2MB.',
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

        // Check if normalized phone is already taken by another user
        $isPhoneTaken = User::where('phone', $cleanPhone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($isPhoneTaken) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar oleh pengguna lain dan tidak dapat digunakan kembali.',
            ]);
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile-photos', 'public');
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $path;
        }

        // If trying to change password
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Kata sandi saat ini yang Anda masukkan salah.',
                ]);
            }
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $cleanPhone;
        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }
}
