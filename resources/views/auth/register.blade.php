@extends('layouts.app')

@section('title', 'Daftar Member Baru - Wistek Topup')

@section('styles')
<style>
    .auth-container {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        min-height: 700px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        overflow: hidden;
        margin: 2rem auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    .auth-form-side {
        padding: 3.5rem 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .auth-info-side {
        background: linear-gradient(135deg, rgba(226, 135, 67, 0.15), rgba(139, 92, 246, 0.15)), 
                    radial-gradient(circle at 80% 20%, rgba(226, 135, 67, 0.25) 0%, transparent 50%),
                    radial-gradient(circle at 20% 80%, rgba(139, 92, 246, 0.25) 0%, transparent 50%);
        border-left: 1px solid var(--border-color);
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
    }

    .auth-input-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 768px) {
        .auth-container {
            grid-template-columns: 1fr;
            min-height: auto;
        }
        .auth-info-side {
            display: none;
        }
        .auth-form-side {
            padding: 2.5rem 1.5rem;
        }
        .auth-input-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="auth-container">
        
        <!-- Form Side (Left) -->
        <div class="auth-form-side">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">Daftar Akun</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">Masukkan informasi pendaftaran yang valid untuk bergabung.</p>
            
            <form action="{{ url('/register') }}" method="POST">
                @csrf
                
                <div class="auth-input-grid">
                    <!-- Nama Lengkap -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama Lengkap" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                        @error('name')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Username" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                        @error('username')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="auth-input-grid">
                    <!-- Alamat Email -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Alamat Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@example.com" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                        @error('email')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nomor Whatsapp -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Nomor Whatsapp</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.9rem; color: var(--text-secondary); font-weight: 600;">+62</span>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="81234567xxx" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem 0.85rem 3.2rem; width: 100%;">
                        </div>
                        @error('phone')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="auth-input-grid">
                    <!-- Kata Sandi -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Kata Sandi</label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Min. 6 karakter" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 3rem 0.85rem 1rem; width: 100%;">
                            <span onclick="togglePasswordVisibility('passwordInput', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-secondary);">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        @error('password')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Konfirmasi Kata Sandi</label>
                        <div style="position: relative;">
                            <input type="password" name="password_confirmation" id="passwordConfirmationInput" class="form-control" placeholder="Ketik ulang kata sandi" required style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 3rem 0.85rem 1rem; width: 100%;">
                            <span onclick="togglePasswordVisibility('passwordConfirmationInput', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-secondary);">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kode Referral (Opsional) -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem;">Kode Referral (Opsional)</label>
                    <input type="text" name="referral_code" class="form-control" value="{{ request('ref') ?? old('referral_code') }}" placeholder="Misal: WSTK-XXXXXX" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; padding: 0.85rem 1rem; width: 100%;">
                    @error('referral_code')
                        <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" style="background: #e28743; border: none; border-radius: 12px; color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; padding: 0.9rem; width: 100%; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); margin-top: 0.5rem;" onmouseover="this.style.background='#cf7432'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#e28743'; this.style.transform='none';">
                    Daftar Sekarang
                </button>
            </form>
            
            <p style="color: var(--text-secondary); font-size: 0.88rem; text-align: center; margin-top: 2rem;">
                Sudah memiliki akun? <a href="{{ url('/login') }}" style="color: #e28743; font-weight: 600; text-decoration: none;">Masuk di sini</a>
            </p>
        </div>

        <!-- Info Side (Right) -->
        <div class="auth-info-side">
            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <i class="fa-solid fa-gift" style="font-size: 3rem; color: #e28743; animation: pulse 2s infinite;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 1rem;">Bagikan Referral & Dapatkan Poin!</h3>
            <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.6; max-width: 320px; margin-bottom: 2rem;">
                Dapatkan bonus poin loyalti secara gratis setiap kali teman yang Anda referensikan melakukan transaksi sukses pertama mereka. Poin dapat digunakan sebagai potongan harga belanja!
            </p>
            <div style="display: flex; gap: 0.5rem; justify-content: center; width: 100%;">
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-check" style="color: #10b981;"></i> 1 Perangkat 1 Referral
                </div>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-check" style="color: #10b981;"></i> Masa Berlaku 6 Bulan
                </div>
            </div>
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
