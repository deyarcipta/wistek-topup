@extends('layouts.app')

@section('title', 'Masuk Akun Member - Wistek Topup')

@section('styles')
<style>
    .auth-container {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        min-height: 550px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        overflow: hidden;
        margin: 4rem auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        max-width: 900px;
    }

    .auth-form-side {
        padding: 3.5rem 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .auth-info-side {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(59, 130, 246, 0.15)), 
                    radial-gradient(circle at 20% 20%, rgba(139, 92, 246, 0.25) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.25) 0%, transparent 50%);
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
            min-height: auto;
            margin: 2rem auto;
        }
        .auth-info-side {
            display: none;
        }
        .auth-form-side {
            padding: 2.5rem 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="auth-container">
        
        <!-- Info Side (Left in Grid but visually right side decoration) -->
        <div class="auth-info-side">
            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <i class="fa-solid fa-gamepad" style="font-size: 3rem; color: #3b82f6;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 1rem;">Masuk ke Arena Belanja</h3>
            <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.6; max-width: 280px; margin-bottom: 0;">
                Nikmati kemudahan pelacakan riwayat transaksi otomatis, penukaran poin belanja, dan berbagai kemudahan member lainnya.
            </p>
        </div>

        <!-- Form Side (Right) -->
        <div class="auth-form-side">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">Selamat Datang</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">Silakan masuk menggunakan akun member Anda.</p>
            
            <form action="{{ url('/login') }}" method="POST">
                @csrf
                
                <!-- Email or Username -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Email atau Username</label>
                    <input type="text" name="login" class="form-control" value="{{ old('login') }}" placeholder="Email atau username akun Anda" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                    @error('login')
                        <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Kata Sandi</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Kata sandi Anda" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 3rem 0.85rem 1rem; width: 100%;">
                        <span onclick="togglePasswordVisibility('passwordInput', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-secondary);">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                    @error('password')
                        <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: #e28743;"> Ingat saya
                    </label>
                    <a href="{{ url('/forgot-password') }}" style="font-size: 0.85rem; color: #e28743; text-decoration: none; font-weight: 600;">Lupa Sandi?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" style="background: #e28743; border: none; border-radius: 12px; color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; padding: 0.9rem; width: 100%; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3);" onmouseover="this.style.background='#cf7432'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#e28743'; this.style.transform='none';">
                    Masuk Sekarang
                </button>
            </form>
            
            <p style="color: var(--text-secondary); font-size: 0.88rem; text-align: center; margin-top: 2rem;">
                Belum terdaftar? <a href="{{ url('/register') }}" style="color: #e28743; font-weight: 600; text-decoration: none;">Daftar member di sini</a>
            </p>
        </div>
        
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePasswordVisibility(fieldId, iconContainer) {
        const field = document.getElementById(fieldId);
        const icon = iconContainer.querySelector('i');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
