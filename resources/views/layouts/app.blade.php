<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Topup Wistek - Sistem Topup Otomatis')</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.0.2') }}">
    
    @yield('styles')
    
    <style>
        /* Menu Toggle Button Styling */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.2s;
        }
        
        .menu-toggle:hover {
            color: #e28743;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr 1.2fr 1.2fr;
            gap: 2.5rem;
        }

        .nav-search-container {
            flex: 1;
            max-width: 440px;
            margin: 0 1.5rem;
            position: relative;
        }

        .nav-search-container input {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(226, 135, 67, 0.25);
            border-radius: 30px;
            padding: 0.55rem 2.4rem 0.55rem 2.4rem;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            outline: none;
            transition: all 0.25s ease;
        }

        .nav-search-container input:focus {
            border-color: #e28743;
            background: rgba(18, 18, 22, 0.95);
            box-shadow: 0 0 18px rgba(226, 135, 67, 0.35);
        }

        @media (max-width: 992px) {
            .nav-search-container {
                max-width: 280px;
                margin: 0 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block !important;
            }
            .navbar {
                position: relative;
                height: auto !important;
                min-height: 70px !important;
                flex-wrap: wrap !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0.75rem 1.25rem !important;
                gap: 0.75rem !important;
            }
            .nav-search-container {
                order: 3;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .nav-links {
                display: none !important;
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                width: 100% !important;
                background: rgba(8, 9, 12, 0.98) !important;
                backdrop-filter: blur(16px) !important;
                border-bottom: 1px solid var(--border-color) !important;
                flex-direction: column !important;
                padding: 1.5rem !important;
                gap: 1rem !important;
                margin-top: 0 !important;
                align-items: flex-start !important;
                z-index: 1000 !important;
                box-shadow: 0 10px 15px rgba(0,0,0,0.5) !important;
            }
            .nav-links.open {
                display: flex !important;
            }
            .nav-link {
                font-size: 0.95rem !important;
                width: 100% !important;
                padding: 0.5rem 0 !important;
                border-bottom: 1px solid rgba(255,255,255,0.02) !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            .nav-link:last-child {
                border-bottom: none !important;
            }
            .hero {
                padding: 3rem 0 2rem !important;
            }
            .hero h1 {
                font-size: 2.25rem !important;
            }
            .hero p {
                font-size: 0.95rem !important;
            }
            .sidebar-info {
                position: relative !important;
                top: 0 !important;
                padding: 1.5rem !important;
                text-align: center !important;
            }
            .sidebar-info img {
                margin: 0 auto 1.25rem !important;
            }
            .dashboard-layout, .dashboard-grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
            .dashboard-card, .dashboard-sidebar {
                min-width: 0 !important;
            }
            .footer-grid {
                grid-template-columns: 1fr !important;
                gap: 2.5rem !important;
            }
            .footer-grid > div {
                text-align: center !important;
                align-items: center !important;
            }
            .footer-grid .logo {
                margin: 0 auto !important;
            }
            .footer-grid div[style*="flex"] {
                justify-content: center !important;
            }
            .footer-bottom {
                flex-direction: column !important;
                text-align: center !important;
                gap: 1.5rem !important;
            }
            .footer-sitemap-links {
                flex-direction: row !important;
                justify-content: center !important;
                gap: 1rem !important;
                flex-wrap: wrap !important;
            }
            .footer-sitemap-links a i {
                display: none !important;
            }
            .footer-sitemap-links a {
                border-right: 1px solid rgba(255,255,255,0.1);
                padding-right: 1rem;
            }
            .footer-sitemap-links a:last-child {
                border-right: none !important;
                padding-right: 0 !important;
            }
            .footer-payment-grid {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 0.5rem !important;
                max-width: 100% !important;
            }
            .footer-payment-grid div {
                flex: 0 1 auto !important;
                padding: 0.35rem 0.75rem !important;
                font-size: 0.7rem !important;
            }
        }
        
        @media (max-width: 480px) {
            .nominal-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
                gap: 0.75rem !important;
            }
            .nominal-card {
                padding: 1rem !important;
            }
            .nominal-name {
                font-size: 0.85rem !important;
            }
            .nominal-price {
                font-size: 0.85rem !important;
            }
            .payment-grid-layout {
                grid-template-columns: 1fr !important;
            }
            .container {
                padding: 0 1rem !important;
            }
            .section-title {
                font-size: 1.3rem !important;
            }

            /* Hide payment logo badges in accordion headers on mobile */
            .accordion-header-logos {
                display: none !important;
            }

            /* Tighten accordion sizes on mobile */
            .accordion-header {
                padding: 0.85rem 1rem !important;
            }
            .accordion-title {
                font-size: 0.82rem !important;
            }
            .payment-row-item {
                padding: 0.65rem 0.85rem !important;
            }
            .payment-name-txt, .payment-row-price {
                font-size: 0.8rem !important;
            }
        }

        /* Hide the redundant '+' sign in payment accordion titles */
        .accordion-header .accordion-title::after {
            content: none !important;
        }

        .dashboard-layout > *, .dashboard-grid > * {
            min-width: 0 !important;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <header>
        <div class="container navbar">
            <a href="{{ url('/') }}" class="logo" style="display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 32px; object-fit: contain;">
                Wistek<span>Topup</span>
            </a>

            @php
                $navCategoriesRaw = \App\Models\Category::where('status', true)
                    ->select('id', 'name', 'slug', 'thumbnail', 'type', 'is_maintenance')
                    ->orderBy('name', 'asc')
                    ->get();
                    
                $navSearchCategories = $navCategoriesRaw->map(function ($cat) {
                    $thumb = $cat->thumbnail;
                    if ($thumb && !\Illuminate\Support\Str::startsWith($thumb, ['http://', 'https://'])) {
                        $thumb = asset('storage/' . ltrim($thumb, '/'));
                    }
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'thumbnail' => $thumb,
                        'type' => match($cat->type) {
                            'game' => 'Game',
                            'pulsa' => 'Pulsa & Data',
                            'emoney' => 'E-Money',
                            'pln' => 'PLN Listrik',
                            'tagihan' => 'Tagihan',
                            'voucher' => 'Voucher',
                            'streaming' => 'Streaming',
                            default => ucfirst($cat->type ?? 'Game'),
                        },
                        'is_maintenance' => (bool) $cat->is_maintenance,
                        'url' => url('/category/' . $cat->slug),
                    ];
                });
            @endphp

            <!-- Navbar Live Search Bar with Autocomplete Dropdown -->
            <div class="nav-search-container" style="position: relative;">
                <form action="{{ url('/') }}" method="GET" id="navSearchForm" onsubmit="handleNavSearchSubmit(event)">
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; color: #e28743; font-size: 0.88rem; pointer-events: none; z-index: 2;"></i>
                        <input type="text" id="navSearchInput" name="search" placeholder="Cari game atau layanan (misal: Mobile Legends, Free Fire)..." value="{{ request('search') }}" autocomplete="off" oninput="handleNavSearchInput(this.value)" onfocus="handleNavSearchFocus(this.value)" onkeydown="handleNavSearchKeyDown(event)">
                        <button type="button" id="navClearSearch" onclick="clearNavSearch()" style="display: {{ request('search') ? 'block' : 'none' }}; position: absolute; right: 0.85rem; background: none; border: none; color: var(--text-secondary); font-size: 0.9rem; cursor: pointer; padding: 0.2rem; transition: color 0.2s; z-index: 2;" onmouseover="this.style.color='#fff';" onmouseout="this.style.color='var(--text-secondary)';">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </form>

                <!-- Instant Autocomplete Results Dropdown -->
                <div id="navSearchDropdown" style="display: none; position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: rgba(18, 19, 26, 0.98); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(226, 135, 67, 0.35); border-radius: 16px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 30px rgba(226, 135, 67, 0.15); max-height: 380px; overflow-y: auto; z-index: 10000; padding: 0.5rem;">
                    <div id="navSearchResultsList" style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <!-- Rendered dynamically -->
                    </div>
                </div>
            </div>

            <nav class="nav-links" id="navLinks" style="display: flex; align-items: center; gap: 1.25rem;">
                <a href="{{ url('/') }}" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
                <a href="{{ url('/history') }}" class="nav-link"><i class="fa-solid fa-receipt"></i> Cek Transaksi</a>
                @auth
                    @php
                        $user = Auth::user();
                        $targetUrl = $user->isMember() ? url('/dashboard') : url('/w1st3k');
                    @endphp
                    <a href="{{ $targetUrl }}" class="nav-link" style="color: #e28743; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap;">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover; border: 1px solid #e28743; flex-shrink: 0;">
                        @else
                            <i class="fa-solid fa-user" style="flex-shrink: 0;"></i>
                        @endif
                        <span style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: middle;" title="{{ $user->name }}">
                            {{ $user->name }}
                        </span>
                        @if($user->isMember())
                            <span style="font-size: 0.78rem; opacity: 0.85; color: var(--text-secondary); font-weight: 600; flex-shrink: 0;">({{ number_format($user->points_balance) }} Pts)</span>
                        @elseif($user->isAdmin())
                            <span style="font-size: 0.78rem; opacity: 0.85; flex-shrink: 0;">(Admin)</span>
                        @elseif($user->isCashier())
                            <span style="font-size: 0.78rem; opacity: 0.85; flex-shrink: 0;">(Petugas)</span>
                        @endif
                    </a>
                @else
                    <a href="{{ url('/login') }}" class="nav-link"><i class="fa-solid fa-right-to-bracket"></i> Masuk</a>
                    <a href="{{ url('/register') }}" class="nav-link" style="background: #e28743; padding: 0.4rem 0.85rem; border-radius: 8px; color: #fff; font-weight: 700;"><i class="fa-solid fa-user-plus"></i> Daftar</a>
                @endauth
            </nav>
            <button class="menu-toggle" id="menuToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main>
        @if($errors->any())
            <div class="container" style="margin-top: 1.5rem; margin-bottom: -1rem;">
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 12px; font-size: 0.95rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem;"></i>
                    {{ $errors->first() }}
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="container" style="margin-top: 1.5rem; margin-bottom: -1rem;">
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 12px; font-size: 0.95rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem;"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @if(session('success'))
            <div class="container" style="margin-top: 1.5rem; margin-bottom: -1rem;">
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 12px; font-size: 0.95rem;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="background: #090a0f; border-top: 1px solid var(--border-color); padding: 4.5rem 0 2rem; color: var(--text-primary); font-family: 'Outfit', sans-serif; margin-top: 2.5rem;">
        <div class="container footer-grid">
            
            @php
                $csWhatsappUrl = \App\Models\Setting::get('cs_whatsapp_url', 'https://wa.me/6281234567890');
                $socialInstagram = \App\Models\Setting::get('social_instagram', 'https://instagram.com');
                $socialTiktok = \App\Models\Setting::get('social_tiktok', 'https://tiktok.com');
                $socialYoutube = \App\Models\Setting::get('social_youtube', 'https://youtube.com');
                $socialWhatsapp = \App\Models\Setting::get('social_whatsapp', $csWhatsappUrl);
            @endphp

            <!-- Column 1: Brand Info -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
                <a href="{{ url('/') }}" class="logo" style="font-size: 1.75rem; width: fit-content; display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 38px; object-fit: contain;">
                    Wistek<span style="color: var(--text-secondary); font-weight: 400;">Topup</span>
                </a>
                <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6; max-width: 480px; margin: 0;">
                    Wistek Topup adalah platform penyedia layanan top-up game online terpercaya, tercepat, dan terlengkap di Indonesia. Kami menghadirkan proses transaksi instan otomatis 24 jam nonstop dengan dukungan pembayaran lengkap dan biaya admin termurah.
                </p>
                <!-- Social Media Buttons -->
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @if(!empty($socialInstagram))
                    <a href="{{ $socialInstagram }}" target="_blank" title="Instagram" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-instagram" style="font-size: 1.1rem;"></i>
                    </a>
                    @endif
                    @if(!empty($socialTiktok))
                    <a href="{{ $socialTiktok }}" target="_blank" title="TikTok" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-tiktok" style="font-size: 1.1rem;"></i>
                    </a>
                    @endif
                    @if(!empty($socialYoutube))
                    <a href="{{ $socialYoutube }}" target="_blank" title="YouTube" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-youtube" style="font-size: 1.1rem;"></i>
                    </a>
                    @endif
                    @if(!empty($socialWhatsapp))
                    <a href="{{ $socialWhatsapp }}" target="_blank" title="WhatsApp" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Column 2: Sitemap Quicklinks -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
                <h4 style="font-size: 1.1rem; font-weight: 700; color: #fff; position: relative; margin: 0; padding-bottom: 0.5rem;">
                    Peta Situs
                    <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 2px; background: #e28743; border-radius: 1px;"></span>
                </h4>
                <div class="footer-sitemap-links" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ url('/') }}" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Halaman Utama</a>
                    <a href="{{ url('/history') }}" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Cek Transaksi</a>
                    @if(!empty($csWhatsappUrl))
                    <a href="{{ $csWhatsappUrl }}" target="_blank" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Hubungi CS</a>
                    @endif
                </div>
            </div>

            <!-- Column 3: Legal & Policy Documents -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
                <h4 style="font-size: 1.1rem; font-weight: 700; color: #fff; position: relative; margin: 0; padding-bottom: 0.5rem;">
                    Legalitas & Kebijakan
                    <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 2px; background: #e28743; border-radius: 1px;"></span>
                </h4>
                <div class="footer-sitemap-links" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ url('/refund-policy') }}" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Kebijakan Refund</a>
                    <a href="{{ url('/terms-and-conditions') }}" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Syarat & Ketentuan</a>
                    <a href="{{ url('/privacy-policy') }}" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Kebijakan Privasi</a>
                </div>
            </div>

            <!-- Column 4: Payment Partners Showcase -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
                <h4 style="font-size: 1.1rem; font-weight: 700; color: #fff; position: relative; margin: 0; padding-bottom: 0.5rem;">
                    Pembayaran Aman
                    <span style="position: absolute; bottom: 0; left: 0; width: 30px; height: 2px; background: #e28743; border-radius: 1px;"></span>
                </h4>
                <p style="font-size: 0.82rem; color: var(--text-secondary); margin: 0; line-height: 1.5;">
                    Kami mendukung berbagai macam metode pembayaran aman dan otomatis untuk memudahkan proses belanja Anda.
                </p>
                <div class="footer-payment-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; max-width: 280px;">
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #fff;">QRIS</div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #3b82f6;">DANA</div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #10b981;">OVO</div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #ef4444;">Shopee</div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #3b82f6;">BCA</div>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.4rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #e28743;">Mandiri</div>
                </div>
            </div>

        </div>

        <!-- Bottom Copyright Bar -->
        <div class="container footer-bottom" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 2rem; margin-top: 3.5rem; text-align: center;">
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0; text-align: center;">
                Copyright &copy; {{ date('Y') }} Wistek Topup by WISTEK. All Rights Reserved.
            </p>
        </div>
    </footer>

    <!-- Global Custom Modal Component -->
    <div id="customModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(5, 6, 9, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 99999; align-items: center; justify-content: center; padding: 1.25rem; opacity: 0; transition: opacity 0.25s ease-in-out;">
        <div id="customModalBox" style="background: rgba(18, 19, 26, 0.96); border: 1px solid rgba(226, 135, 67, 0.35); border-radius: 24px; width: 100%; max-width: 440px; padding: 2.25rem 1.75rem; text-align: center; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7), 0 0 35px rgba(226, 135, 67, 0.15); transform: scale(0.88); transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); position: relative; overflow: hidden;">
            
            <!-- Background Glow Effect -->
            <div id="customModalGlow" style="position: absolute; top: -60px; left: 50%; transform: translateX(-50%); width: 180px; height: 180px; background: rgba(226, 135, 67, 0.25); filter: blur(55px); border-radius: 50%; pointer-events: none;"></div>

            <!-- Close Button (X) -->
            <button type="button" onclick="closeCustomModal()" style="position: absolute; top: 1rem; right: 1rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-secondary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.background='rgba(255, 255, 255, 0.15)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.background='rgba(255, 255, 255, 0.05)';">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Icon Header -->
            <div id="customModalIconBg" style="width: 68px; height: 68px; border-radius: 50%; background: rgba(226, 135, 67, 0.12); border: 1px solid rgba(226, 135, 67, 0.3); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; box-shadow: 0 8px 20px rgba(226, 135, 67, 0.2);">
                <i id="customModalIcon" class="fa-solid fa-triangle-exclamation" style="font-size: 1.85rem; color: #e28743;"></i>
            </div>

            <!-- Title -->
            <h3 id="customModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0 0 0.75rem 0; line-height: 1.3;">Pemberitahuan</h3>

            <!-- Message Body -->
            <div id="customModalMessage" style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.75rem; white-space: pre-line;">
                Pesan modal akan muncul di sini.
            </div>

            <!-- Action Button -->
            <button type="button" id="customModalBtn" onclick="closeCustomModal()" style="width: 100%; background: linear-gradient(135deg, #e28743, #d97706); color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; padding: 0.85rem 1.5rem; border-radius: 14px; border: none; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 6px 20px rgba(226, 135, 67, 0.35);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(226, 135, 67, 0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 20px rgba(226, 135, 67, 0.35)';">
                <span id="customModalBtnText">Saya Mengerti</span>
            </button>
        </div>
    </div>

    @yield('scripts')
    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('open');
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });

        // ----------------------------------------------------
        // Global Professional Custom Modal System
        // ----------------------------------------------------
        let modalConfirmCallback = null;

        function showCustomModal(options = {}) {
            const title = options.title || 'Pemberitahuan';
            const message = options.message || '';
            const type = options.type || 'warning'; // warning, success, error, info
            const btnText = options.btnText || 'Saya Mengerti';
            modalConfirmCallback = options.onConfirm || null;

            const backdrop = document.getElementById('customModalBackdrop');
            const modalBox = document.getElementById('customModalBox');
            const modalTitle = document.getElementById('customModalTitle');
            const modalMessage = document.getElementById('customModalMessage');
            const modalIcon = document.getElementById('customModalIcon');
            const modalIconBg = document.getElementById('customModalIconBg');
            const modalGlow = document.getElementById('customModalGlow');
            const modalBtn = document.getElementById('customModalBtn');
            const modalBtnText = document.getElementById('customModalBtnText');

            if (!backdrop || !modalBox) return;

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalBtnText.textContent = btnText;

            // Apply color theme according to type
            if (type === 'success') {
                modalIcon.className = 'fa-solid fa-circle-check';
                modalIcon.style.color = '#10b981';
                modalIconBg.style.background = 'rgba(16, 185, 129, 0.12)';
                modalIconBg.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                modalIconBg.style.boxShadow = '0 8px 20px rgba(16, 185, 129, 0.2)';
                modalGlow.style.background = 'rgba(16, 185, 129, 0.25)';
                modalBox.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                modalBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                modalBtn.style.boxShadow = '0 6px 20px rgba(16, 185, 129, 0.35)';
            } else if (type === 'error' || type === 'danger') {
                modalIcon.className = 'fa-solid fa-circle-xmark';
                modalIcon.style.color = '#ef4444';
                modalIconBg.style.background = 'rgba(239, 68, 68, 0.12)';
                modalIconBg.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                modalIconBg.style.boxShadow = '0 8px 20px rgba(239, 68, 68, 0.2)';
                modalGlow.style.background = 'rgba(239, 68, 68, 0.25)';
                modalBox.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                modalBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                modalBtn.style.boxShadow = '0 6px 20px rgba(239, 68, 68, 0.35)';
            } else if (type === 'info') {
                modalIcon.className = 'fa-solid fa-circle-info';
                modalIcon.style.color = '#3b82f6';
                modalIconBg.style.background = 'rgba(59, 130, 246, 0.12)';
                modalIconBg.style.borderColor = 'rgba(59, 130, 246, 0.3)';
                modalIconBg.style.boxShadow = '0 8px 20px rgba(59, 130, 246, 0.2)';
                modalGlow.style.background = 'rgba(59, 130, 246, 0.25)';
                modalBox.style.borderColor = 'rgba(59, 130, 246, 0.3)';
                modalBtn.style.background = 'linear-gradient(135deg, #3b82f6, #2563eb)';
                modalBtn.style.boxShadow = '0 6px 20px rgba(59, 130, 246, 0.35)';
            } else { // warning default
                modalIcon.className = 'fa-solid fa-triangle-exclamation';
                modalIcon.style.color = '#e28743';
                modalIconBg.style.background = 'rgba(226, 135, 67, 0.12)';
                modalIconBg.style.borderColor = 'rgba(226, 135, 67, 0.3)';
                modalIconBg.style.boxShadow = '0 8px 20px rgba(226, 135, 67, 0.2)';
                modalGlow.style.background = 'rgba(226, 135, 67, 0.25)';
                modalBox.style.borderColor = 'rgba(226, 135, 67, 0.3)';
                modalBtn.style.background = 'linear-gradient(135deg, #e28743, #d97706)';
                modalBtn.style.boxShadow = '0 6px 20px rgba(226, 135, 67, 0.35)';
            }

            backdrop.style.display = 'flex';
            requestAnimationFrame(() => {
                backdrop.style.opacity = '1';
                modalBox.style.transform = 'scale(1)';
            });
        }

        function closeCustomModal() {
            const backdrop = document.getElementById('customModalBackdrop');
            const modalBox = document.getElementById('customModalBox');
            if (!backdrop || !modalBox) return;

            backdrop.style.opacity = '0';
            modalBox.style.transform = 'scale(0.88)';
            setTimeout(() => {
                backdrop.style.display = 'none';
                if (typeof modalConfirmCallback === 'function') {
                    const cb = modalConfirmCallback;
                    modalConfirmCallback = null;
                    cb();
                }
            }, 250);
        }

        // Close on ESC key or backdrop click
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const backdrop = document.getElementById('customModalBackdrop');
                if (backdrop && backdrop.style.display !== 'none') {
                    closeCustomModal();
                }
            }
        });

        document.getElementById('customModalBackdrop')?.addEventListener('click', (e) => {
            if (e.target.id === 'customModalBackdrop') {
                closeCustomModal();
            }
        });

        // Override standard browser alert globally
        window.alert = function(msg) {
            showCustomModal({
                title: 'Pemberitahuan',
                message: msg,
                type: 'warning'
            });
        };

        // ----------------------------------------------------
        // Navbar Search Functions with Instant Autocomplete Dropdown
        // ----------------------------------------------------
        const globalNavCategories = @json($navSearchCategories);
        let activeNavSearchIndex = -1;

        function highlightMatch(text, query) {
            if (!query) return text;
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<strong style="color: #e28743; font-weight: 800;">$1</strong>');
        }

        function handleNavSearchInput(val) {
            const clearBtn = document.getElementById('navClearSearch');
            if (clearBtn) {
                clearBtn.style.display = val.trim() ? 'block' : 'none';
            }
            
            // If on homepage, trigger instant live search on grid
            if (typeof handleLiveSearch === 'function') {
                handleLiveSearch(val);
            }

            renderNavSearchDropdown(val);
        }

        function handleNavSearchFocus(val) {
            if (val && val.trim().length > 0) {
                renderNavSearchDropdown(val);
            }
        }

        function renderNavSearchDropdown(val) {
            const dropdown = document.getElementById('navSearchDropdown');
            const list = document.getElementById('navSearchResultsList');
            if (!dropdown || !list) return;

            const query = val ? val.trim().toLowerCase() : '';
            if (!query) {
                dropdown.style.display = 'none';
                activeNavSearchIndex = -1;
                return;
            }

            const matches = globalNavCategories.filter(cat => {
                const name = cat.name ? cat.name.toLowerCase() : '';
                const type = cat.type ? cat.type.toLowerCase() : '';
                const slug = cat.slug ? cat.slug.toLowerCase() : '';
                return name.includes(query) || type.includes(query) || slug.includes(query);
            });

            if (matches.length === 0) {
                list.innerHTML = `
                    <div style="padding: 1.25rem; text-align: center; color: var(--text-secondary); font-size: 0.88rem;">
                        <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 1.4rem; color: #ef4444; margin-bottom: 0.4rem; display: block;"></i>
                        Game atau layanan "<strong style="color: #e28743;">${val.trim()}</strong>" tidak ditemukan.
                    </div>
                `;
            } else {
                list.innerHTML = matches.map((cat, idx) => {
                    const thumb = cat.thumbnail || `https://placehold.co/100x100/1e293b/ffffff?text=${encodeURIComponent(cat.name)}`;
                    const maintenanceBadge = cat.is_maintenance ? `
                        <span style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 0.6rem; font-weight: 800; padding: 2px 7px; border-radius: 10px; text-transform: uppercase; flex-shrink: 0; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);">
                            <i class="fa-solid fa-wrench" style="font-size: 0.55rem; margin-right: 2px;"></i> Maintenance
                        </span>
                    ` : '';

                    return `
                        <a href="${cat.url}" class="nav-search-item" data-index="${idx}" style="display: flex; align-items: center; gap: 0.85rem; padding: 0.65rem 0.85rem; border-radius: 12px; text-decoration: none; transition: all 0.2s ease; background: transparent; border: 1px solid transparent;" onmouseover="this.style.background='rgba(226, 135, 67, 0.14)'; this.style.borderColor='rgba(226, 135, 67, 0.3)';" onmouseout="if(!this.classList.contains('active-search-item')){ this.style.background='transparent'; this.style.borderColor='transparent'; }">
                            <img src="${thumb}" alt="${cat.name}" style="width: 44px; height: 44px; border-radius: 10px; object-fit: cover; border: 1.5px solid rgba(226, 135, 67, 0.25); flex-shrink: 0;" onerror="this.onerror=null; this.src='https://placehold.co/100x100/1e293b/ffffff?text=${encodeURIComponent(cat.name)}';">
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; color: #fff; margin: 0; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        ${highlightMatch(cat.name, val.trim())}
                                    </h4>
                                    ${maintenanceBadge}
                                </div>
                                <span style="font-size: 0.78rem; color: var(--text-secondary); display: block; margin-top: 2px;">${cat.type}</span>
                            </div>
                        </a>
                    `;
                }).join('');
            }

            dropdown.style.display = 'block';
            activeNavSearchIndex = -1;
        }

        function handleNavSearchKeyDown(e) {
            const dropdown = document.getElementById('navSearchDropdown');
            if (!dropdown || dropdown.style.display === 'none') return;

            const items = dropdown.querySelectorAll('.nav-search-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeNavSearchIndex = (activeNavSearchIndex + 1) % items.length;
                updateNavSearchSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeNavSearchIndex = (activeNavSearchIndex - 1 + items.length) % items.length;
                updateNavSearchSelection(items);
            } else if (e.key === 'Enter') {
                if (activeNavSearchIndex >= 0 && items[activeNavSearchIndex]) {
                    e.preventDefault();
                    window.location.href = items[activeNavSearchIndex].getAttribute('href');
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        }

        function updateNavSearchSelection(items) {
            items.forEach((item, idx) => {
                if (idx === activeNavSearchIndex) {
                    item.classList.add('active-search-item');
                    item.style.background = 'rgba(226, 135, 67, 0.2)';
                    item.style.borderColor = '#e28743';
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active-search-item');
                    item.style.background = 'transparent';
                    item.style.borderColor = 'transparent';
                }
            });
        }

        function clearNavSearch() {
            const input = document.getElementById('navSearchInput');
            if (input) {
                input.value = '';
                handleNavSearchInput('');
                input.focus();
            }
        }

        function handleNavSearchSubmit(e) {
            const dropdown = document.getElementById('navSearchDropdown');
            const items = dropdown ? dropdown.querySelectorAll('.nav-search-item') : [];
            if (activeNavSearchIndex >= 0 && items[activeNavSearchIndex]) {
                e.preventDefault();
                window.location.href = items[activeNavSearchIndex].getAttribute('href');
                return;
            }

            const input = document.getElementById('navSearchInput');
            if (!input) return;
            const query = input.value.trim();
            // If already on homepage, prevent page reload and perform search in place
            if (window.location.pathname === '/' || window.location.pathname === '') {
                e.preventDefault();
                if (typeof handleLiveSearch === 'function') {
                    handleLiveSearch(query);
                }
            }
        }

        // Close dropdown on click outside
        document.addEventListener('click', (e) => {
            const searchContainer = document.querySelector('.nav-search-container');
            const dropdown = document.getElementById('navSearchDropdown');
            if (searchContainer && dropdown && !searchContainer.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>
