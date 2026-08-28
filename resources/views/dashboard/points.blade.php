@extends('layouts.app')

@section('title', 'Riwayat Poin Loyalti - Wistek Topup')

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

    /* Simple Pagination Style */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }
    .pagination a, .pagination span {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 600;
    }
    .pagination .active {
        background: #e28743;
        border-color: #e28743;
        color: #fff;
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
                @if(Auth::user()->profile_photo_path)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 2px solid #e28743; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                @else
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #e28743 0%, #8b5cf6 100%); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2); font-family: 'Outfit', sans-serif;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                @endif
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0 0 0.25rem;">{{ Auth::user()->name }}</h4>
                <span style="font-size: 0.8rem; color: var(--text-secondary);">@ {{Auth::user()->username}}</span>
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
        <div class="dashboard-card">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 1.5rem;">
                Riwayat Mutasi Poin
            </h2>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">
                            <th style="padding: 0.75rem 1rem;">Tanggal</th>
                            <th style="padding: 0.75rem 1rem;">Keterangan</th>
                            <th style="padding: 0.75rem 1rem;">Tipe</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">Jumlah Poin</th>
                            <th style="padding: 0.75rem 1rem;">Masa Berlaku</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointLogs as $log)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); color: #fff;">
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td style="padding: 1rem;">
                                    <strong>{{ $log->description }}</strong>
                                    @if($log->transaction)
                                        <span style="display: block; font-size: 0.75rem; color: var(--text-secondary);">Invoice: {{ $log->transaction->invoice }}</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    @if($log->type === 'earn')
                                        <span style="color: #10b981; font-size: 0.8rem; font-weight: 600;">Belanja</span>
                                    @elseif($log->type === 'spend')
                                        <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600;">Belanja Poin</span>
                                    @elseif($log->type === 'referral_bonus')
                                        <span style="color: #8b5cf6; font-size: 0.8rem; font-weight: 600;">Bonus Referral</span>
                                    @else
                                        <span style="color: var(--text-secondary); font-size: 0.8rem; font-weight: 600;">Kedaluwarsa</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; text-align: center; font-weight: 700; font-family: monospace;">
                                    @if($log->amount > 0)
                                        <span style="color: #10b981;">+{{ number_format($log->amount) }}</span>
                                    @else
                                        <span style="color: #ef4444;">{{ number_format($log->amount) }}</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    @if($log->is_expired)
                                        <span style="color: var(--danger); font-size: 0.78rem; font-weight: 600;">HANGUS (EXPIRED)</span>
                                    @elseif($log->expired_at)
                                        @if($log->expired_at->isPast())
                                            <span style="color: var(--danger); font-size: 0.78rem;">Hangus</span>
                                        @else
                                            <span style="color: var(--text-secondary); font-size: 0.78rem;">s/d {{ $log->expired_at->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span style="color: var(--text-secondary); font-size: 0.78rem;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 2.5rem; text-align: center; color: var(--text-secondary);">
                                    Belum ada catatan mutasi poin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Laravel custom pagination links -->
            @if($pointLogs->hasPages())
                <div class="pagination">
                    {{ $pointLogs->links() }}
                </div>
            @endif

        </div>
        
    </div>
</div>
@endsection
