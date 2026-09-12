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
            <div class="slider-wrapper" style="display: flex; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1); width: 100%;">
                @foreach($banners as $index => $banner)
                    <div class="slide" style="min-width: 100%; box-sizing: border-box; position: relative;">
                        @if($banner->link_url)
                            <a href="{{ $banner->link_url }}" style="display: block;">
                        @endif
                        
                        <img src="{{ asset('storage/' . $banner->image_path) }}" alt="{{ $banner->title ?? 'Promo Banner' }}" style="width: 100%; height: auto; aspect-ratio: 1200/500; object-fit: cover; display: block;">
                        
                        @if($banner->link_url)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
            
            @if(count($banners) > 1)
                <!-- Navigation Arrows (Clean circular floating buttons) -->
                <button onclick="moveSlide(-1)" aria-label="Previous Slide" style="position: absolute; top: 50%; left: 1rem; transform: translateY(-50%); background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(4px); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; transition: background 0.2s, transform 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.7)'; this.style.transform='translateY(-50%) scale(1.08)';" onmouseout="this.style.background='rgba(0,0,0,0.45)'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fa-solid fa-chevron-left" style="font-size: 1.1rem;"></i>
                </button>
                <button onclick="moveSlide(1)" aria-label="Next Slide" style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(4px); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; transition: background 0.2s, transform 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.7)'; this.style.transform='translateY(-50%) scale(1.08)';" onmouseout="this.style.background='rgba(0,0,0,0.45)'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fa-solid fa-chevron-right" style="font-size: 1.1rem;"></i>
                </button>
            @endif
        </div>
    @else
        <!-- Fallback Default Hero Section if no slides are added by Admin yet -->
        <section class="hero" style="background: linear-gradient(135deg, rgba(226,135,67,0.05), rgba(139,92,246,0.05)); border: 1px solid var(--border-color); border-radius: 20px; padding: 3.5rem 2rem; margin-top: 2rem;">
            <h1>Topup Game & Pulsa Otomatis</h1>
            <p>Proses instan 24 jam nonstop, pembayaran lengkap dengan QRIS, E-Wallet, dan Transfer VA dengan biaya admin paling murah!</p>
        </section>
    @endif



    <!-- Section: Flash Sale & Countdown Timer -->
    @if(isset($flashSales) && count($flashSales) > 0)
        @php
            $firstEndTime = $flashSales->min('end_at');
            $endTimeIso = $firstEndTime ? $firstEndTime->format('Y-m-d\TH:i:s') : '';
        @endphp
        <section id="flashSaleSection" style="margin-top: 2rem; margin-bottom: 3rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(226, 135, 67, 0.1)); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; padding: 1.75rem; box-shadow: 0 12px 35px rgba(239, 68, 68, 0.15); position: relative; overflow: hidden;">
            
            <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: rgba(239, 68, 68, 0.2); filter: blur(60px); border-radius: 50%; pointer-events: none;"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px dashed rgba(255, 255, 255, 0.1); padding-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="background: linear-gradient(135deg, #ef4444, #e28743); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);">
                        <i class="fa-solid fa-bolt" style="font-size: 1.35rem; color: #fff; animation: pulse 1.5s infinite;"></i>
                    </div>
                    <div>
                        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                            FLASH SALE <span style="color: #ef4444;">TERBATAS</span> ⚡
                        </h2>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">Penawaran diskon spesial dengan kuota terbatas!</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(239, 68, 68, 0.4); padding: 0.5rem 1rem; border-radius: 12px;">
                    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">BERAKHIR DALAM:</span>
                    <div id="flashSaleTimer" data-end="{{ $endTimeIso }}" style="display: flex; gap: 0.35rem; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1rem; color: #fff;">
                        <span id="timerHours" style="background: #ef4444; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);">00</span> :
                        <span id="timerMinutes" style="background: #e28743; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 6px rgba(226, 135, 67, 0.4);">00</span> :
                        <span id="timerSeconds" style="background: #10b981; padding: 2px 7px; border-radius: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);">00</span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.25rem;">
                @foreach($flashSales as $sale)
                    @php
                        $product = $sale->product;
                        $category = $product ? $product->category : null;
                        $percentage = $sale->discount_percentage;
                        $remaining = $sale->stock_remaining;
                        $soldPercent = $sale->stock_total > 0 ? min(100, round(($sale->stock_sold / $sale->stock_total) * 100)) : 0;
                    @endphp
                    @if($product && $category)
                        <div style="background: rgba(18, 18, 22, 0.75); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; transition: all 0.25s ease;" onmouseover="this.style.borderColor='#ef4444'; this.style.transform='translateY(-4px)';" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'; this.style.transform='none';">
                            
                            <span style="position: absolute; top: 0.6rem; right: 0.6rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; font-weight: 800; font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4); z-index: 2;">
                                DISKON {{ $percentage }}%
                            </span>

                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.85rem;">
                                <img src="{{ $category->thumbnail ?? 'https://placehold.co/100x100' }}" alt="{{ $category->name }}" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 1.5px solid rgba(239, 68, 68, 0.3);">
                                <div>
                                    <span style="font-size: 0.72rem; color: #ef4444; font-weight: 700; text-transform: uppercase; display: block;">{{ $category->name }}</span>
                                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.92rem; font-weight: 700; color: #fff; margin: 2px 0 0 0; line-height: 1.25;">{{ $product->name }}</h4>
                                </div>
                            </div>

                            <div style="margin-bottom: 0.85rem; background: rgba(0,0,0,0.25); padding: 0.6rem 0.75rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04);">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-size: 0.78rem; color: var(--text-secondary); text-decoration: line-through;">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</span>
                                </div>
                                <span style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #10b981;">Rp {{ number_format($sale->discount_price, 0, ',', '.') }}</span>
                            </div>

                            <div style="margin-bottom: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-secondary); margin-bottom: 0.25rem; font-weight: 600;">
                                    <span>Tersisa: <strong style="color: #ef4444;">{{ $remaining }} item</strong></span>
                                    <span>{{ $soldPercent }}% Terjual</span>
                                </div>
                                <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden;">
                                    <div style="width: {{ $soldPercent }}%; height: 100%; background: linear-gradient(90deg, #ef4444, #f59e0b); border-radius: 10px; transition: width 0.4s ease;"></div>
                                </div>
                            </div>

                            <a href="{{ url('/category/' . $category->slug) }}" style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; background: linear-gradient(135deg, #ef4444, #e28743); color: #fff; font-family: 'Outfit', sans-serif; font-size: 0.85rem; font-weight: 700; padding: 0.6rem 1rem; border-radius: 8px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);" onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='none';">
                                <span>Beli Sekarang</span> <i class="fa-solid fa-bolt" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <!-- Section: Kategori Populer -->
    @if(count($popularCategories) > 0)
        <section style="margin-top: 2.5rem; margin-bottom: 2rem;">
            <h2 class="section-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fa-solid fa-fire" style="color: #ef4444; animation: pulse 2s infinite;"></i> Game Populer
            </h2>
            
            <div class="categories-grid" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 0;">
                @foreach($popularCategories as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="category-card" style="border-color: rgba(226, 135, 67, 0.15); box-shadow: 0 8px 24px rgba(226, 135, 67, 0.03); position: relative; overflow: hidden;">
                        @if($category->is_maintenance)
                            <span style="position: absolute; top: 0.75rem; left: 0.75rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.25rem; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); z-index: 2;">
                                <i class="fa-solid fa-wrench" style="font-size: 0.6rem;"></i> MAINTENANCE
                            </span>
                        @endif
                        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: linear-gradient(135deg, #ef4444, #e28743); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.25rem; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25); z-index: 2;">
                            <i class="fa-solid fa-star" style="font-size: 0.6rem;"></i> POPULER
                        </span>
                        
                        <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}" class="category-thumbnail" style="border: 2px solid rgba(226, 135, 67, 0.2);" onerror="this.onerror=null; this.src='https://placehold.co/150x150/1e293b/ffffff?text={{ urlencode($category->name) }}';">
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
                <a href="{{ url('/category/' . $category->slug) }}" class="category-card" data-category-type="{{ $category->type }}" style="position: relative; overflow: hidden;">
                    @if($category->is_maintenance)
                        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.25rem; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); z-index: 2;">
                            <i class="fa-solid fa-wrench" style="font-size: 0.6rem;"></i> MAINTENANCE
                        </span>
                    @endif
                    <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}" class="category-thumbnail" onerror="this.onerror=null; this.src='https://placehold.co/150x150/1e293b/ffffff?text={{ urlencode($category->name) }}';">
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

            <!-- Empty state notice when live search yields 0 results -->
            <div id="noSearchMatchNotice" style="display: none; grid-column: 1/-1; text-align: center; padding: 4rem; background: var(--bg-card); border-radius: 16px; border: 1px dashed var(--border-color);">
                <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 2.5rem; color: #ef4444; margin-bottom: 1rem;"></i>
                <h4 style="color: #fff; font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">Pencarian Tidak Ditemukan</h4>
                <p style="color: var(--text-secondary); font-size: 0.88rem;">Tidak ada game atau layanan yang cocok dengan "<strong id="searchQueryKeyword" style="color: #e28743;"></strong>". Silakan coba kata kunci lain.</p>
            </div>
        </div>

        <!-- Tombol Tampilkan Lainnya (Limit 12) -->
        <div id="showMoreContainer" style="display: none; justify-content: center; margin-top: 2rem;">
            <button type="button" id="btnToggleCategories" onclick="toggleShowMoreCategories()" style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border-color); color: var(--text-primary); font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 600; padding: 0.75rem 2.25rem; border-radius: 30px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.6rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.15);" onmouseover="this.style.background='#e28743'; this.style.borderColor='#e28743'; this.style.color='#fff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.04)'; this.style.borderColor='var(--border-color)'; this.style.color='var(--text-primary)'; this.style.transform='none';">
                <span id="showMoreText">Tampilkan Lainnya...</span>
                <i class="fa-solid fa-chevron-down" id="showMoreIcon" style="font-size: 0.8rem; transition: transform 0.3s ease;"></i>
            </button>
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
        $reviewLimitSetting = (int) \App\Models\Setting::get('review_display_limit', 3);
        $reviewLimit = in_array($reviewLimitSetting, [3, 6]) ? $reviewLimitSetting : 3;
        $reviewSpeedSetting = (int) \App\Models\Setting::get('review_autoplay_speed', 5);
        $publicReviews = $reviewEnabled 
            ? \App\Models\Review::where('is_visible', true)->orderBy('sort_order', 'asc')->latest()->get()
            : collect();
        $reviewChunks = $publicReviews->chunk($reviewLimit);
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="section-title" style="margin-bottom: 0;">Ulasan Pelanggan Setia</h2>
            
            @if($reviewChunks->count() > 1)
            <div style="display: flex; gap: 0.6rem; align-items: center;">
                <button type="button" onclick="moveReviewSlide(-1)" class="review-nav-arrow" aria-label="Sebelumnya" style="background: var(--bg-card); border: 1px solid var(--border-color); color: #fff; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#e28743';this.style.color='#e28743';" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='#fff';">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.9rem;"></i>
                </button>
                <button type="button" onclick="moveReviewSlide(1)" class="review-nav-arrow" aria-label="Berikutnya" style="background: var(--bg-card); border: 1px solid var(--border-color); color: #fff; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#e28743';this.style.color='#e28743';" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='#fff';">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.9rem;"></i>
                </button>
            </div>
            @endif
        </div>
        
        <div class="review-slider-viewport" style="overflow: hidden; width: 100%; position: relative;">
            <div class="review-slider-track" style="display: flex; transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1); width: 100%;">
                @foreach($reviewChunks as $chunkIndex => $chunk)
                    <div class="review-slide-page" style="min-width: 100%; flex-shrink: 0; box-sizing: border-box;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            @foreach($chunk as $index => $rev)
                                @php
                                    $initials = collect(explode(' ', trim($rev->name)))
                                        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                    $gradient = $avatarGradients[($chunkIndex * $reviewLimit + $index) % count($avatarGradients)];
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
                    </div>
                @endforeach
            </div>
        </div>

        @if($reviewChunks->count() > 1)
        <div class="review-dots" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
            @for($d = 0; $d < $reviewChunks->count(); $d++)
                <span class="review-dot-item {{ $d === 0 ? 'active' : '' }}" onclick="setReviewSlide({{ $d }})" style="width: 10px; height: 10px; border-radius: 50%; background: {{ $d === 0 ? '#e28743' : 'rgba(255,255,255,0.2)' }}; cursor: pointer; transition: all 0.2s; display: inline-block;"></span>
            @endfor
        </div>
        @endif
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
            if (autoplayInterval) clearInterval(autoplayInterval);
            autoplayInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 4500);
        }
    }

    function resetAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
        startAutoplay();
    }

    // ----------------------------------------------------
    // Category Filtering & "Tampilkan Lainnya" Functionality
    // ----------------------------------------------------
    let currentCategoryType = 'all';
    let isCategoriesExpanded = false;
    const CATEGORY_LIMIT = 12;

    function updateCategoryVisibility() {
        const cards = document.querySelectorAll('#allCategoriesGrid .category-card');
        let matchingIndex = 0;

        cards.forEach(card => {
            const cardType = card.getAttribute('data-category-type');
            const isMatch = (currentCategoryType === 'all' || cardType === currentCategoryType);

            if (isMatch) {
                matchingIndex++;
                if (isCategoriesExpanded || matchingIndex <= CATEGORY_LIMIT) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                } else {
                    card.style.display = 'none';
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle "Tampilkan Lainnya..." button visibility
        const container = document.getElementById('showMoreContainer');
        const textEl = document.getElementById('showMoreText');
        const iconEl = document.getElementById('showMoreIcon');

        if (container && textEl && iconEl) {
            if (matchingIndex > CATEGORY_LIMIT) {
                container.style.display = 'flex';
                if (isCategoriesExpanded) {
                    textEl.innerText = 'Sembunyikan';
                    iconEl.style.transform = 'rotate(180deg)';
                } else {
                    textEl.innerText = 'Tampilkan Lainnya...';
                    iconEl.style.transform = 'rotate(0deg)';
                }
            } else {
                container.style.display = 'none';
            }
        }
    }

    function filterCategory(type) {
        currentCategoryType = type;
        isCategoriesExpanded = false; // Reset expand state when tab changes

        // Toggle active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (btn.getAttribute('data-type') === type) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        updateCategoryVisibility();
    }

    function toggleShowMoreCategories() {
        isCategoriesExpanded = !isCategoriesExpanded;
        updateCategoryVisibility();
    }

    // ----------------------------------------------------
    // Live Search Bar Functionality (Instant 0ms Filtering)
    // ----------------------------------------------------
    function handleLiveSearch(queryOverride) {
        let rawQuery = '';
        if (typeof queryOverride === 'string') {
            rawQuery = queryOverride;
        } else {
            const navInput = document.getElementById('navSearchInput');
            rawQuery = navInput ? navInput.value : '';
        }

        const navInput = document.getElementById('navSearchInput');
        if (navInput && navInput.value !== rawQuery) {
            navInput.value = rawQuery;
        }

        const clearBtnNav = document.getElementById('navClearSearch');
        if (clearBtnNav) {
            clearBtnNav.style.display = rawQuery.trim() ? 'block' : 'none';
        }

        const query = rawQuery.trim().toLowerCase();
        const cards = document.querySelectorAll('#allCategoriesGrid .category-card');
        let matchCount = 0;

        cards.forEach(card => {
            const titleEl = card.querySelector('h3');
            const typeEl = card.querySelector('span');

            const name = titleEl ? titleEl.innerText.toLowerCase() : '';
            const typeAttr = card.getAttribute('data-category-type') || '';
            const typeTxt = typeEl ? typeEl.innerText.toLowerCase() : '';

            const isMatch = !query || name.includes(query) || typeAttr.includes(query) || typeTxt.includes(query);

            if (isMatch) {
                matchCount++;
                card.style.display = 'flex';
                card.style.opacity = '1';
            } else {
                card.style.display = 'none';
            }
        });

        // Hide show more container when searching
        const showMoreContainer = document.getElementById('showMoreContainer');
        if (showMoreContainer) {
            showMoreContainer.style.display = query ? 'none' : (matchCount > CATEGORY_LIMIT ? 'flex' : 'none');
        }

        // Toggle No Match Notice
        const noNotice = document.getElementById('noSearchMatchNotice');
        const kw = document.getElementById('searchQueryKeyword');
        if (noNotice) {
            if (query && matchCount === 0) {
                noNotice.style.display = 'block';
                if (kw) kw.innerText = rawQuery.trim();
            } else {
                noNotice.style.display = 'none';
            }
        }

        if (!query) {
            updateCategoryVisibility();
        }
    }

    function clearLiveSearch() {
        if (typeof clearNavSearch === 'function') {
            clearNavSearch();
        } else {
            handleLiveSearch('');
        }
    }

    // ----------------------------------------------------
    // Flash Sale Real-Time Digital Countdown Timer
    // ----------------------------------------------------
    function startFlashSaleTimer() {
        const timerEl = document.getElementById('flashSaleTimer');
        if (!timerEl) return;

        const endDateStr = timerEl.getAttribute('data-end');
        if (!endDateStr) return;

        const targetTime = new Date(endDateStr).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const diff = targetTime - now;

            if (diff <= 0) {
                const section = document.getElementById('flashSaleSection');
                if (section) section.style.display = 'none';
                return;
            }

            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const hEl = document.getElementById('timerHours');
            const mEl = document.getElementById('timerMinutes');
            const sEl = document.getElementById('timerSeconds');

            if (hEl) hEl.innerText = String(hours).padStart(2, '0');
            if (mEl) mEl.innerText = String(minutes).padStart(2, '0');
            if (sEl) sEl.innerText = String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // Initialize Slider Autoplay, Category Limit, Flash Sale Timer & URL Search on Load
    document.addEventListener('DOMContentLoaded', () => {
        startAutoplay();
        updateCategoryVisibility();
        startFlashSaleTimer();

        // Check URL search param and trigger search if present
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
            handleLiveSearch(searchParam);
        }

        const slider = document.querySelector('.slider-container');
        if (slider) {
            slider.addEventListener('mouseenter', () => {
                if (autoplayInterval) clearInterval(autoplayInterval);
            });
            slider.addEventListener('mouseleave', () => {
                startAutoplay();
            });
        }
    });

    // ----------------------------------------------------
    // Review Carousel Functionality with Autoplay
    // ----------------------------------------------------
    let currentReviewSlide = 0;
    const totalReviewSlides = {{ isset($reviewChunks) ? $reviewChunks->count() : 0 }};
    const reviewAutoplaySpeed = {{ isset($reviewSpeedSetting) ? $reviewSpeedSetting : 5 }};
    let reviewAutoplayInterval = null;

    function showReviewSlide(idx) {
        if (totalReviewSlides <= 1) return;
        if (idx >= totalReviewSlides) {
            currentReviewSlide = 0;
        } else if (idx < 0) {
            currentReviewSlide = totalReviewSlides - 1;
        } else {
            currentReviewSlide = idx;
        }

        const track = document.querySelector('.review-slider-track');
        if (track) {
            track.style.transform = `translateX(-${currentReviewSlide * 100}%)`;
        }

        const dots = document.querySelectorAll('.review-dot-item');
        dots.forEach((dot, i) => {
            if (i === currentReviewSlide) {
                dot.style.background = '#e28743';
                dot.style.transform = 'scale(1.3)';
            } else {
                dot.style.background = 'rgba(255,255,255,0.2)';
                dot.style.transform = 'scale(1)';
            }
        });
    }

    function moveReviewSlide(step) {
        resetReviewAutoplay();
        showReviewSlide(currentReviewSlide + step);
    }

    function setReviewSlide(idx) {
        resetReviewAutoplay();
        showReviewSlide(idx);
    }

    function startReviewAutoplay() {
        if (totalReviewSlides > 1 && reviewAutoplaySpeed > 0) {
            reviewAutoplayInterval = setInterval(() => {
                showReviewSlide(currentReviewSlide + 1);
            }, reviewAutoplaySpeed * 1000);
        }
    }

    function resetReviewAutoplay() {
        if (reviewAutoplayInterval) {
            clearInterval(reviewAutoplayInterval);
            startReviewAutoplay();
        }
    }

    // Initialize Review Autoplay on Load
    document.addEventListener('DOMContentLoaded', () => {
        startReviewAutoplay();

        const viewport = document.querySelector('.review-slider-viewport');
        if (viewport) {
            viewport.addEventListener('mouseenter', () => {
                if (reviewAutoplayInterval) clearInterval(reviewAutoplayInterval);
            });
            viewport.addEventListener('mouseleave', () => {
                startReviewAutoplay();
            });
        }
    });
</script>
@endsection
