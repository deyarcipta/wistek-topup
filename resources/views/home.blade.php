@extends('layouts.app')

@section('title', 'Wistek Topup - Sistem Topup Otomatis Terlengkap')

@section('styles')
<style>
    /* Slider/Carousel Styles */
    .slider-container:hover button {
        opacity: 1 !important;
    }
    .slider-dots .dot.active {
        background: #e28743 !important;
        width: 24px !important;
        border-radius: 4px !important;
    }
    .slider-container button {
        opacity: 0.3;
        transition: all 0.2s ease-in-out;
    }
    .slider-container button:hover {
        background: #e28743 !important;
        border-color: #e28743 !important;
        transform: translateY(-50%) scale(1.05);
        opacity: 1 !important;
    }

    /* Filter Tabs Styles */
    .filter-tabs .filter-btn {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
        border-radius: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
    }
    
    .filter-tabs .filter-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-primary);
        border-color: rgba(255, 255, 255, 0.2);
    }
    
    .filter-tabs .filter-btn.active {
        background: #e28743;
        border-color: #e28743;
        color: #fff;
        box-shadow: 0 4px 15px rgba(226, 135, 67, 0.25);
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-bottom: 5rem;">
    
    <!-- Slider / Info Carousel -->
    @if(count($banners) > 0)
        <div class="slider-container" style="position: relative; margin-top: 2rem; border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <div class="slider-wrapper" style="display: flex; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); width: 100%;">
                @foreach($banners as $index => $banner)
                    <div class="slide" style="min-width: 100%; box-sizing: border-box; position: relative;">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}">
                        @endif
                        
                        <img src="{{ asset('storage/' . $banner->image_path) }}" alt="{{ $banner->title ?? 'Promo Banner' }}" style="width: 100%; height: auto; aspect-ratio: 1200/500; object-fit: cover; display: block;">
                        
                        @if($banner->title)
                            <div class="slide-caption" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(8,9,12,0.9) 0%, rgba(8,9,12,0) 100%); padding: 3rem 2rem 2rem; color: #fff;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">{{ $banner->title }}</h3>
                            </div>
                        @endif
                        
                        @if($banner->link_url)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Navigation Arrows -->
            <button onclick="moveSlide(-1)" style="position: absolute; top: 50%; left: 1.5rem; transform: translateY(-50%); background: rgba(8,9,12,0.6); backdrop-filter: blur(4px); border: 1px solid var(--border-color); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;">
                <i class="fa-solid fa-chevron-left" style="font-size: 1rem;"></i>
            </button>
            <button onclick="moveSlide(1)" style="position: absolute; top: 50%; right: 1.5rem; transform: translateY(-50%); background: rgba(8,9,12,0.6); backdrop-filter: blur(4px); border: 1px solid var(--border-color); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;">
                <i class="fa-solid fa-chevron-right" style="font-size: 1rem;"></i>
            </button>
            
            <!-- Indicators / Dots -->
            <div class="slider-dots" style="position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem; z-index: 10;">
                @foreach($banners as $index => $banner)
                    <span class="dot {{ $index === 0 ? 'active' : '' }}" onclick="setSlide({{ $index }})" style="width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.4); cursor: pointer; transition: all 0.3s;"></span>
                @endforeach
            </div>
        </div>
    @else
        <!-- Fallback Default Hero Section if no slides are added by Admin yet -->
        <section class="hero" style="background: linear-gradient(135deg, rgba(226,135,67,0.05), rgba(139,92,246,0.05)); border: 1px solid var(--border-color); border-radius: 20px; padding: 3.5rem 2rem; margin-top: 2rem;">
            <h1>Topup Game & Pulsa Otomatis</h1>
            <p>Proses instan 24 jam nonstop, pembayaran lengkap dengan QRIS, E-Wallet, dan Transfer VA dengan biaya admin paling murah!</p>
        </section>
    @endif

    <!-- Section: Kategori Populer -->
    @if(count($popularCategories) > 0)
        <section style="margin-top: 4rem; margin-bottom: 2rem;">
            <h2 class="section-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-solid fa-fire" style="color: #ef4444; animation: pulse 2s infinite;"></i> Game Populer
            </h2>
            
            <div class="categories-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 0;">
                @foreach($popularCategories as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="category-card" style="border-color: rgba(226, 135, 67, 0.15); box-shadow: 0 8px 24px rgba(226, 135, 67, 0.03); position: relative; overflow: hidden;">
                        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: linear-gradient(135deg, #ef4444, #e28743); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.25rem; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25); z-index: 2;">
                            <i class="fa-solid fa-star" style="font-size: 0.6rem;"></i> POPULER
                        </span>
                        
                        <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}" class="category-thumbnail" style="border: 2px solid rgba(226, 135, 67, 0.2);">
                        <div class="category-info">
                            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 0.35rem;">{{ $category->name }}</h3>
                            <span style="background: rgba(226, 135, 67, 0.1); color: #e28743; border: 1px solid rgba(226, 135, 67, 0.2);">{{ match($category->type) {
                                'game' => 'Game',
                                'pulsa' => 'Pulsa & Data',
                                'emoney' => 'E-Money',
                                'pln' => 'PLN Listrik',
                                'tagihan' => 'Tagihan',
                                'voucher' => 'Voucher',
                                default => ucfirst($category->type),
                            } }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Section: Semua Game & Layanan -->
    <section style="margin-top: 4rem; margin-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <h2 class="section-title" style="margin-bottom: 0;">Semua Game & Layanan</h2>
            
            @php
                $activeCategoryTypes = $categories->pluck('type')->unique()->toArray();
                // Tipe utama yang selalu dipertahankan
                $primaryTypes = ['game', 'pulsa', 'streaming'];
                
                $allTypeDefinitions = [
                    'game' => ['label' => 'Game', 'icon' => 'fa-solid fa-gamepad'],
                    'pulsa' => ['label' => 'Pulsa & Data', 'icon' => 'fa-solid fa-mobile-screen-button'],
                    'emoney' => ['label' => 'E-Money', 'icon' => 'fa-solid fa-wallet'],
                    'streaming' => ['label' => 'Streaming', 'icon' => 'fa-solid fa-film'],
                    'pln' => ['label' => 'PLN', 'icon' => 'fa-solid fa-bolt'],
                    'tagihan' => ['label' => 'Tagihan', 'icon' => 'fa-solid fa-file-invoice-dollar'],
                    'voucher' => ['label' => 'Voucher', 'icon' => 'fa-solid fa-ticket'],
                ];
            @endphp

            <!-- Filters Tabs -->
            <div class="filter-tabs" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button onclick="filterCategory('all')" class="filter-btn active" data-type="all">Semua</button>
                @foreach($allTypeDefinitions as $typeKey => $tabDef)
                    @if(in_array($typeKey, $primaryTypes) || in_array($typeKey, $activeCategoryTypes))
                        <button onclick="filterCategory('{{ $typeKey }}')" class="filter-btn" data-type="{{ $typeKey }}">
                            <i class="{{ $tabDef['icon'] }}"></i> {{ $tabDef['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
        
        <div class="categories-grid" id="allCategoriesGrid">
            @forelse($categories as $category)
                <a href="{{ url('/category/' . $category->slug) }}" class="category-card" data-category-type="{{ $category->type }}">
                    <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}" class="category-thumbnail">
                    <div class="category-info">
                        <h3>{{ $category->name }}</h3>
                        <span>{{ match($category->type) {
                            'game' => 'Game',
                            'pulsa' => 'Pulsa & Data',
                            'emoney' => 'E-Money',
                            'streaming' => 'Streaming',
                            'pln' => 'PLN Listrik',
                            'tagihan' => 'Tagihan',
                            'voucher' => 'Voucher',
                            default => ucfirst($category->type),
                        } }}</span>
                    </div>
                </a>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: var(--bg-card); border-radius: 16px; border: 1px dashed var(--border-color);">
                    <i class="fa-solid fa-gamepad" style="font-size: 2.5rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary);">Belum ada produk/kategori tersedia. Silakan jalankan seeder atau lakukan sinkronisasi.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Section: Keunggulan Wistek Topup -->
    <section style="margin-top: 5rem; margin-bottom: 4rem;">
        <h2 class="section-title" style="margin-bottom: 2rem;">Mengapa Harus Wistek Topup?</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <!-- Keunggulan 1: Proses Instan -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2rem; transition: all 0.3s ease; text-align: left;" onmouseover="this.style.borderColor='rgba(226, 135, 67, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="background: rgba(226, 135, 67, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; border: 1px solid rgba(226, 135, 67, 0.2);">
                    <i class="fa-solid fa-bolt" style="font-size: 1.5rem; color: #e28743;"></i>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem;">Proses Instan & Otomatis</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">Layanan kami aktif 24 jam nonstop setiap hari. Transaksi diproses otomatis oleh sistem hanya dalam hitungan detik setelah pembayaran diterima.</p>
            </div>

            <!-- Keunggulan 2: Metode Pembayaran Lengkap -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2rem; transition: all 0.3s ease; text-align: left;" onmouseover="this.style.borderColor='rgba(59, 130, 246, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="background: rgba(59, 130, 246, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; border: 1px solid rgba(59, 130, 246, 0.2);">
                    <i class="fa-solid fa-credit-card" style="font-size: 1.5rem; color: #3b82f6;"></i>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem;">Metode Bayar Terlengkap</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">Tersedia berbagai pilihan metode pembayaran mulai dari QRIS (dana, gopay, ovo, shopeepay), Virtual Account Bank (BCA, Mandiri, BNI, BRI), hingga Retail Alfamart.</p>
            </div>

            <!-- Keunggulan 3: Harga Terjangkau -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2rem; transition: all 0.3s ease; text-align: left;" onmouseover="this.style.borderColor='rgba(16, 185, 129, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="background: rgba(16, 185, 129, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fa-solid fa-tags" style="font-size: 1.5rem; color: #10b981;"></i>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem;">Harga Termurah & Hemat</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">Kami menawarkan harga top-up yang sangat bersaing dan murah bagi gamers. Nikmati potongan ekstra menggunakan berbagai kode voucher promo aktif.</p>
            </div>

            <!-- Keunggulan 4: Layanan Responsif -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 2rem; transition: all 0.3s ease; text-align: left;" onmouseover="this.style.borderColor='rgba(139, 92, 246, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.transform='none';">
                <div style="background: rgba(139, 92, 246, 0.1); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; border: 1px solid rgba(139, 92, 246, 0.2);">
                    <i class="fa-solid fa-headset" style="font-size: 1.5rem; color: #8b5cf6;"></i>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem;">Layanan CS Responsif</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">Kepuasan pelanggan adalah prioritas utama. Tim customer service kami siap merespon pertanyaan dan kendala transaksi Anda via WhatsApp dengan cepat.</p>
            </div>
        </div>
    </section>

    <!-- Section: Ulasan Pelanggan -->
    @php
        $reviewEnabled = \App\Models\Setting::get('review_section_enabled', '1') === '1';
        $reviewLimit = (int) \App\Models\Setting::get('review_display_limit', 3);
        $publicReviews = $reviewEnabled 
            ? \App\Models\Review::where('is_visible', true)->orderBy('sort_order', 'asc')->latest()->take($reviewLimit)->get()
            : collect();
        $avatarGradients = [
            'linear-gradient(135deg, #e28743, #ef4444)',
            'linear-gradient(135deg, #3b82f6, #8b5cf6)',
            'linear-gradient(135deg, #10b981, #3b82f6)',
            'linear-gradient(135deg, #ec4899, #f43f5e)',
            'linear-gradient(135deg, #8b5cf6, #d946ef)',
            'linear-gradient(135deg, #f59e0b, #e28743)',
        ];
    @endphp

    @if($reviewEnabled && $publicReviews->isNotEmpty())
    <section style="margin-top: 4rem; margin-bottom: 2rem;">
        <h2 class="section-title" style="margin-bottom: 2rem;">Ulasan Pelanggan Setia</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach($publicReviews as $index => $rev)
                @php
                    $initials = collect(explode(' ', trim($rev->name)))
                        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                        ->take(2)
                        ->implode('');
                    $gradient = $avatarGradients[$index % count($avatarGradients)];
                @endphp
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 1.75rem; display: flex; flex-direction: column; gap: 1rem; text-align: left; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="background: {{ $gradient }}; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-family: 'Outfit', sans-serif;">
                                {{ $initials ?: 'WP' }}
                            </div>
                            <div>
                                <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 700; color: #fff; margin: 0;">{{ $rev->name }}</h4>
                                <span style="font-size: 0.75rem; color: var(--text-secondary);">{{ $rev->role_or_title ?: 'Pelanggan Setia' }}</span>
                            </div>
                        </div>
                        <div style="color: #f59e0b; display: flex; gap: 0.15rem;">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $rev->rating)
                                    <i class="fa-solid fa-star" style="font-size: 0.75rem;"></i>
                                @else
                                    <i class="fa-regular fa-star" style="font-size: 0.75rem; color: #4b5563;"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; font-style: italic; margin-bottom: 0;">"{{ $rev->comment }}"</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

@section('scripts')
<script>
    // ----------------------------------------------------
    // Slider Carousel Functionality
    // ----------------------------------------------------
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dots .dot');
    const totalSlides = slides.length;
    let autoplayInterval;

    function showSlide(index) {
        if (totalSlides === 0) return;
        
        if (index >= totalSlides) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = totalSlides - 1;
        } else {
            currentSlide = index;
        }

        const wrapper = document.querySelector('.slider-wrapper');
        if (wrapper) {
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        // Update active dots
        dots.forEach((dot, idx) => {
            if (idx === currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    function moveSlide(step) {
        resetAutoplay();
        showSlide(currentSlide + step);
    }

    function setSlide(index) {
        resetAutoplay();
        showSlide(index);
    }

    function startAutoplay() {
        if (totalSlides > 1) {
            autoplayInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000);
        }
    }

    function resetAutoplay() {
        clearInterval(autoplayInterval);
        startAutoplay();
    }

    // ----------------------------------------------------
    // Category Filtering Functionality
    // ----------------------------------------------------
    function filterCategory(type) {
        // Toggle active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (btn.getAttribute('data-type') === type) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Filter cards in grid
        const cards = document.querySelectorAll('#allCategoriesGrid .category-card');
        cards.forEach(card => {
            const cardType = card.getAttribute('data-category-type');
            
            if (type === 'all' || cardType === type) {
                card.style.display = 'flex';
                // Trigger simple animation
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transition = 'opacity 0.2s ease-in-out';
                }, 50);
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Initialize Slider Autoplay on Load
    document.addEventListener('DOMContentLoaded', () => {
        startAutoplay();
    });
</script>
@endsection
