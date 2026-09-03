@extends('layouts.app')

@section('title', $category->name . ' - Wistek Topup')

@section('styles')
<style>
    /* Accordion Container */
    .payment-accordion-container {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* Accordion Panel */
    .accordion-panel {
        background: rgba(25, 25, 25, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 6px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .accordion-panel.active {
        border-color: rgba(255, 255, 255, 0.1);
        background: rgba(30, 30, 30, 0.8);
    }

    /* Accordion Header */
    .accordion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        cursor: pointer;
        user-select: none;
        background: rgba(0, 0, 0, 0.2);
        transition: background 0.2s;
    }
    
    .accordion-header:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .accordion-title-area {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-grow: 1;
    }

    .accordion-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .accordion-arrow {
        color: var(--text-secondary);
        font-size: 0.85rem;
        transition: transform 0.3s ease;
    }

    .accordion-panel.active .accordion-arrow {
        color: #e28743;
    }

    /* Logo Badges in Header */
    .accordion-header-logos {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
        margin-left: auto;
        margin-right: 1.5rem;
    }

    .header-logo-badge {
        height: 14px;
        background: #fff;
        padding: 2px 4px;
        border-radius: 2px;
        object-fit: contain;
    }

    .header-logo-plus {
        font-size: 0.7rem;
        color: var(--text-secondary);
        background: rgba(255, 255, 255, 0.05);
        padding: 2px 4px;
        border-radius: 2px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Accordion Content Panel */
    .accordion-content-panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease;
        padding: 0 1.25rem;
    }

    .accordion-panel.active .accordion-content-panel {
        max-height: 1000px; /* high value to allow expansion */
        padding: 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Grid Layout inside Accordion */
    .payment-grid-layout {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 0.75rem;
    }

    /* Row Item in Grid */
    .payment-row-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: rgba(15, 15, 15, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .payment-row-item:hover {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .payment-row-item.active {
        background: rgba(226, 135, 67, 0.08);
        border-color: #e28743;
        box-shadow: 0 0 6px rgba(226, 135, 67, 0.15);
    }

    /* Left side: Icon + Title */
    .payment-left-side {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .payment-icon-img {
        height: 18px;
        width: auto;
        max-width: 55px;
        background: #fff;
        padding: 2px 4px;
        border-radius: 2px;
        object-fit: contain;
    }

    .payment-name-txt {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    /* Right side: Price */
    .payment-row-price {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        transition: color 0.2s;
    }

    .payment-row-item.active .payment-row-price {
        color: #e28743;
    }

    /* Nominal Product Card Styles */
    .nominal-card {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .nominal-card:hover {
        border-color: rgba(226, 135, 67, 0.5) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    }

    .nominal-card.active {
        background: linear-gradient(135deg, rgba(226, 135, 67, 0.18), rgba(20, 20, 25, 0.95)) !important;
        border: 2px solid #e28743 !important;
        box-shadow: 0 0 22px rgba(226, 135, 67, 0.4), 0 6px 18px rgba(0, 0, 0, 0.5) !important;
        transform: translateY(-3px) scale(1.015);
    }

    .nominal-card .check-indicator {
        position: absolute;
        top: -18px;
        right: -18px;
        width: 38px;
        height: 38px;
        background: #e28743;
        transform: rotate(45deg);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s ease, transform 0.2s ease;
        box-shadow: 0 2px 8px rgba(226, 135, 67, 0.4);
        z-index: 5;
    }

    .nominal-card.active .check-indicator {
        opacity: 1;
    }

    .nominal-card .check-indicator i {
        transform: rotate(-45deg);
        font-size: 0.6rem;
        color: #fff;
        margin-bottom: 2px;
    }

    .nominal-card.active .nominal-name {
        color: #fff !important;
        text-shadow: 0 0 10px rgba(226, 135, 67, 0.3);
    }

    .nominal-card.active .nominal-price {
        color: #f59e0b !important;
        font-size: 1.1rem !important;
        font-weight: 800 !important;
    }
</style>
@endsection

@section('content')
<div class="container" style="padding-bottom: 5rem;">
    
    <form action="{{ url('/checkout') }}" method="POST" id="topupForm">
        @csrf
        <input type="hidden" name="category_id" value="{{ $category->id }}">
        <input type="hidden" name="product_id" id="selectedProductId" required>
        <input type="hidden" name="payment_method" id="selectedPaymentMethod" required>

        <div class="product-grid">
            
            <!-- Left Column: Category Description Info -->
            <div class="sidebar-info">
                <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}">
                <h2>{{ $category->name }}</h2>
                <p style="margin-bottom: 1.5rem;">Top Up {{ $category->name }} otomatis dan aman 24 jam nonstop.</p>
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; font-size: 0.85rem; color: var(--text-secondary);">
                    <h4 style="color: var(--text-primary); margin-bottom: 0.25rem;"><i class="fa-solid fa-circle-info"></i> Petunjuk</h4>
                    <p>Masukkan Data Akun Anda dengan benar. Pilih nominal topup yang Anda inginkan, selesaikan pembayaran, dan item akan langsung masuk ke akun Anda.</p>
                </div>
            </div>

            <!-- Right Column: Interactive Step Form -->
            <div>
                
                <!-- Step 1: Input Account ID / HP -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h3 class="step-title">Masukkan Data Akun</h3>
                    </div>
                    
                    @php
                        $slug = strtolower($category->slug);
                        $type = strtolower($category->type);
                    @endphp

                    @if(in_array($slug, ['mobile-legends', 'mobile-legends-bang-bang', 'mlbb', 'magic-chess', 'magic-chess-go-go', 'magic-chess-pass', 'mc']))
                        <div class="input-group-row">
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">User ID</label>
                                <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 12345678" inputmode="numeric" required>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Zone ID</label>
                                <input type="text" name="zone_id" id="account_zone_id" class="form-control" placeholder="Contoh: 1234" inputmode="numeric" required>
                            </div>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">User ID & Zone ID tertera di profil game Mobile Legends / Magic Chess Anda.</p>

                    @elseif(in_array($slug, ['genshin-impact', 'honkai-star-rail']))
                        <div class="input-group-row">
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">User ID (UID)</label>
                                <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 800000000" inputmode="numeric" required>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Server</label>
                                <select name="zone_id" id="account_zone_id" class="form-control" style="background: rgba(15, 15, 15, 0.8); color: #fff; border: 1px solid var(--border-color);" required>
                                    <option value="os_asia">Asia</option>
                                    <option value="os_usa">America</option>
                                    <option value="os_euro">Europe</option>
                                    <option value="os_cht">TW / HK / MO</option>
                                </select>
                            </div>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">UID tertera pada sudut kanan bawah layar game.</p>

                    @elseif($slug === 'valorant')
                        <div class="input-group-row">
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Riot ID</label>
                                <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: PlayerOne" required>
                            </div>
                            <div>
                                <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Tagline (#)</label>
                                <input type="text" name="zone_id" id="account_zone_id" class="form-control" placeholder="Contoh: IDN atau 1234" required>
                            </div>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan Riot ID dan Tagline tanpa tanda hashtag (#).</p>

                    @elseif(in_array($slug, ['free-fire', 'free-fire-max', 'ff']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Player ID (User ID)</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 123456789" inputmode="numeric" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Player ID tertera di menu profil game Free Fire Anda.</p>

                    @elseif(in_array($slug, ['pubg-mobile', 'pubg']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Character ID</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 5123456789" inputmode="numeric" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Character ID berupa angka di sebelah avatar profil game.</p>

                    @elseif(in_array($slug, ['call-of-duty-mobile', 'codm']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">OpenID</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 1234567890123456789" inputmode="numeric" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">OpenID tertera pada menu Pengaturan (Settings) > Legal & Privacy.</p>

                    @elseif(in_array($slug, ['honor-of-kings', 'hok']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Player ID</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 123456789" inputmode="numeric" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Player ID tertera pada profil game Honor of Kings Anda.</p>

                    @elseif(in_array($slug, ['point-blank', 'pb']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Username / ID Zepetto</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: player_pb" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan Username / ID Akun Zepetto Point Blank Anda.</p>

                    @elseif($type === 'pln')
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Nomor Meter / ID Pelanggan PLN</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 14123456789" inputmode="numeric" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan 11-12 digit Nomor Meter atau ID Pelanggan PLN Anda.</p>

                    @elseif(in_array($type, ['pulsa', 'paket-data']))
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Nomor Handphone</label>
                            <input type="tel" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 081234567890" inputmode="tel" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan nomor handphone aktif penerima pulsa/paket data.</p>

                    @elseif($type === 'emoney')
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Nomor HP Terdaftar</label>
                            <input type="tel" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 081234567890" inputmode="tel" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan nomor telepon yang terdaftar pada akun e-wallet penerima.</p>

                    @elseif($type === 'streaming' || $type === 'voucher')
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">ID Pelanggan / No. HP / Email Akun</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 081234567890 / user@email.com" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Masukkan No. HP atau Email terdaftar untuk pengiriman voucher/akses.</p>

                    @else
                        <div>
                            <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">ID Akun / Target</label>
                            <input type="text" name="target_id" id="account_user_id" class="form-control" placeholder="Contoh: 523087265" required>
                        </div>
                        <p class="form-help" style="margin-top: 0.5rem;">Kesalahan input data oleh pembeli bukan tanggung jawab kami.</p>
                    @endif

                    <div id="nickname_check_result" style="margin-top: 0.75rem; font-size: 0.88rem; display: none; align-items: center; gap: 0.5rem; font-family: 'Outfit', sans-serif; padding: 0.6rem 0.85rem; border-radius: 6px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.08); transition: all 0.2s ease;">
                    </div>
                </div>

                <!-- Step 2: Choose Nominal / Products -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h3 class="step-title">Pilih Nominal Top Up</h3>
                    </div>
                    
                    @php
                        // Smart Sub-Category Grouping
                        $groupedProducts = $products->groupBy(function($item) {
                            if ($item->subCategory) {
                                return $item->subCategory->name;
                            }

                            $name = strtolower($item->name);
                            if (str_contains($name, 'pass') || str_contains($name, 'weekly') || str_contains($name, 'twilight') || str_contains($name, 'starlight') || str_contains($name, 'membership')) {
                                return 'Special Items ✨';
                            } elseif (str_contains($name, 'first') || str_contains($name, 'double') || str_contains($name, 'perdana') || str_contains($name, 'first top up')) {
                                return 'First Top Up (Double Diamonds) ✨';
                            } elseif (str_contains($name, 'pack') || str_contains($name, 'bundle') || str_contains($name, 'box') || str_contains($name, 'epic') || str_contains($name, 'elite')) {
                                return 'Weekly / Monthly Pack ✨';
                            }

                            return 'Top Up ✨';
                        })->sortBy(function($group, $key) {
                            $order = match ($key) {
                                'Special Items ✨' => 1,
                                'First Top Up (Double Diamonds) ✨' => 2,
                                'Weekly / Monthly Pack ✨' => 3,
                                'Top Up ✨' => 4,
                                default => 5,
                            };
                            return $group->first()->subCategory ? $group->first()->subCategory->sort_order : $order;
                        });
                    @endphp

                    @forelse($groupedProducts as $subCategory => $groupItems)
                        <h4 style="color: #fff; font-size: 0.9rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.85rem; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.5rem; border-left: 3.5px solid #e28743; padding-left: 0.6rem; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $subCategory }}
                        </h4>
                        <div class="nominal-grid" style="margin-bottom: 2rem;">
                            @foreach($groupItems as $product)
                                @php
                                    // Robust Regex to strip brand name prefix + optional hyphen/colon/pipe/spaces
                                    $categorySlug = strtolower($category->slug);
                                    $categoryName = strtolower($category->name);
                                    $cleanCategoryName = preg_replace('/[^a-zA-Z0-9]/', '', $categoryName);
                                    $cleanCategorySlug = preg_replace('/[^a-zA-Z0-9]/', '', $categorySlug);

                                    $pattern = '/^('
                                        . preg_quote($categoryName, '/') . '|'
                                        . preg_quote(str_replace('-', ' ', $categorySlug), '/') . '|'
                                        . preg_quote($cleanCategoryName, '/') . '|'
                                        . preg_quote($cleanCategorySlug, '/') . '|'
                                        . 'MOBILE\s*LEGENDS?|MOBILELEGEND|MOBILE\s*LEGEND|MLBB|PUBG\s*MOBILE|PUBG|FREE\s*FIRE\s*MAX|FREE\s*FIRE|FF|GENSHIN\s*IMPACT|HONKAI\s*STAR\s*RAIL|CALL\s*OF\s*DUTY\s*MOBILE|CODM|POINT\s*BLANK|PB|HONOR\s*OF\s*KINGS|HOK|MAGIC\s*CHESS|TELKOMSEL|INDOSAT|AXIS|XL|SMARTFREN|TRI|THREE|BYU|GOPAY|DANA|OVO|SHOPEEPAY'
                                        . ')[\s\-\:\|\.\,]+/i';

                                    $displayName = preg_replace($pattern, '', $product->name);
                                    $displayName = trim(ltrim($displayName, '-:|= '));

                                    if (empty($displayName)) {
                                        $displayName = $product->name;
                                    }

                                    $nameLower = strtolower($displayName);

                                    // Determine Currency/Item Icon and Badge Text
                                    $iconClass = 'fa-solid fa-coins';
                                    $iconColor = '#f59e0b'; // Gold
                                    $iconBg = 'rgba(245, 158, 11, 0.12)';
                                    $badgeText = null;

                                    if (str_contains($nameLower, 'pass') || str_contains($nameLower, 'weekly') || str_contains($nameLower, 'twilight') || str_contains($nameLower, 'starlight') || str_contains($nameLower, 'membership')) {
                                        $iconClass = 'fa-solid fa-crown';
                                        $iconColor = '#f59e0b';
                                        $iconBg = 'rgba(245, 158, 11, 0.15)';
                                        $badgeText = 'Special Pass';
                                    } elseif (str_contains($nameLower, 'first') || str_contains($nameLower, 'double') || str_contains($nameLower, 'perdana') || str_contains($nameLower, 'bonus')) {
                                        $iconClass = 'fa-solid fa-fire';
                                        $iconColor = '#ef4444';
                                        $iconBg = 'rgba(239, 68, 68, 0.15)';
                                        $badgeText = 'First Top Up';
                                    } elseif (str_contains($nameLower, 'pack') || str_contains($nameLower, 'bundle') || str_contains($nameLower, 'box')) {
                                        $iconClass = 'fa-solid fa-box-open';
                                        $iconColor = '#a855f7';
                                        $iconBg = 'rgba(168, 85, 247, 0.15)';
                                        $badgeText = 'Special Bundle';
                                    } elseif (str_contains($categorySlug, 'mobile-legend') || str_contains($categorySlug, 'magic-chess') || str_contains($categorySlug, 'free-fire') || str_contains($nameLower, 'diamond')) {
                                        $iconClass = 'fa-solid fa-gem';
                                        $iconColor = '#3b82f6'; // Diamond Blue
                                        $iconBg = 'rgba(59, 130, 246, 0.12)';
                                    } elseif (str_contains($categorySlug, 'pubg') || str_contains($nameLower, 'uc')) {
                                        $iconClass = 'fa-solid fa-bolt';
                                        $iconColor = '#eab308'; // UC Gold/Yellow
                                        $iconBg = 'rgba(234, 179, 8, 0.12)';
                                    } elseif (str_contains($categorySlug, 'genshin') || str_contains($categorySlug, 'star-rail') || str_contains($nameLower, 'crystal')) {
                                        $iconClass = 'fa-solid fa-wand-magic-sparkles';
                                        $iconColor = '#a855f7'; // Crystal Purple
                                        $iconBg = 'rgba(168, 85, 247, 0.12)';
                                    } elseif (str_contains($categorySlug, 'valorant') || str_contains($nameLower, 'vp')) {
                                        $iconClass = 'fa-solid fa-shield-halved';
                                        $iconColor = '#ef4444'; // Valorant Red
                                        $iconBg = 'rgba(239, 68, 68, 0.12)';
                                    } elseif (in_array($category->type, ['pulsa', 'paket-data'])) {
                                        $iconClass = 'fa-solid fa-mobile-screen-button';
                                        $iconColor = '#10b981'; // Green
                                        $iconBg = 'rgba(16, 185, 129, 0.12)';
                                    } elseif ($category->type === 'emoney') {
                                        $iconClass = 'fa-solid fa-wallet';
                                        $iconColor = '#06b6d4'; // Cyan
                                        $iconBg = 'rgba(6, 182, 212, 0.12)';
                                    }
                                @endphp
                                <div class="nominal-card" data-product-id="{{ $product->id }}" onclick="selectProduct({{ $product->id }}, {{ $product->price_sell }})" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.1rem 1.15rem; min-height: 100px; border-radius: 12px; background: rgba(22, 22, 22, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); transition: all 0.22s ease; cursor: pointer; position: relative;">
                                    <div class="check-indicator"><i class="fa-solid fa-check"></i></div>
                                    
                                    <!-- Upper Row: Title & Styled Icon Box -->
                                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; width: 100%;">
                                        <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                                            <span class="nominal-name" style="font-weight: 700; color: #fff; font-size: 0.95rem; line-height: 1.35; font-family: 'Outfit', sans-serif;">{{ $displayName }}</span>
                                            @if($badgeText)
                                                <span style="font-size: 0.65rem; background: {{ $iconBg }}; color: {{ $iconColor }}; padding: 1px 6px; border-radius: 4px; font-weight: 600; width: fit-content; border: 1px solid {{ $iconColor }}33;">
                                                    {{ $badgeText }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div style="width: 34px; height: 34px; min-width: 34px; border-radius: 8px; background: {{ $iconBg }}; display: flex; align-items: center; justify-content: center; border: 1px solid {{ $iconColor }}33;">
                                            <i class="{{ $iconClass }}" style="color: {{ $iconColor }}; font-size: 1.1rem;"></i>
                                        </div>
                                    </div>

                                    <!-- Bottom Row: Price & Instant Delivery Badge -->
                                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-top: 1rem; padding-top: 0.5rem; border-top: 1px dashed rgba(255, 255, 255, 0.06);">
                                        <span class="nominal-price" style="font-weight: 700; color: #3b82f6; font-size: 1.05rem; font-family: 'Outfit', sans-serif;">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</span>
                                        <span style="font-size: 0.68rem; color: #10b981; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 2px 6px; border-radius: 4px; display: flex; align-items: center; gap: 3px; font-weight: 600;">
                                            <i class="fa-solid fa-bolt" style="font-size: 0.6rem; color: #eab308;"></i> Instan
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            Produk tidak tersedia untuk kategori ini.
                        </div>
                    @endforelse
                </div>

                <!-- Step 3: Choose Payment Gateway -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <h3 class="step-title">Pilih Metode Pembayaran</h3>
                    </div>

                    @if(count($paymentChannels) > 0)
                        @php
                            // Filter only unique and clean payment channels (hide redundant QRIS codes)
                            $ewalletCodes = ['QRIS', 'SHOPEEPAY', 'OVO', 'DANA', 'LINKAJA', 'JENIUS_PAY'];
                            $vaCodes = ['BCAVA', 'MANDIRIVA', 'BNIVA', 'BRIVA', 'PERMATAVA', 'CIMBVA', 'ATM_BERSAMA_VA', 'MAYBANKVA', 'BSIVA', 'AGVA', 'SAMPOERNAVA', 'NOBUVA'];
                            $retailCodes = ['RETAIL', 'INDOMARET'];
                            $otherCodes = ['CREDIT_CARD', 'INDODANA_PAYLATER'];

                            $ewalletChannels = array_filter($paymentChannels, fn($c) => in_array($c['code'], $ewalletCodes));
                            $vaChannels = array_filter($paymentChannels, fn($c) => in_array($c['code'], $vaCodes));
                            $retailChannels = array_filter($paymentChannels, fn($c) => in_array($c['code'], $retailCodes));
                            $otherChannels = array_filter($paymentChannels, fn($c) => in_array($c['code'], $otherCodes));
                        @endphp

                        <div class="payment-accordion-container">
                            <!-- 1. QRIS Accordion (Only 1 item: QRIS) -->
                            @if(count($ewalletChannels) > 0)
                                @php
                                    $qrisChannel = collect($ewalletChannels)->firstWhere('code', 'QRIS');
                                @endphp
                                @if($qrisChannel)
                                    <div class="accordion-panel active">
                                        <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                            <div class="accordion-title-area">
                                                <span class="accordion-title">QRIS</span>
                                                <div class="accordion-header-logos">
                                                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" class="header-logo-badge">
                                                </div>
                                            </div>
                                            <i class="fa-solid fa-chevron-up accordion-arrow"></i>
                                        </div>
                                        <div class="accordion-content-panel">
                                            <div class="payment-grid-layout">
                                                <div class="payment-row-item" data-code="{{ $qrisChannel['code'] }}" data-fee-flat="{{ $qrisChannel['fee_flat'] }}" data-fee-percent="{{ $qrisChannel['fee_percent'] }}" onclick="selectPayment('{{ $qrisChannel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" class="payment-icon-img">
                                                        <span class="payment-name-txt">QRIS</span>
                                                    </div>
                                                    <div class="payment-right-side">
                                                        <span class="payment-row-price" data-base-price="0">Rp 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <!-- 2. E-Wallet Accordion (Exclude QRIS) -->
                            @php
                                $filteredEwallets = array_filter($ewalletChannels, fn($c) => $c['code'] !== 'QRIS');
                            @endphp
                            @if(count($filteredEwallets) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <div class="accordion-title-area">
                                            <span class="accordion-title">E-Wallet</span>
                                            <div class="accordion-header-logos">
                                                @foreach($filteredEwallets as $channel)
                                                    @if(!empty($channel['icon_url']))
                                                        <img src="{{ $channel['icon_url'] }}" alt="logo" class="header-logo-badge">
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($filteredEwallets as $channel)
                                                @php
                                                    $displayName = $channel['name'];
                                                    if ($channel['code'] === 'SHOPEEPAY') {
                                                        $displayName = 'ShopeePay/SPayLater';
                                                    } elseif ($channel['code'] === 'LINKAJA') {
                                                        $displayName = 'LinkAja';
                                                    }
                                                @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($channel['icon_url']))
                                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $displayName }}" class="payment-icon-img">
                                                        @endif
                                                        <span class="payment-name-txt">{{ $displayName }}</span>
                                                    </div>
                                                    <div class="payment-right-side">
                                                        <span class="payment-row-price" data-base-price="0">Rp 0</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 3. Virtual Account Accordion -->
                            @if(count($vaChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <div class="accordion-title-area">
                                            <span class="accordion-title">Virtual Account</span>
                                            <div class="accordion-header-logos">
                                                @foreach(array_slice($vaChannels, 0, 4) as $channel)
                                                    @if(!empty($channel['icon_url']))
                                                        <img src="{{ $channel['icon_url'] }}" alt="logo" class="header-logo-badge">
                                                    @endif
                                                @endforeach
                                                @if(count($vaChannels) > 4)
                                                    <span class="header-logo-plus">+{{ count($vaChannels) - 4 }} lainnya</span>
                                                @endif
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($vaChannels as $channel)
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($channel['icon_url']))
                                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" class="payment-icon-img">
                                                        @endif
                                                        <span class="payment-name-txt">{{ $channel['name'] }}</span>
                                                    </div>
                                                    <div class="payment-right-side">
                                                        <span class="payment-row-price" data-base-price="0">Rp 0</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 4. Convenience Store Accordion -->
                            @if(count($retailChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <div class="accordion-title-area">
                                            <span class="accordion-title">Convenience Store</span>
                                            <div class="accordion-header-logos">
                                                @foreach($retailChannels as $channel)
                                                    @if(!empty($channel['icon_url']))
                                                        <img src="{{ $channel['icon_url'] }}" alt="logo" class="header-logo-badge">
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($retailChannels as $channel)
                                                @php
                                                    $displayName = $channel['name'];
                                                    if ($channel['code'] === 'RETAIL') {
                                                        $displayName = 'Alfamart';
                                                    }
                                                @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($channel['icon_url']))
                                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $displayName }}" class="payment-icon-img">
                                                        @endif
                                                        <span class="payment-name-txt">{{ $displayName }}</span>
                                                    </div>
                                                    <div class="payment-right-side">
                                                        <span class="payment-row-price" data-base-price="0">Rp 0</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 5. Credit Card & Paylater Accordion -->
                            @if(count($otherChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <div class="accordion-title-area">
                                            <span class="accordion-title">Kartu Kredit & Paylater</span>
                                            <div class="accordion-header-logos">
                                                @foreach($otherChannels as $channel)
                                                    @if(!empty($channel['icon_url']))
                                                        <img src="{{ $channel['icon_url'] }}" alt="logo" class="header-logo-badge">
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($otherChannels as $channel)
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($channel['icon_url']))
                                                            <img src="{{ $channel['icon_url'] }}" alt="{{ $channel['name'] }}" class="payment-icon-img">
                                                        @endif
                                                        <span class="payment-name-txt">{{ $channel['name'] }}</span>
                                                    </div>
                                                    <div class="payment-right-side">
                                                        <span class="payment-row-price" data-base-price="0">Rp 0</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Fallback / Sandbox default channels if API offline -->
                        <div class="payment-accordion-container">
                            <div class="accordion-panel active">
                                <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                    <span class="accordion-title">Metode Instan</span>
                                    <i class="fa-solid fa-chevron-up accordion-arrow"></i>
                                </div>
                                <div class="accordion-content-panel">
                                    <div class="payment-grid-layout">
                                        <div class="payment-row-item" data-code="QRIS" data-fee-flat="0" data-fee-percent="0.7" onclick="selectPayment('QRIS')">
                                            <div class="payment-left-side">
                                                <span class="payment-name-txt">QRIS (Semua E-Wallet)</span>
                                            </div>
                                            <div class="payment-right-side">
                                                <span class="payment-row-price">Rp 0</span>
                                            </div>
                                        </div>
                                        <div class="payment-row-item" data-code="BCAVA" data-fee-flat="1500" data-fee-percent="0" onclick="selectPayment('BCAVA')">
                                            <div class="payment-left-side">
                                                <span class="payment-name-txt">BCA Virtual Account</span>
                                            </div>
                                            <div class="payment-right-side">
                                                <span class="payment-row-price">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Step 4: Checkout Action -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <h3 class="step-title">Masukkan Kontak & Konfirmasi</h3>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Miliki Kode Promo / Voucher?</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" name="voucher_code" id="voucherCodeInput" class="form-control" placeholder="Masukkan kode voucher (Opsional)" style="flex-grow: 1;">
                            <button type="button" id="btnApplyVoucher" onclick="applyVoucherCode()" style="background: rgba(226, 135, 67, 0.1); border: 1px solid #e28743; color: #e28743; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 700; cursor: pointer; transition: all 0.2s;">Terapkan</button>
                        </div>
                        <span id="voucherFeedback" style="display: block; font-size: 0.8rem; margin-top: 0.35rem; display: none;"></span>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary);">Nomor WhatsApp (Untuk Notifikasi Transaksi)</label>
                        <input type="text" name="customer_phone" class="form-control" value="{{ Auth::check() ? Auth::user()->phone : '' }}" placeholder="Contoh: 081234567890" required>
                    </div>

                    @auth
                        <div style="background: {{ Auth::user()->points_balance > 0 ? 'rgba(139, 92, 246, 0.05)' : 'rgba(255, 255, 255, 0.02)' }}; border: 1px solid {{ Auth::user()->points_balance > 0 ? 'rgba(139, 92, 246, 0.15)' : 'var(--border-color)' }}; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; opacity: {{ Auth::user()->points_balance > 0 ? '1' : '0.6' }};">
                            <label style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; color: #fff; cursor: {{ Auth::user()->points_balance > 0 ? 'pointer' : 'not-allowed' }}; user-select: none;">
                                <input type="checkbox" name="use_points" id="usePointsCheckbox" value="1" onchange="togglePointsUsage()" {{ Auth::user()->points_balance > 0 ? '' : 'disabled' }} style="accent-color: #8b5cf6; width: 18px; height: 18px;">
                                <div>
                                    <span style="font-weight: 700; color: {{ Auth::user()->points_balance > 0 ? '#8b5cf6' : 'var(--text-secondary)' }}; display: block;">Gunakan Poin Loyalti</span>
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                        @if(Auth::user()->points_balance > 0)
                                            Potong harga sebesar Rp {{ number_format(Auth::user()->points_balance, 0, ',', '.') }} (Capped)
                                        @else
                                            Poin Anda tidak mencukupi (0 Pts)
                                        @endif
                                    </span>
                                </div>
                            </label>
                        </div>
                    @endauth

                    <!-- Dynamic Payment Summary Box -->
                    <div id="paymentSummaryBox" style="display: none; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                        <h4 style="color: var(--text-primary); font-size: 0.95rem; font-weight: 600; margin-top: 0; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; align-items: center;">
                            <i class="fa-solid fa-receipt" style="color: var(--accent-blue); margin-right: 0.5rem;"></i> Ringkasan Pembayaran
                        </h4>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                            <span>Harga Produk:</span>
                            <span id="summaryProductPrice">Rp 0</span>
                        </div>
                        <div id="summaryVoucherRow" style="display: none; justify-content: space-between; font-size: 0.85rem; color: #10b981; margin-bottom: 0.5rem;">
                            <span>Diskon Voucher:</span>
                            <span id="summaryVoucherDiscount">-Rp 0</span>
                        </div>
                        <div id="summaryPointsRow" style="display: none; justify-content: space-between; font-size: 0.85rem; color: #8b5cf6; margin-bottom: 0.5rem;">
                            <span>Potongan Poin:</span>
                            <span id="summaryPointsUsed">-Rp 0</span>
                        </div>
                        @auth
                            <div id="summaryPointsEarnedRow" style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #10b981; margin-bottom: 0.5rem;">
                                <span>Estimasi Poin Diperoleh:</span>
                                <span id="summaryPointsEarned" style="font-weight: 600;">+0 Pts</span>
                            </div>
                        @endauth
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                            <span>Biaya Layanan:</span>
                            <span id="summaryPaymentFee">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: var(--text-primary); font-weight: 700; margin-top: 0.75rem; border-top: 1px dashed var(--border-color); padding-top: 0.75rem;">
                            <span>Total Bayar:</span>
                            <span id="summaryTotalPrice" style="color: var(--accent-blue);">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-checkout">
                        <i class="fa-solid fa-cart-shopping"></i> Beli Sekarang
                    </button>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
    let selectedPrice = 0;
    let selectedFeeFlat = 0;
    let selectedFeePercent = 0;
    let appliedDiscount = 0;
    let appliedVoucherCode = '';

    function selectProduct(id, price) {
        // Remove active class from all nominal cards
        document.querySelectorAll('.nominal-card').forEach(card => {
            card.classList.remove('active');
        });
        
        // Add active class to clicked card
        const card = document.querySelector(`.nominal-card[data-product-id="${id}"]`);
        if (card) {
            card.classList.add('active');
        }

        // Reset voucher when product is changed to prevent exploitation
        resetVoucher();

        // Set hidden input value
        document.getElementById('selectedProductId').value = id;
        selectedPrice = price;
        
        // Dynamic price update on all accordion rows
        updateAllPaymentRowPrices();
        updateSummary();
    }

    function selectPayment(code) {
        // Remove active class from all payment items
        document.querySelectorAll('.payment-row-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        const item = document.querySelector(`.payment-row-item[data-code="${code}"]`);
        if (item) {
            item.classList.add('active');
            selectedFeeFlat = parseFloat(item.getAttribute('data-fee-flat') || 0);
            selectedFeePercent = parseFloat(item.getAttribute('data-fee-percent') || 0);
        }

        // Set hidden input value
        document.getElementById('selectedPaymentMethod').value = code;
        updateSummary();
    }

    function updateAllPaymentRowPrices() {
        if (!selectedPrice) return;
        
        const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

        document.querySelectorAll('.payment-row-item').forEach(item => {
            const feeFlat = parseFloat(item.getAttribute('data-fee-flat') || 0);
            const feePercent = parseFloat(item.getAttribute('data-fee-percent') || 0);
            
            // Calculate base price after discount
            const discountedPrice = Math.max(0, selectedPrice - appliedDiscount);

            let fee = feeFlat;
            if (feePercent > 0) {
                fee += Math.round((discountedPrice * feePercent) / 100);
            }
            const total = discountedPrice + fee;

            const priceEl = item.querySelector('.payment-row-price');
            if (priceEl) {
                priceEl.innerText = formatRupiah(total);
            }
        });
    }

    function togglePaymentAccordion(headerElement) {
        const currentPanel = headerElement.parentElement;
        const arrow = headerElement.querySelector('.accordion-arrow');
        
        const isActive = currentPanel.classList.contains('active');
        
        // Close all other panels
        document.querySelectorAll('.accordion-panel').forEach(panel => {
            panel.classList.remove('active');
            const panelArrow = panel.querySelector('.accordion-arrow');
            if (panelArrow) {
                panelArrow.classList.remove('fa-chevron-up');
                panelArrow.classList.add('fa-chevron-down');
            }
        });
        
        // Toggle current panel
        if (!isActive) {
            currentPanel.classList.add('active');
            if (arrow) {
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-up');
            }
        } else {
            if (arrow) {
                arrow.classList.remove('fa-chevron-up');
                arrow.classList.add('fa-chevron-down');
            }
        }
    }

    function togglePointsUsage() {
        updateSummary();
    }

    function updateSummary() {
        const productId = document.getElementById('selectedProductId').value;
        const paymentMethod = document.getElementById('selectedPaymentMethod').value;
        const summaryBox = document.getElementById('paymentSummaryBox');

        if (productId && paymentMethod) {
            summaryBox.style.display = 'block';

            // Calculate dynamic fee on discounted price
            const discountedPrice = Math.max(0, selectedPrice - appliedDiscount);

            let fee = selectedFeeFlat;
            if (selectedFeePercent > 0) {
                fee += Math.round((discountedPrice * selectedFeePercent) / 100);
            }
            let total = discountedPrice + fee;

            // Format to Rupiah
            const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

            // Points deduction handling
            const usePointsCheckbox = document.getElementById('usePointsCheckbox');
            const summaryPointsRow = document.getElementById('summaryPointsRow');
            let pointsDeducted = 0;

            if (usePointsCheckbox && usePointsCheckbox.checked) {
                const pointsBalance = parseInt("{{ Auth::check() ? Auth::user()->points_balance : 0 }}") || 0;
                // Keep at least Rp 1,000 for Duitku
                pointsDeducted = Math.min(pointsBalance, Math.max(0, total - 1000));
                
                if (pointsDeducted > 0) {
                    total -= pointsDeducted;
                    if (summaryPointsRow) {
                        summaryPointsRow.style.display = 'flex';
                        document.getElementById('summaryPointsUsed').innerText = '-' + formatRupiah(pointsDeducted);
                    }
                } else {
                    if (summaryPointsRow) {
                        summaryPointsRow.style.display = 'none';
                    }
                }
            } else {
                if (summaryPointsRow) {
                    summaryPointsRow.style.display = 'none';
                }
            }

            // Points earned display
            const summaryPointsEarned = document.getElementById('summaryPointsEarned');
            if (summaryPointsEarned) {
                const pointsEarned = Math.floor(selectedPrice * 0.01);
                summaryPointsEarned.innerText = '+' + new Intl.NumberFormat('id-ID').format(pointsEarned) + ' Pts';
            }

            document.getElementById('summaryProductPrice').innerText = formatRupiah(selectedPrice);
            document.getElementById('summaryPaymentFee').innerText = fee > 0 ? formatRupiah(fee) : 'Rp 0';
            document.getElementById('summaryTotalPrice').innerText = formatRupiah(total);
        } else {
            summaryBox.style.display = 'none';
        }
    }

    function resetVoucher() {
        appliedDiscount = 0;
        appliedVoucherCode = '';
        const voucherInput = document.getElementById('voucherCodeInput');
        if (voucherInput) {
            voucherInput.value = '';
            voucherInput.removeAttribute('readonly');
        }
        const feedback = document.getElementById('voucherFeedback');
        if (feedback) {
            feedback.style.display = 'none';
            feedback.innerText = '';
        }
        const applyBtn = document.getElementById('btnApplyVoucher');
        if (applyBtn) {
            applyBtn.innerText = 'Terapkan';
            applyBtn.disabled = false;
            applyBtn.style.opacity = '1';
            applyBtn.style.borderColor = '#e28743';
            applyBtn.style.color = '#e28743';
            applyBtn.style.background = 'rgba(226, 135, 67, 0.1)';
        }
        const summaryVoucherRow = document.getElementById('summaryVoucherRow');
        if (summaryVoucherRow) {
            summaryVoucherRow.style.display = 'none';
        }
        updateAllPaymentRowPrices();
        updateSummary();
    }

    function applyVoucherCode() {
        const productId = document.getElementById('selectedProductId').value;
        const codeInput = document.getElementById('voucherCodeInput');
        const feedback = document.getElementById('voucherFeedback');
        const applyBtn = document.getElementById('btnApplyVoucher');
        
        if (!productId) {
            alert('Silakan pilih nominal produk terlebih dahulu sebelum memasukkan voucher!');
            return;
        }

        const code = codeInput.value.trim();
        if (!code) {
            alert('Silakan masukkan kode voucher!');
            return;
        }

        // If a voucher is already applied, clicking again will reset/remove it
        if (appliedVoucherCode) {
            resetVoucher();
            return;
        }

        feedback.style.display = 'block';
        feedback.style.color = '#a1a1aa';
        feedback.innerText = 'Memvalidasi...';
        applyBtn.disabled = true;
        applyBtn.style.opacity = '0.5';

        fetch('/api/validate-voucher', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                code: code,
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appliedDiscount = parseFloat(data.discount || 0);
                appliedVoucherCode = data.code;
                
                feedback.style.color = '#10b981';
                feedback.innerText = `Voucher berhasil diterapkan! Diskon ${data.formatted_discount}`;
                
                codeInput.setAttribute('readonly', 'true');
                applyBtn.disabled = false;
                applyBtn.style.opacity = '1';
                applyBtn.innerText = 'Batal';
                applyBtn.style.borderColor = '#ef4444';
                applyBtn.style.color = '#ef4444';
                applyBtn.style.background = 'rgba(239, 68, 68, 0.1)';

                const summaryVoucherRow = document.getElementById('summaryVoucherRow');
                if (summaryVoucherRow) {
                    summaryVoucherRow.style.display = 'flex';
                    document.getElementById('summaryVoucherDiscount').innerText = '-' + data.formatted_discount;
                }
                
                updateAllPaymentRowPrices();
                updateSummary();
            } else {
                feedback.style.color = '#ef4444';
                feedback.innerText = data.message || 'Gagal menerapkan voucher.';
                applyBtn.disabled = false;
                applyBtn.style.opacity = '1';
                applyBtn.innerText = 'Terapkan';
            }
        })
        .catch(error => {
            console.error('Error validating voucher:', error);
            feedback.style.color = '#ef4444';
            feedback.innerText = 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.';
            applyBtn.disabled = false;
            applyBtn.style.opacity = '1';
            applyBtn.innerText = 'Terapkan';
        });
    }

    // Dynamic Nickname Checker for Games ONLY
    const accountUserIdInput = document.getElementById('account_user_id');
    const accountZoneIdInput = document.getElementById('account_zone_id');
    const nicknameResult = document.getElementById('nickname_check_result');
    const currentGameSlug = "{{ strtolower($category->slug) }}";
    const isCategoryNicknameCheckEnabled = {{ ($category->is_nickname_check_enabled ?? true) ? 'true' : 'false' }};
    const nicknameCheckProvider = "{{ $category->nickname_check_provider ?? 'public' }}";

    const nicknameSupportedGames = [
        'mobile-legends', 'mobile-legends-bang-bang', 'mlbb', 'magic-chess',
        'free-fire', 'free-fire-max', 'ff',
        'genshin-impact', 'honkai-star-rail',
        'pubg-mobile', 'pubg',
        'valorant',
        'call-of-duty-mobile', 'codm',
        'point-blank', 'pb',
        'honor-of-kings', 'hok'
    ];

    const isNicknameCheckSupported = isCategoryNicknameCheckEnabled && nicknameCheckProvider !== 'disabled' && nicknameSupportedGames.includes(currentGameSlug);

    let nicknameCheckTimeout = null;

    if (accountUserIdInput && nicknameResult && isNicknameCheckSupported) {
        const triggerNicknameCheck = () => {
            clearTimeout(nicknameCheckTimeout);

            const userId = accountUserIdInput.value.trim();
            const zoneId = accountZoneIdInput ? accountZoneIdInput.value.trim() : '';

            // If game needs zone/server (e.g. MLBB, Magic Chess, Genshin, Valorant) and it's empty, wait
            const needsZone = ['mobile-legends', 'mobile-legends-bang-bang', 'mlbb', 'magic-chess', 'magic-chess-go-go', 'magic-chess-pass', 'mc', 'genshin-impact', 'honkai-star-rail', 'valorant'].includes(currentGameSlug);

            if (userId === '' || (needsZone && zoneId === '')) {
                nicknameResult.style.display = 'none';
                return;
            }

            nicknameCheckTimeout = setTimeout(() => {
                // Display loading status right before fetch
                nicknameResult.style.display = 'flex';
                nicknameResult.style.borderColor = 'rgba(234, 179, 8, 0.3)';
                nicknameResult.style.color = '#eab308'; // Warning yellow
                nicknameResult.innerHTML = `<i class="fa-solid fa-spinner fa-spin" style="margin-right: 0.35rem;"></i> Memverifikasi data akun...`;
                fetch(`/api/check-nickname?game=${encodeURIComponent(currentGameSlug)}&id=${encodeURIComponent(userId)}&zone=${encodeURIComponent(zoneId)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.disabled) {
                            nicknameResult.style.display = 'none';
                        } else if (data.success && data.nickname) {
                            nicknameResult.style.display = 'flex';
                            nicknameResult.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                            nicknameResult.style.color = '#10b981'; // Green
                            const providerBadge = data.provider === 'digiflazz' ? ' <span style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.15); padding: 2px 6px; border-radius: 4px; margin-left: 0.4rem;">Resmi</span>' : '';
                            nicknameResult.innerHTML = `<i class="fa-solid fa-circle-check" style="margin-right: 0.35rem;"></i> Nickname: <strong style="margin-left: 0.25rem;">${data.nickname}</strong>${providerBadge}`;
                        } else if (data.offline) {
                            nicknameResult.style.display = 'flex';
                            nicknameResult.style.borderColor = 'rgba(234, 179, 8, 0.3)';
                            nicknameResult.style.color = '#eab308'; // Yellow notice
                            nicknameResult.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.35rem;"></i> Server verifikasi nama sedang sibuk. Anda tetap dapat melakukan transaksi (pastikan ID sudah benar).`;
                        } else {
                            nicknameResult.style.display = 'flex';
                            nicknameResult.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                            nicknameResult.style.color = '#ef4444'; // Red
                            nicknameResult.innerHTML = `<i class="fa-solid fa-circle-xmark" style="margin-right: 0.35rem;"></i> ${data.message || 'Data akun tidak ditemukan.'}`;
                        }
                    })
                    .catch(error => {
                        console.error('Error validating account:', error);
                        nicknameResult.style.display = 'flex';
                        nicknameResult.style.borderColor = 'rgba(234, 179, 8, 0.3)';
                        nicknameResult.style.color = '#eab308';
                        nicknameResult.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.35rem;"></i> Server verifikasi nama sedang sibuk. Anda tetap dapat melakukan transaksi (pastikan ID sudah benar).`;
                    });
            }, 400); // 400ms debounce
        };

        accountUserIdInput.addEventListener('input', triggerNicknameCheck);
        accountUserIdInput.addEventListener('blur', triggerNicknameCheck);
        if (accountZoneIdInput) {
            accountZoneIdInput.addEventListener('input', triggerNicknameCheck);
            accountZoneIdInput.addEventListener('change', triggerNicknameCheck);
            accountZoneIdInput.addEventListener('blur', triggerNicknameCheck);
        }
    }

    // Client-side validation before submit
    document.getElementById('topupForm').addEventListener('submit', function(e) {
        const productId = document.getElementById('selectedProductId').value;
        const paymentMethod = document.getElementById('selectedPaymentMethod').value;

        if (!productId) {
            e.preventDefault();
            alert('Silakan pilih nominal top-up terlebih dahulu!');
            return;
        }

        if (!paymentMethod) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran terlebih dahulu!');
            return;
        }
    });
</script>
@endsection
