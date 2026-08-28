@extends('layouts.app')

@section('title', 'Riwayat Transaksi - Wistek Topup')

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
                Semua Riwayat Transaksi
            </h2>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-secondary);">
                            <th style="padding: 0.75rem 1rem;">Tanggal</th>
                            <th style="padding: 0.75rem 1rem;">Invoice</th>
                            <th style="padding: 0.75rem 1rem;">Produk</th>
                            <th style="padding: 0.75rem 1rem;">Target</th>
                            <th style="padding: 0.75rem 1rem;">Total Bayar</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">Status Bayar</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">Status Topup</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); color: #fff;">
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $tx->created_at->format('d M Y H:i') }}</td>
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
                                <td style="padding: 1rem; text-align: center;">
                                    @if($tx->topup_status === 'success')
                                        <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">SUKSES</span>
                                    @elseif($tx->topup_status === 'processing')
                                        <span style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">DIPROSES</span>
                                    @elseif($tx->topup_status === 'pending')
                                        <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">PENDING</span>
                                    @else
                                        <span style="background: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px;">GAGAL</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="{{ url('/transaction/' . $tx->invoice) }}" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s;" onmouseover="this.style.background='rgba(226,135,67,0.1)'; this.style.borderColor='#e28743'; this.style.color='#e28743';" onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='var(--border-color)'; this.style.color='#fff';">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 2.5rem; text-align: center; color: var(--text-secondary);">
                                    Belum ada transaksi pembelian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Laravel custom pagination links -->
            @if($transactions->hasPages())
                <div class="pagination">
                    {{ $transactions->links() }}
                </div>
            @endif

        </div>
        
    </div>
</div>
@endsection
