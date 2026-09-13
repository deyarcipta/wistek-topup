@php
    $authUser = Auth::user();
    $tier = $authUser->tier_level ?? 'regular';
@endphp
<div class="dashboard-sidebar">
    <div style="text-align: center; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
        <div style="position: relative; display: inline-block; margin-bottom: 0.75rem;">
            @if($authUser->profile_photo_path)
                <img src="{{ asset('storage/' . $authUser->profile_photo_path) }}" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2.5px solid {{ $tier === 'platinum' ? '#c084fc' : ($tier === 'gold' ? '#f59e0b' : '#e28743') }}; display: block; box-shadow: 0 0 16px {{ $tier === 'platinum' ? 'rgba(168, 85, 247, 0.45)' : ($tier === 'gold' ? 'rgba(245, 158, 11, 0.45)' : 'rgba(0,0,0,0.35)') }};">
            @else
                <div style="width: 72px; height: 72px; border-radius: 50%; background: {{ $tier === 'platinum' ? 'linear-gradient(135deg, #a855f7 0%, #3b82f6 100%)' : ($tier === 'gold' ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #e28743 0%, #8b5cf6 100%)') }}; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2); font-family: 'Outfit', sans-serif; box-shadow: 0 0 16px {{ $tier === 'platinum' ? 'rgba(168, 85, 247, 0.45)' : ($tier === 'gold' ? 'rgba(245, 158, 11, 0.45)' : 'rgba(0,0,0,0.35)') }}; border: 2px solid rgba(255,255,255,0.2);">
                    {{ strtoupper(substr($authUser->name, 0, 2)) }}
                </div>
            @endif
        </div>
        <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0 0 0.2rem; word-break: break-word;">{{ $authUser->name }}</h4>
        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.75rem;">@ {{ $authUser->username }}</div>
        
        <!-- Exclusive Level Badge Pill -->
        @if($tier === 'platinum')
            <div style="background: linear-gradient(135deg, #a855f7, #3b82f6); color: #fff; font-size: 0.72rem; font-weight: 800; padding: 4px 14px; border-radius: 20px; box-shadow: 0 4px 14px rgba(168, 85, 247, 0.4); display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.5px; text-transform: uppercase;">
                <i class="fa-solid fa-gem" style="font-size: 0.75rem;"></i> PLATINUM MEMBER
            </div>
        @elseif($tier === 'gold')
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 0.72rem; font-weight: 800; padding: 4px 14px; border-radius: 20px; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4); display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.5px; text-transform: uppercase;">
                <i class="fa-solid fa-crown" style="font-size: 0.75rem;"></i> GOLD MEMBER
            </div>
        @else
            <div style="background: rgba(255, 255, 255, 0.08); color: #cbd5e1; font-size: 0.72rem; font-weight: 700; padding: 4px 14px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.15); display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.5px; text-transform: uppercase;">
                <i class="fa-solid fa-user-shield" style="font-size: 0.75rem;"></i> REGULAR MEMBER
            </div>
        @endif
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
