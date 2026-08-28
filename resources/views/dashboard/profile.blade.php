@extends('layouts.app')

@section('title', 'Edit Profil Member - Wistek Topup')

@section('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        margin: 2rem auto;
        max-width: 1200px;
    }

    .dashboard-sidebar {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        height: fit-content;
    }

    .dashboard-menu-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--text-secondary);
        text-decoration: none;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s;
        margin-bottom: 0.5rem;
    }

    .dashboard-menu-link:hover {
        background: rgba(255, 255, 255, 0.03);
        color: #fff;
    }

    .dashboard-menu-link.active {
        background: rgba(226, 135, 67, 0.1);
        color: #e28743;
    }

    .dashboard-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: #fff;
        padding: 0.75rem 1rem;
        width: 100%;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #e28743;
        background: rgba(255, 255, 255, 0.05);
        outline: none;
    }

    .btn-submit {
        background: #e28743;
        border: none;
        border-radius: 12px;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        padding: 0.85rem 2rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3);
    }

    .btn-submit:hover {
        background: #cf7432;
        transform: translateY(-1px);
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-top: 1rem; padding-bottom: 3rem;">
    
    <div class="dashboard-grid">
        
        <!-- Sidebar Navigation -->
        <div class="dashboard-sidebar">
            <div style="text-align: center; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 2px solid #e28743; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                @else
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #e28743 0%, #8b5cf6 100%); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2); font-family: 'Outfit', sans-serif;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0 0 0.25rem;">{{ $user->name }}</h4>
                <span style="font-size: 0.8rem; color: var(--text-secondary);">@ {{$user->username}}</span>
            </div>
            
            <a href="{{ url('/dashboard') }}" class="dashboard-menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Ringkasan Akun
            </a>
            <a href="{{ url('/dashboard/transactions') }}" class="dashboard-menu-link {{ Request::is('dashboard/transactions*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Riwayat Transaksi
            </a>
            <a href="{{ url('/dashboard/points') }}" class="dashboard-menu-link {{ Request::is('dashboard/points*') ? 'active' : '' }}">
                <i class="fa-solid fa-gift"></i> Riwayat Poin
            </a>
            <a href="{{ url('/dashboard/profile') }}" class="dashboard-menu-link {{ Request::is('dashboard/profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear"></i> Edit Profil
            </a>
            
            <form action="{{ url('/logout') }}" method="POST" style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                @csrf
                <button type="submit" class="dashboard-menu-link" style="background: none; border: none; width: 100%; cursor: pointer; text-align: left;">
                    <i class="fa-solid fa-right-from-bracket" style="color: var(--danger);"></i> Keluar Akun
                </button>
            </form>
        </div>
        
        <!-- Main Content Area -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div class="dashboard-card">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-pen" style="color: #e28743;"></i> Edit Profil Saya
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 2rem;">Perbarui data profil member dan kata sandi keamanan Anda.</p>

                @if(session('success'))
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ url('/dashboard/profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Profile Photo Selection -->
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; background: rgba(255, 255, 255, 0.01); border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem;">
                        <div style="position: relative;">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" id="avatarPreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e28743; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: block;">
                            @else
                                <div id="avatarPlaceholder" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #e28743 0%, #8b5cf6 100%); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <img id="avatarPreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e28743; box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: none;">
                            @endif
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #fff; margin-bottom: 0.5rem;">Foto Profil (Avatar)</label>
                            <input type="file" name="photo" id="photoInput" accept="image/png, image/jpeg, image/jpg" style="display: none;" onchange="previewImage(this)">
                            <button type="button" onclick="document.getElementById('photoInput').click()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 0.82rem; font-weight: 700; padding: 0.5rem 1rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Pilih Foto
                            </button>
                            <span style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">Format JPG, JPEG, PNG. Maksimal 2MB.</span>
                            @error('photo')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-grid-2" style="margin-bottom: 1.5rem;">
                        <!-- Name -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Username (Readonly) -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" value="{{ $user->username }}" readonly style="opacity: 0.5; cursor: not-allowed; background: rgba(255,255,255,0.01);">
                            <span style="color: var(--text-secondary); font-size: 0.72rem; margin-top: 0.25rem; display: block;">Username tidak dapat diubah.</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Email -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="email">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="phone">Nomor WhatsApp</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required placeholder="Misal: 08123456789">
                            @error('phone')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">Ubah Kata Sandi (Opsional)</h4>

                    <div class="form-group">
                        <label for="current_password">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Wajib diisi jika ingin mengubah kata sandi">
                        @error('current_password')
                            <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-grid-2" style="margin-bottom: 2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="password">Kata Sandi Baru</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter">
                            @error('password')
                                <span style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const placeholder = document.getElementById('avatarPlaceholder');
                
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
