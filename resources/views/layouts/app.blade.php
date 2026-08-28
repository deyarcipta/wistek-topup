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
            grid-template-columns: 2fr 1fr 1.5fr;
            gap: 4rem;
        }
        @media (max-width: 768px) {
            .menu-toggle {
                display: block !important;
            }
            .navbar {
                position: relative;
                height: 70px !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0 1.5rem !important;
                gap: 0 !important;
            }
            .nav-links {
                display: none !important;
                position: absolute !important;
                top: 70px !important;
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
            <a href="{{ url('/') }}" class="logo" style="display: flex; align-items: center; gap: 0.5rem;">
                <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 32px; object-fit: contain;">
                Wistek<span>Topup</span>
            </a>
            <nav class="nav-links" id="navLinks" style="display: flex; align-items: center; gap: 1.25rem;">
                <a href="{{ url('/') }}" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
                <a href="{{ url('/history') }}" class="nav-link"><i class="fa-solid fa-receipt"></i> Cek Transaksi</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-link" style="color: #e28743; display: inline-flex; align-items: center; gap: 0.35rem;">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover; border: 1px solid #e28743;">
                        @else
                            <i class="fa-solid fa-user"></i>
                        @endif
                        {{ Auth::user()->name }} ({{ number_format(Auth::user()->points_balance) }} Pts)
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
            
            <!-- Column 1: Brand Info -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem; text-align: left;">
                <a href="{{ url('/') }}" class="logo" style="font-size: 1.75rem; width: fit-content; display: flex; align-items: center; gap: 0.5rem;">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 38px; object-fit: contain;">
                    Wistek<span style="color: var(--text-secondary); font-weight: 400;">Topup</span>
                </a>
                <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6; max-width: 480px; margin: 0;">
                    Wistek Topup adalah platform penyedia layanan top-up game online terpercaya, tercepat, dan terlengkap di Indonesia. Kami menghadirkan proses transaksi instan otomatis 24 jam nonstop dengan dukungan pembayaran lengkap dan biaya admin termurah.
                </p>
                <!-- Social Media Buttons -->
                <div style="display: flex; gap: 0.75rem;">
                    <a href="https://instagram.com" target="_blank" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-instagram" style="font-size: 1.1rem;"></i>
                    </a>
                    <a href="https://tiktok.com" target="_blank" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-tiktok" style="font-size: 1.1rem;"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-youtube" style="font-size: 1.1rem;"></i>
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.color='#fff'; this.style.borderColor='#e28743'; this.style.background='rgba(226,135,67,0.1)'; this.style.transform='translateY(-3px)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)'; this.style.background='rgba(255,255,255,0.03)'; this.style.transform='none';">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i>
                    </a>
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
                    <a href="https://wa.me/6281234567890" target="_blank" style="font-size: 0.88rem; color: var(--text-secondary); transition: all 0.2s;" onmouseover="this.style.color='#fff'; this.style.paddingLeft='4px';" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0';"><i class="fa-solid fa-angle-right" style="font-size: 0.75rem; margin-right: 0.4rem; color: #e28743;"></i> Hubungi CS</a>
                </div>
            </div>

            <!-- Column 3: Payment Partners Showcase -->
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
    </script>
</body>
</html>
