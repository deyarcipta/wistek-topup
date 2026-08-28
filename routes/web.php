<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TopupController;
use Illuminate\Support\Facades\Route;

// Landing Homepage
Route::get('/', [TopupController::class, 'index']);

// Product detail / order forms
Route::get('/category/{slug}', [TopupController::class, 'showCategory']);
Route::post('/checkout', [TopupController::class, 'checkout']);
Route::post('/api/validate-voucher', [TopupController::class, 'validateVoucher']);

// Invoice / payment receipt details page
Route::get('/transaction/{invoice}', [TopupController::class, 'showTransaction']);

// Check transaction status via search invoice
Route::get('/history', [TopupController::class, 'showHistoryForm']);
Route::post('/history', [TopupController::class, 'checkHistory']);

// API status lookup for AJAX checkout page status updates
Route::get('/api/transaction-status/{invoice}', [TopupController::class, 'apiStatus']);

// Webhooks (Exempt from CSRF in bootstrap/app.php)
Route::post('/callback/duitku', [CallbackController::class, 'duitkuCallback']);
Route::post('/callback/digiflazz', [CallbackController::class, 'digiflazzCallback']);

// Local Simulation Route for Testing
Route::get('/simulate-paid/{invoice}', [CallbackController::class, 'simulatePaid']);

// Authentikasi Guest
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/register/verify', [AuthController::class, 'showVerifyForm'])->name('register.verify');
    Route::post('/register/verify', [AuthController::class, 'verifyOtp']);
    Route::post('/register/resend-otp', [AuthController::class, 'resendOtp']);
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp']);
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Member Dashboard (Auth and Customer Role Required)
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/dashboard', [MemberController::class, 'index']);
    Route::get('/dashboard/transactions', [MemberController::class, 'transactions']);
    Route::get('/dashboard/points', [MemberController::class, 'pointLogs']);
    Route::get('/dashboard/profile', [MemberController::class, 'showProfile']);
    Route::post('/dashboard/profile', [MemberController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
