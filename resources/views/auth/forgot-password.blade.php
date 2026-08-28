@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - Wistek Topup')

@section('styles')
<style>
    .auth-container {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        min-height: 500px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        overflow: hidden;
        margin: 4rem auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        max-width: 850px;
    }

    .auth-form-side {
        padding: 3.5rem 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .auth-info-side {
        background: linear-gradient(135deg, rgba(226, 135, 67, 0.1), rgba(139, 92, 246, 0.1)), 
                    radial-gradient(circle at 20% 20%, rgba(226, 135, 67, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
        border-left: 1px solid var(--border-color);
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
    }

    @media (max-width: 768px) {
        .auth-container {
            grid-template-columns: 1fr;
        }
        .auth-info-side {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div style="padding: 2rem 0; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="auth-container">
        
        <!-- Form Side (Left) -->
        <div class="auth-form-side">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">Lupa Kata Sandi</h2>
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 2rem; line-height: 1.5;">
                Masukkan nomor WhatsApp terdaftar Anda. Kami akan mengirimkan kode OTP untuk menyetel ulang kata sandi Anda secara instan.
            </p>

            @if(session('success'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            
            <form action="{{ url('/forgot-password') }}" method="POST">
                @csrf
                
                <!-- Phone Number -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Nomor WhatsApp Terdaftar</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                    @error('phone')
                        <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" style="background: #e28743; border: none; border-radius: 12px; color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; padding: 0.9rem; width: 100%; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); margin-bottom: 1rem;" onmouseover="this.style.background='#cf7432'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#e28743'; this.style.transform='none';">
                    Kirim OTP WhatsApp
                </button>
            </form>
            
            <a href="{{ url('/login') }}" style="color: var(--text-secondary); font-size: 0.82rem; text-align: center; text-decoration: none; display: block; margin-top: 1.5rem; font-weight: 600;" onmouseover="this.style.color='#fff';" onmouseout="this.style.color='var(--text-secondary)';">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
            </a>
        </div>

        <!-- Info Side (Right) -->
        <div class="auth-info-side">
            <div style="background: rgba(226, 135, 67, 0.05); border: 1px solid rgba(226, 135, 67, 0.15); border-radius: 50%; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i class="fa-solid fa-key" style="font-size: 2.5rem; color: #e28743;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem;">Pemulihan Kata Sandi</h3>
            <p style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.6; max-width: 280px; margin-bottom: 0;">
                Kami menggunakan verifikasi WhatsApp OTP dua arah yang aman untuk menjamin keamanan aset akun dan poin loyalitas Anda saat mereset kata sandi.
            </p>
        </div>
        
    </div>
</div>
@endsection
