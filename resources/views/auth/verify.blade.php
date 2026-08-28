@extends('layouts.app')

@section('title', 'Verifikasi WhatsApp OTP - Wistek Topup')

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
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">Verifikasi OTP</h2>
            <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 2rem; line-height: 1.5;">
                Kami telah mengirimkan 6 digit kode OTP pendaftaran via WhatsApp ke nomor <strong style="color: #fff;">{{ session('pending_registration.phone') }}</strong>.
            </p>

            @if(session('success'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            
            <form action="{{ url('/register/verify') }}" method="POST">
                @csrf
                
                <!-- OTP Code Field -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.75rem;">Kode OTP WhatsApp</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" placeholder="Masukkan 6 Digit OTP" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.9rem; width: 100%; text-align: center; font-size: 1.25rem; font-weight: 700; letter-spacing: 0.5rem; font-family: monospace;">
                    @error('otp')
                        <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.5rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" style="background: #e28743; border: none; border-radius: 12px; color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; padding: 0.9rem; width: 100%; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); margin-bottom: 1rem;" onmouseover="this.style.background='#cf7432'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#e28743'; this.style.transform='none';">
                    Verifikasi Akun
                </button>
            </form>

            <!-- Resend OTP Form -->
            <form action="{{ url('/register/resend-otp') }}" method="POST" id="resendForm">
                @csrf
                <p style="color: var(--text-secondary); font-size: 0.82rem; text-align: center; margin-top: 1rem; margin-bottom: 0;">
                    Tidak menerima kode? 
                    <button type="submit" id="resendBtn" style="background: none; border: none; color: #e28743; font-weight: 700; cursor: pointer; font-size: 0.82rem; padding: 0; display: inline; text-decoration: underline;" disabled>
                        Kirim Ulang (<span id="cooldownText">60</span>s)
                    </button>
                </p>
            </form>
            
            <a href="{{ url('/register') }}" style="color: var(--text-secondary); font-size: 0.82rem; text-align: center; text-decoration: none; display: block; margin-top: 1.5rem; font-weight: 600;" onmouseover="this.style.color='#fff';" onmouseout="this.style.color='var(--text-secondary)';">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Pendaftaran
            </a>
        </div>

        <!-- Info Side (Right) -->
        <div class="auth-info-side">
            <div style="background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.15); border-radius: 50%; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                <i class="fa-brands fa-whatsapp" style="font-size: 3rem; color: #10b981; animation: pulse 2s infinite;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem;">Verifikasi Satu Perangkat</h3>
            <p style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.6; max-width: 280px; margin-bottom: 0;">
                Sistem OTP kami mengirimkan kode otentikasi unik ke perangkat WhatsApp Anda untuk memastikan keamanan kepemilikan akun.
            </p>
        </div>
        
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const resendBtn = document.getElementById("resendBtn");
        const cooldownText = document.getElementById("cooldownText");

        let lastSentAt = parseInt("{{ session('pending_registration.last_sent_at') }}") || 0;
        let cooldown = 60;

        function updateTimer() {
            let now = Math.floor(Date.now() / 1000);
            let timePassed = now - lastSentAt;

            if (timePassed < cooldown) {
                let timeLeft = cooldown - timePassed;
                resendBtn.disabled = true;
                resendBtn.style.color = "var(--text-secondary)";
                resendBtn.style.textDecoration = "none";
                resendBtn.style.cursor = "not-allowed";
                cooldownText.innerText = timeLeft;
                setTimeout(updateTimer, 1000);
            } else {
                resendBtn.disabled = false;
                resendBtn.style.color = "#e28743";
                resendBtn.style.textDecoration = "underline";
                resendBtn.style.cursor = "pointer";
                cooldownText.parentNode.innerHTML = "Sekarang";
            }
        }

        if (lastSentAt > 0) {
            updateTimer();
        }
    });
</script>
@endsection
