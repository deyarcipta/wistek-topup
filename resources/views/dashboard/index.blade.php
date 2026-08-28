@extends('layouts.app')

@section('title', 'Dashboard Member - Wistek Topup')

@section('styles')
<style>
    .dashboard-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        margin: 2rem 0;
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
        padding: 0.85rem 1rem;
        color: var(--text-secondary);
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
    }

    .dashboard-menu-link:hover, .dashboard-menu-link.active {
        background: rgba(226, 135, 67, 0.1);
        color: #e28743;
    }

    .dashboard-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.75rem;
    }

    .stat-badge {
        background: rgba(226, 135, 67, 0.15);
        color: #e28743;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        text-transform: uppercase;
        width: fit-content;
    }

    @media (max-width: 768px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="dashboard-layout">
        
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
            
            <!-- Welcome Bar -->
            <div class="dashboard-card" style="background: linear-gradient(135deg, rgba(226, 135, 67, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 0.25rem;">Halo, {{ $user->name }}!</h2>
                    <p style="color: var(--text-secondary); font-size: 0.88rem; margin: 0;">Selamat datang di dashboard member Anda. Nikmati kemudahan transaksi instan Wistek.</p>
                </div>
                <div class="stat-badge">
                    MEMBER AKTIF
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                <!-- Stat Card 1: Poin Balance -->
                <div class="dashboard-card" style="display: flex; align-items: center; gap: 1.25rem; border-color: rgba(226, 135, 67, 0.2);">
                    <div style="background: rgba(226, 135, 67, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(226, 135, 67, 0.2);">
                        <i class="fa-solid fa-gift" style="font-size: 1.5rem; color: #e28743;"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Saldo Poin Anda</span>
                        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0;">{{ number_format($user->points_balance, 0, ',', '.') }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-secondary);">Pts</span></h3>
                        @if($expiringPoints > 0)
                            <span style="font-size: 0.72rem; color: var(--danger); display: block; margin-top: 0.2rem;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ number_format($expiringPoints) }} Pts akan hangus bln ini
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Stat Card 2: Total Spent -->
                <div class="dashboard-card" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: rgba(16, 185, 129, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(16, 185, 129, 0.2);">
                        <i class="fa-solid fa-wallet" style="font-size: 1.5rem; color: #10b981;"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Total Pengeluaran</span>
                        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0;">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <!-- Stat Card 3: Total Transactions -->
                <div class="dashboard-card" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: rgba(59, 130, 246, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(59, 130, 246, 0.2);">
                        <i class="fa-solid fa-cart-shopping" style="font-size: 1.5rem; color: #3b82f6;"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Transaksi Sukses</span>
                        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0;">{{ $totalTransactions }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-secondary);">Order</span></h3>
                    </div>
                </div>
            </div>

            <!-- Referral Sharing Card -->
            <div class="dashboard-card" style="border-color: rgba(139, 92, 246, 0.2); background: radial-gradient(circle at 100% 0%, rgba(139, 92, 246, 0.05) 0%, transparent 40%);">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-gift" style="color: #8b5cf6;"></i> Program Referral WISTEK
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.5rem;">
                    Undang teman Anda untuk mendaftar akun di Wistek Topup. Anda akan mendapatkan bonus **1.000 poin loyalti** secara otomatis setelah teman yang Anda undang berhasil melakukan transaksi pembelian sukses pertama mereka.
                </p>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; max-width: 600px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 12px; padding: 0.85rem 1rem; color: #fff; font-family: monospace; font-size: 1rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; flex: 1; min-width: 250px; flex-wrap: wrap; gap: 0.5rem;">
                        <span>{{ $user->referral_code }}</span>
                        <div style="display: flex; gap: 1rem;">
                            <button onclick="copyReferralCode()" style="background: none; border: none; color: #e28743; cursor: pointer; font-size: 0.9rem; font-family: 'Outfit', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-regular fa-copy"></i> Salin Kode
                            </button>
                            <button onclick="copyReferralLink()" style="background: none; border: none; color: #8b5cf6; cursor: pointer; font-size: 0.9rem; font-family: 'Outfit', sans-serif; font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-link"></i> Salin Link Daftar
                            </button>
                        </div>
                    </div>
                </div>
                <span id="copyMessage" style="color: #10b981; font-size: 0.75rem; margin-top: 0.5rem; display: none;"><i class="fa-solid fa-check"></i> Kode berhasil disalin!</span>
            </div>

            <!-- Recent Transactions Table -->
            <div class="dashboard-card">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem;">
                    5 Transaksi Terakhir
                </h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">
                                <th style="padding: 0.75rem 1rem;">Invoice</th>
                                <th style="padding: 0.75rem 1rem;">Produk</th>
                                <th style="padding: 0.75rem 1rem;">Target</th>
                                <th style="padding: 0.75rem 1rem;">Total Bayar</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Status</th>
                                <th style="padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); color: #fff;">
                                    <td style="padding: 1rem; font-family: monospace;">{{ $tx->invoice }}</td>
                                    <td style="padding: 1rem;">
                                        <strong>{{ $tx->product_name }}</strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--text-secondary);">{{ $tx->category_name }}</span>
                                    </td>
                                    <td style="padding: 1rem; font-family: monospace;">{{ $tx->target_no }}</td>
                                    <td style="padding: 1rem; font-weight: 600;">Rp {{ number_format($tx->price, 0, ',', '.') }}</td>
                                    <td style="padding: 1rem; text-align: center;">
                                        @if($tx->payment_status === 'paid')
                                            <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">LUNAS</span>
                                        @elseif($tx->payment_status === 'unpaid')
                                            <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">BELUM BAYAR</span>
                                        @else
                                            <span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">{{ strtoupper($tx->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <a href="{{ url('/transaction/' . $tx->invoice) }}" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s;" onmouseover="this.style.background='rgba(226,135,67,0.1)'; this.style.borderColor='#e28743'; this.style.color='#e28743';" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='var(--border-color)'; this.style.color='#fff';">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--text-secondary);">
                                        Belum ada riwayat transaksi belanja.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyReferralCode() {
        const referralCode = "{{ $user->referral_code }}";
        
        navigator.clipboard.writeText(referralCode).then(function() {
            const message = document.getElementById('copyMessage');
            message.innerHTML = '<i class="fa-solid fa-check"></i> Kode referral berhasil disalin ke clipboard!';
            message.style.color = '#10b981';
            message.style.display = 'block';
            setTimeout(function() {
                message.style.display = 'none';
            }, 3000);
        });
    }

    function copyReferralLink() {
        const referralCode = "{{ $user->referral_code }}";
        const referralLink = "{{ url('/register') }}?ref=" + referralCode;
        
        navigator.clipboard.writeText(referralLink).then(function() {
            const message = document.getElementById('copyMessage');
            message.innerHTML = '<i class="fa-solid fa-check"></i> Link daftar referral berhasil disalin ke clipboard!';
            message.style.color = '#8b5cf6';
            message.style.display = 'block';
            setTimeout(function() {
                message.style.display = 'none';
            }, 3000);
        });
    }
</script>
@endsection
