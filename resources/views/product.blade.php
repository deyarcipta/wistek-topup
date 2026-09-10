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
        margin-left: auto;
        margin-right: 0.75rem;
    }

    .header-logo-badge {
        height: 20px;
        width: auto;
        background: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        object-fit: contain;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .header-logo-plus {
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        line-height: 1;
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
        height: 22px;
        width: auto;
        max-width: 65px;
        background: #fff;
        padding: 2px 6px;
        border-radius: 4px;
        object-fit: contain;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
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

    /* Custom Modal Sleek Scrollbar */
    .modal-custom-scroll {
        max-height: 75vh;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(226, 135, 67, 0.5) rgba(15, 15, 20, 0.6);
    }

    .modal-custom-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .modal-custom-scroll::-webkit-scrollbar-track {
        background: rgba(15, 15, 20, 0.6);
        border-radius: 4px;
    }

    .modal-custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(226, 135, 67, 0.4);
        border-radius: 4px;
    }

    .modal-custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #e28743;
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
                <img src="{{ $category->thumbnail ?? 'https://placehold.co/150x150/1e293b/ffffff?text=' . urlencode($category->name) }}" alt="{{ $category->name }}" onerror="this.onerror=null; this.src='https://placehold.co/150x150/1e293b/ffffff?text={{ urlencode($category->name) }}';">
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
                            $qrisChannels = [];
                            $vaChannels = [];
                            $ewalletChannels = [];
                            $retailChannels = [];
                            $otherChannels = [];

                            foreach ($paymentChannels as $c) {
                                $code = strtoupper((string)($c['code'] ?? ''));
                                $name = strtoupper((string)($c['name'] ?? ''));

                                // 1. QRIS Check: code or name contains QRIS
                                if ($code === 'QRIS' || str_contains($code, 'QRIS') || str_contains($name, 'QRIS')) {
                                    $qrisChannels[] = $c;
                                    continue;
                                }

                                // 2. Virtual Account Check:
                                $isKnownVaCode = in_array($code, [
                                    'BCAVA', 'MANDIRIVA', 'BNIVA', 'BRIVA', 'PERMATAVA', 'CIMBVA', 'ATM_BERSAMA_VA', 
                                    'MAYBANKVA', 'BSIVA', 'AGVA', 'SAMPOERNAVA', 'NOBUVA', 'DANAMONVA', 'BNCVA', 
                                    'BSSVA', 'BTNVA', 'BJBVA', 'OTHER_VA', 'OTHER_BANK_VA', 'ATM_BERSAMA', 'BC', 'M2', 
                                    'I1', 'BR', 'BT', 'B1', 'A1', 'VA', 'BV', 'AG', 'S1', 'NC'
                                ]);
                                
                                $isVaByNameOrCode = str_contains($code, 'VA') || 
                                                    str_contains($name, 'VIRTUAL ACCOUNT') || 
                                                    str_contains($name, 'VIRTUAL') || 
                                                    str_contains($name, 'VA ') || 
                                                    str_contains($name, ' VA') || 
                                                    str_contains($name, 'OTHER BANK') || 
                                                    str_contains($name, 'BANK LAIN') || 
                                                    str_contains($name, 'ATM BERSAMA');

                                if ($isKnownVaCode || $isVaByNameOrCode) {
                                    $vaChannels[] = $c;
                                    continue;
                                }

                                // 3. Convenience Store / Retail Check:
                                $isRetail = in_array($code, ['RETAIL', 'INDOMARET', 'ALFAMART', 'FT', 'IR', 'ALFA']) ||
                                            str_contains($code, 'ALFA') || str_contains($code, 'INDOMARET') || str_contains($code, 'RETAIL') ||
                                            str_contains($name, 'ALFAMART') || str_contains($name, 'INDOMARET') || str_contains($name, 'ALFA GROUP') || str_contains($name, 'RETAIL');

                                if ($isRetail) {
                                    $retailChannels[] = $c;
                                    continue;
                                }

                                // 4. E-Wallet Check:
                                $isEwallet = in_array($code, ['SHOPEEPAY', 'SHOPEEPAY_APP', 'OVO', 'DANA', 'LINKAJA', 'LINKAJA_QRIS', 'JENIUS_PAY', 'DOKU_WALLET', 'AKULAKU', 'SP', 'SA', 'OV', 'DA', 'LA', 'JP']) ||
                                             str_contains($code, 'SHOPEE') || str_contains($code, 'OVO') || str_contains($code, 'DANA') || str_contains($code, 'LINKAJA') || str_contains($code, 'WALLET') || str_contains($code, 'PAYLATER') ||
                                             str_contains($name, 'SHOPEE') || str_contains($name, 'OVO') || str_contains($name, 'DANA') || str_contains($name, 'LINKAJA') || str_contains($name, 'E-WALLET') || str_contains($name, 'PAYLATER');

                                if ($isEwallet) {
                                    $ewalletChannels[] = $c;
                                    continue;
                                }

                                // 5. Leftovers: Portal Pembayaran & Lainnya
                                $otherChannels[] = $c;
                            }
                        @endphp

                        <div class="payment-accordion-container">
                            <!-- 1. QRIS Accordion (Paling Atas & Active by default) -->
                            @if(count($qrisChannels) > 0)
                                <div class="accordion-panel active">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <span class="accordion-title">QRIS</span>
                                        <div class="accordion-header-logos">
                                            <img src="{{ asset('images/payments/qris.svg') }}" alt="QRIS" class="header-logo-badge" onerror="this.style.display='none'">
                                        </div>
                                        <i class="fa-solid fa-chevron-up accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($qrisChannels as $channel)
                                                @php
                                                    $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? asset('images/payments/qris.svg');
                                                @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" data-min-fee="{{ $channel['min_fee'] ?? 0 }}" data-max-fee="{{ $channel['max_fee'] ?? 0 }}" data-free-min-amount="{{ $channel['free_min_amount'] ?? 0 }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        <img src="{{ $imgUrl }}" alt="{{ $channel['name'] }}" class="payment-icon-img" onerror="this.src='{{ asset('images/payments/qris.svg') }}'">
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

                            <!-- 2. Virtual Account Accordion (Includes Other Bank VA) -->
                            @if(count($vaChannels) > 0)
                                <div class="accordion-panel {{ count($qrisChannels) === 0 ? 'active' : '' }}">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <span class="accordion-title">Virtual Account</span>
                                        <div class="accordion-header-logos">
                                            @php
                                                $maxLogos = 4;
                                                $sliceLogos = array_slice($vaChannels, 0, $maxLogos);
                                                $remLogos = count($vaChannels) - count($sliceLogos);
                                            @endphp
                                            @foreach($sliceLogos as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                @if(!empty($imgUrl))
                                                    <img src="{{ $imgUrl }}" alt="logo" class="header-logo-badge" onerror="this.style.display='none'">
                                                @endif
                                            @endforeach
                                            @if($remLogos > 0)
                                                <span class="header-logo-plus">+{{ $remLogos }}</span>
                                            @endif
                                        </div>
                                        <i class="fa-solid {{ count($qrisChannels) === 0 ? 'fa-chevron-up' : 'fa-chevron-down' }} accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($vaChannels as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" data-min-fee="{{ $channel['min_fee'] ?? 0 }}" data-max-fee="{{ $channel['max_fee'] ?? 0 }}" data-free-min-amount="{{ $channel['free_min_amount'] ?? 0 }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($imgUrl))
                                                            <img src="{{ $imgUrl }}" alt="{{ $channel['name'] }}" class="payment-icon-img" onerror="this.style.display='none'">
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

                            <!-- 3. E-Wallet Accordion -->
                            @if(count($ewalletChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <span class="accordion-title">E-Wallet</span>
                                        <div class="accordion-header-logos">
                                            @php
                                                $maxLogos = 4;
                                                $sliceLogos = array_slice($ewalletChannels, 0, $maxLogos);
                                                $remLogos = count($ewalletChannels) - count($sliceLogos);
                                            @endphp
                                            @foreach($sliceLogos as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                @if(!empty($imgUrl))
                                                    <img src="{{ $imgUrl }}" alt="logo" class="header-logo-badge" onerror="this.style.display='none'">
                                                @endif
                                            @endforeach
                                            @if($remLogos > 0)
                                                <span class="header-logo-plus">+{{ $remLogos }}</span>
                                            @endif
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($ewalletChannels as $channel)
                                                @php
                                                    $displayName = $channel['name'];
                                                    if ($channel['code'] === 'SHOPEEPAY') {
                                                        $displayName = 'ShopeePay/SPayLater';
                                                    } elseif ($channel['code'] === 'LINKAJA') {
                                                        $displayName = 'LinkAja';
                                                    }
                                                    $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? '';
                                                @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" data-min-fee="{{ $channel['min_fee'] ?? 0 }}" data-max-fee="{{ $channel['max_fee'] ?? 0 }}" data-free-min-amount="{{ $channel['free_min_amount'] ?? 0 }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($imgUrl))
                                                            <img src="{{ $imgUrl }}" alt="{{ $displayName }}" class="payment-icon-img" onerror="this.style.display='none'">
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

                            <!-- 4. Convenience Store Accordion -->
                            @if(count($retailChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <span class="accordion-title">Convenience Store</span>
                                        <div class="accordion-header-logos">
                                            @php
                                                $maxLogos = 4;
                                                $sliceLogos = array_slice($retailChannels, 0, $maxLogos);
                                                $remLogos = count($retailChannels) - count($sliceLogos);
                                            @endphp
                                            @foreach($sliceLogos as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                @if(!empty($imgUrl))
                                                    <img src="{{ $imgUrl }}" alt="logo" class="header-logo-badge" onerror="this.style.display='none'">
                                                @endif
                                            @endforeach
                                            @if($remLogos > 0)
                                                <span class="header-logo-plus">+{{ $remLogos }}</span>
                                            @endif
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
                                                    $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? '';
                                                @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" data-min-fee="{{ $channel['min_fee'] ?? 0 }}" data-max-fee="{{ $channel['max_fee'] ?? 0 }}" data-free-min-amount="{{ $channel['free_min_amount'] ?? 0 }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($imgUrl))
                                                            <img src="{{ $imgUrl }}" alt="{{ $displayName }}" class="payment-icon-img" onerror="this.style.display='none'">
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

                            <!-- 5. Portal Pembayaran & Lainnya Accordion -->
                            @if(count($otherChannels) > 0)
                                <div class="accordion-panel">
                                    <div class="accordion-header" onclick="togglePaymentAccordion(this)">
                                        <span class="accordion-title">Portal Pembayaran & Lainnya</span>
                                        <div class="accordion-header-logos">
                                            @php
                                                $maxLogos = 4;
                                                $sliceLogos = array_slice($otherChannels, 0, $maxLogos);
                                                $remLogos = count($otherChannels) - count($sliceLogos);
                                            @endphp
                                            @foreach($sliceLogos as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                @if(!empty($imgUrl))
                                                    <img src="{{ $imgUrl }}" alt="logo" class="header-logo-badge" onerror="this.style.display='none'">
                                                @endif
                                            @endforeach
                                            @if($remLogos > 0)
                                                <span class="header-logo-plus">+{{ $remLogos }}</span>
                                            @endif
                                        </div>
                                        <i class="fa-solid fa-chevron-down accordion-arrow"></i>
                                    </div>
                                    <div class="accordion-content-panel">
                                        <div class="payment-grid-layout">
                                            @foreach($otherChannels as $channel)
                                                @php $imgUrl = $channel['icon_url'] ?? $channel['icon'] ?? ''; @endphp
                                                <div class="payment-row-item" data-code="{{ $channel['code'] }}" data-fee-flat="{{ $channel['fee_flat'] }}" data-fee-percent="{{ $channel['fee_percent'] }}" data-min-fee="{{ $channel['min_fee'] ?? 0 }}" data-max-fee="{{ $channel['max_fee'] ?? 0 }}" data-free-min-amount="{{ $channel['free_min_amount'] ?? 0 }}" onclick="selectPayment('{{ $channel['code'] }}')">
                                                    <div class="payment-left-side">
                                                        @if(!empty($imgUrl))
                                                            <img src="{{ $imgUrl }}" alt="{{ $channel['name'] }}" class="payment-icon-img" onerror="this.style.display='none'">
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
                        <!-- Message when payment channels are unconfigured or unavailable -->
                        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; padding: 1.5rem; text-align: center; color: var(--text-secondary);">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.8rem; color: #f59e0b; margin-bottom: 0.5rem; display: block;"></i>
                            <p style="font-weight: 700; color: #fff; margin-bottom: 0.25rem; font-size: 0.95rem;">Metode Pembayaran Belum Aktif</p>
                            <p style="font-size: 0.85rem;">Metode pembayaran pada provider aktif belum dikonfigurasi di Admin Panel. Silakan hubungi Customer Service.</p>
                        </div>
                    @endif
                </div>

                <!-- Step 4: Detail Kontak -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <h3 class="step-title">Detail Kontak</h3>
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary); font-weight: 500;">Email (Opsional)</label>
                        <input type="email" name="customer_email" class="form-control" value="{{ Auth::check() ? Auth::user()->email : '' }}" placeholder="example@gmail.com">
                    </div>

                    <div style="margin-bottom: 0.5rem; position: relative;">
                        <label style="font-size: 0.9rem; margin-bottom: 0.5rem; display: block; color: var(--text-secondary); font-weight: 500;">No. WhatsApp</label>
                        <div style="display: flex; align-items: center;">
                            <div id="countryDropdownBtn" onclick="toggleCountryDropdown(event)" style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); border-right: none; border-top-left-radius: 8px; border-bottom-left-radius: 8px; padding: 0.75rem 0.85rem; display: flex; align-items: center; gap: 0.4rem; color: #fff; font-weight: 700; font-size: 0.9rem; cursor: pointer; user-select: none; transition: background 0.2s;">
                                <span id="selectedCountryFlag" style="font-size: 1.1rem; line-height: 1;">🇮🇩</span>
                                <i class="fa-solid fa-angle-down" style="font-size: 0.7rem; color: var(--text-secondary);"></i>
                                <span id="selectedCountryDial" style="margin-left: 0.2rem;">+62</span>
                            </div>
                            <input type="hidden" name="country_dial_code" id="countryDialCodeInput" value="+62">
                            <input type="text" name="customer_phone" class="form-control" value="{{ Auth::check() ? Auth::user()->phone : '' }}" placeholder="81234567890" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" required>
                        </div>

                        <!-- Country Dropdown Popup Menu -->
                        <div id="countryDropdownMenu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; background: #1f1f23; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; width: 280px; box-shadow: 0 15px 35px rgba(0,0,0,0.7); z-index: 9999; overflow: hidden; font-family: 'Outfit', sans-serif;">
                            <!-- Search Bar -->
                            <div style="padding: 0.65rem 0.75rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-magnifying-glass" style="color: var(--text-secondary); font-size: 0.85rem;"></i>
                                <input type="text" id="countrySearchInput" onkeyup="filterCountryList()" placeholder="Cari negara..." style="background: none; border: none; outline: none; color: #fff; font-size: 0.85rem; width: 100%;">
                            </div>
                            <!-- Country Items List -->
                            <div id="countryListContainer" class="modal-custom-scroll" style="max-height: 220px; overflow-y: auto; padding: 0.35rem;">
                            </div>
                        </div>

                        <p style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 0.4rem; font-style: italic;">
                            **Nomor ini akan dihubungi jika terjadi masalah
                        </p>
                    </div>

                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 0.85rem 1rem; margin-top: 1rem; display: flex; align-items: center; gap: 0.75rem; color: var(--text-secondary); font-size: 0.85rem;">
                        <i class="fa-solid fa-circle-info" style="font-size: 1.1rem; color: #e28743;"></i>
                        <span>Jika ada kendala, kami akan menghubungi nomor WA kamu diatas</span>
                    </div>
                </div>

                <!-- Step 5: Kode Promo -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">5</div>
                        <h3 class="step-title">Kode Promo</h3>
                    </div>
                    
                    <div>
                        <div style="display: flex; gap: 0.75rem;">
                            <input type="text" name="voucher_code" id="voucherCodeInput" class="form-control" placeholder="Ketik Kode Promo Kamu" style="flex-grow: 1;">
                            <button type="button" id="btnApplyVoucher" onclick="applyVoucherCode()" style="background: #e28743; border: none; color: #fff; padding: 0.75rem 1.75rem; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap;">Gunakan</button>
                        </div>
                        <span id="voucherFeedback" style="display: block; font-size: 0.8rem; margin-top: 0.35rem; display: none;"></span>
                    </div>
                </div>

                <!-- Step 6: Konfirmasi & Pembayaran -->
                <div class="form-step">
                    <div class="step-header">
                        <div class="step-number">6</div>
                        <h3 class="step-title">Konfirmasi & Pembayaran</h3>
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

    <!-- Checkout Confirmation Modal -->
    <div id="checkoutConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: #141418; border: 1px solid rgba(226, 135, 67, 0.35); border-radius: 16px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.8); transform: translateY(20px); transition: transform 0.3s ease;" id="confirmModalCard">
            
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, rgba(226, 135, 67, 0.18), rgba(20, 20, 24, 0.95)); padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fa-solid fa-shield-halved" style="color: #e28743; font-size: 1.2rem;"></i>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #fff; font-size: 1.1rem; margin: 0;">Konfirmasi Detail Pesanan</h3>
                </div>
                <button type="button" onclick="closeCheckoutModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 1.2rem; cursor: pointer; padding: 0.2rem 0.5rem; transition: color 0.2s;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-custom-scroll" style="padding: 1.25rem 1.5rem;">
                
                <div style="background: rgba(226, 135, 67, 0.08); border: 1px solid rgba(226, 135, 67, 0.2); border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.82rem; color: #e28743; display: flex; align-items: flex-start; gap: 0.6rem;">
                    <i class="fa-solid fa-circle-info" style="margin-top: 2px; font-size: 0.95rem; min-width: 16px;"></i>
                    <span>Mohon periksa kembali data tujuan &amp; pesanan Anda. Kesalahan input data bukan tanggung jawab kami.</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem; font-family: 'Outfit', sans-serif; font-size: 0.9rem;">
                    
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem;">
                        <span style="color: var(--text-secondary);">Kategori / Layanan:</span>
                        <span style="font-weight: 700; color: #fff;">{{ $category->name }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem;">
                        <span style="color: var(--text-secondary);">Target / ID Akun:</span>
                        <span id="confirmModalTarget" style="font-weight: 700; color: #3b82f6;">-</span>
                    </div>

                    <div id="confirmModalNicknameRow" style="display: none; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem; background: rgba(16, 185, 129, 0.05); padding: 0.6rem 0.75rem; border-radius: 6px;">
                        <span style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-user-check" style="color: #10b981;"></i> Nickname Akun:</span>
                        <span id="confirmModalNickname" style="font-weight: 800; color: #10b981;">-</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem;">
                        <span style="color: var(--text-secondary);">Item Nominal:</span>
                        <span id="confirmModalItem" style="font-weight: 700; color: #fff;">-</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem;">
                        <span style="color: var(--text-secondary);">Metode Pembayaran:</span>
                        <span id="confirmModalPayment" style="font-weight: 700; color: #f59e0b; text-transform: uppercase;">-</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255, 255, 255, 0.06); padding-bottom: 0.6rem;">
                        <span style="color: var(--text-secondary);">No. WhatsApp:</span>
                        <span id="confirmModalPhone" style="font-weight: 600; color: #fff;">-</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0, 0, 0, 0.35); padding: 0.85rem 1rem; border-radius: 8px; border: 1px solid rgba(226, 135, 67, 0.25); margin-top: 0.25rem;">
                        <span style="font-weight: 700; color: #fff;">Total Pembayaran:</span>
                        <span id="confirmModalTotalPrice" style="font-weight: 800; font-size: 1.2rem; color: #e28743;">Rp 0</span>
                    </div>

                </div>

            </div>

            <!-- Modal Footer Buttons -->
            <div style="padding: 1.25rem 1.5rem; background: rgba(0, 0, 0, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeCheckoutModal()" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-secondary); padding: 0.7rem 1.25rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Outfit', sans-serif;">
                    Batal / Edit
                </button>
                <button type="button" id="btnSubmitFinalOrder" onclick="submitFinalOrder()" style="background: linear-gradient(135deg, #e28743, #d97706); border: none; color: #fff; padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.5rem;">
                    <span>Lanjutkan Pembayaran</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    window.tierRules = @json(\App\Filament\Pages\ManagePriceMarginSettings::getTierRules());

    function getServiceFeePercent(amount) {
        if (!window.tierRules || window.tierRules.length === 0) return 1.0;
        
        const parseNum = (val) => parseFloat(String(val || 0).replace(',', '.')) || 0;

        const sorted = [...window.tierRules].sort((a, b) => {
            const maxA = parseNum(a.max_amount) === 0 ? 999999999 : parseNum(a.max_amount);
            const maxB = parseNum(b.max_amount) === 0 ? 999999999 : parseNum(b.max_amount);
            return maxA - maxB;
        });

        for (let rule of sorted) {
            const max = parseNum(rule.max_amount);
            if (max > 0 && amount <= max) {
                return parseNum(rule.service_fee_percent);
            }
        }

        const defaultRule = sorted[sorted.length - 1];
        return parseNum(defaultRule.service_fee_percent);
    }

    let selectedPrice = 0;
    let selectedFeeFlat = 0;
    let selectedFeePercent = 0;
    let selectedMinFee = 0;
    let selectedMaxFee = 0;
    let selectedFreeMinAmount = 0;
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
            selectedMinFee = parseFloat(item.getAttribute('data-min-fee') || 0);
            selectedMaxFee = parseFloat(item.getAttribute('data-max-fee') || 0);
            selectedFreeMinAmount = parseFloat(item.getAttribute('data-free-min-amount') || 0);
        }

        // Set hidden input value
        document.getElementById('selectedPaymentMethod').value = code;
        updateSummary();
    }

    function updateAllPaymentRowPrices() {
        if (!selectedPrice) return;
        
        const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);

        document.querySelectorAll('.payment-row-item').forEach(item => {
            const code = item.getAttribute('data-code') || '';
            const isQris = (code === 'QRIS' || code.includes('QRIS'));
            const feeFlat = parseFloat(item.getAttribute('data-fee-flat') || 0);
            let feePercent = parseFloat(item.getAttribute('data-fee-percent') || 0);
            const minFee = parseFloat(item.getAttribute('data-min-fee') || 0);
            const maxFee = parseFloat(item.getAttribute('data-max-fee') || 0);
            
            // Calculate base price after discount
            const discountedPrice = Math.max(0, selectedPrice - appliedDiscount);

            if (isQris || feePercent > 0) {
                feePercent = getServiceFeePercent(discountedPrice);
            }

            let fee = feeFlat;
            if (feePercent > 0) {
                fee += Math.round((discountedPrice * feePercent) / 100);
            }
            if (minFee > 0 && fee < minFee) {
                fee = minFee;
            }
            if (maxFee > 0 && fee > maxFee) {
                fee = maxFee;
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

            const isQris = (paymentMethod === 'QRIS' || paymentMethod.includes('QRIS'));
            let feePercent = selectedFeePercent;
            if (isQris || feePercent > 0) {
                feePercent = getServiceFeePercent(discountedPrice);
            }

            let fee = selectedFeeFlat;
            if (feePercent > 0) {
                fee += Math.round((discountedPrice * feePercent) / 100);
            }
            if (selectedMinFee > 0 && fee < selectedMinFee) {
                fee = selectedMinFee;
            }
            if (selectedMaxFee > 0 && fee > selectedMaxFee) {
                fee = selectedMaxFee;
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

    // Modal Confirmation Popup Logic
    let isConfirmedByModal = false;

    function openCheckoutModal() {
        const modal = document.getElementById('checkoutConfirmModal');
        const modalCard = document.getElementById('confirmModalCard');
        
        // Populate Target ID
        const userId = document.getElementById('account_user_id') ? document.getElementById('account_user_id').value.trim() : '';
        const zoneId = document.getElementById('account_zone_id') ? document.getElementById('account_zone_id').value.trim() : '';
        let targetText = userId;
        if (zoneId) {
            targetText += ` (${zoneId})`;
        }
        document.getElementById('confirmModalTarget').innerText = targetText || '-';

        // Populate Nickname if available
        const nicknameResult = document.getElementById('nickname_check_result');
        const nicknameRow = document.getElementById('confirmModalNicknameRow');
        const nicknameEl = document.getElementById('confirmModalNickname');
        
        if (nicknameResult && nicknameResult.style.display !== 'none' && nicknameResult.querySelector('strong')) {
            const nickText = nicknameResult.querySelector('strong').innerText;
            nicknameEl.innerText = nickText;
            nicknameRow.style.display = 'flex';
        } else {
            nicknameRow.style.display = 'none';
        }

        // Populate Item Nominal
        const activeCard = document.querySelector('.nominal-card.active');
        if (activeCard) {
            const nameEl = activeCard.querySelector('.nominal-name');
            document.getElementById('confirmModalItem').innerText = nameEl ? nameEl.innerText : '-';
        }

        // Populate Payment Method Name
        const activePayment = document.querySelector('.payment-row-item.active');
        if (activePayment) {
            const payName = activePayment.querySelector('.payment-name-txt');
            document.getElementById('confirmModalPayment').innerText = payName ? payName.innerText : '-';
        }

        // Populate Phone
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        document.getElementById('confirmModalPhone').innerText = phoneInput ? phoneInput.value.trim() : '-';

        // Populate Total Price
        const totalSummary = document.getElementById('summaryTotalPrice');
        document.getElementById('confirmModalTotalPrice').innerText = totalSummary ? totalSummary.innerText : 'Rp 0';

        // Show Modal
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modalCard.style.transform = 'translateY(0)';
        }, 10);
    }

    function closeCheckoutModal() {
        const modal = document.getElementById('checkoutConfirmModal');
        const modalCard = document.getElementById('confirmModalCard');
        modal.style.opacity = '0';
        modalCard.style.transform = 'translateY(20px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function submitFinalOrder() {
        const btn = document.getElementById('btnSubmitFinalOrder');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memproses...`;
        }
        isConfirmedByModal = true;
        document.getElementById('topupForm').submit();
    }

    // Client-side validation before submit
    document.getElementById('topupForm').addEventListener('submit', function(e) {
        if (isConfirmedByModal) {
            return true;
        }

        const productId = document.getElementById('selectedProductId').value;
        const paymentMethod = document.getElementById('selectedPaymentMethod').value;
        const accountUserId = document.getElementById('account_user_id') ? document.getElementById('account_user_id').value.trim() : '';
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        const phoneVal = phoneInput ? phoneInput.value.trim() : '';

        if (accountUserId === '') {
            e.preventDefault();
            alert('Silakan isi Data Akun / Target ID terlebih dahulu!');
            return;
        }

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

        if (!phoneVal) {
            e.preventDefault();
            alert('Silakan isi nomor WhatsApp untuk notifikasi transaksi!');
            return;
        }

        e.preventDefault();
        openCheckoutModal();
    });

    // Country Code Dropdown Logic
    const countriesList = [
        { code: 'ID', name: 'Indonesia', flag: '🇮🇩', dial: '+62' },
        { code: 'MY', name: 'Malaysia', flag: '🇲🇾', dial: '+60' },
        { code: 'SG', name: 'Singapore', flag: '🇸🇬', dial: '+65' },
        { code: 'BN', name: 'Brunei', flag: '🇧🇳', dial: '+673' },
        { code: 'TH', name: 'Thailand', flag: '🇹🇭', dial: '+66' },
        { code: 'PH', name: 'Philippines', flag: '🇵🇭', dial: '+63' },
        { code: 'VN', name: 'Vietnam', flag: '🇻🇳', dial: '+84' },
        { code: 'US', name: 'United States', flag: '🇺🇸', dial: '+1' },
        { code: 'GB', name: 'United Kingdom', flag: '🇬🇧', dial: '+44' },
        { code: 'AU', name: 'Australia', flag: '🇦🇺', dial: '+61' },
        { code: 'JP', name: 'Japan', flag: '🇯🇵', dial: '+81' },
        { code: 'KR', name: 'South Korea', flag: '🇰🇷', dial: '+82' },
        { code: 'CN', name: 'China', flag: '🇨🇳', dial: '+86' },
        { code: 'IN', name: 'India', flag: '🇮🇳', dial: '+91' },
        { code: 'SA', name: 'Saudi Arabia', flag: '🇸🇦', dial: '+966' },
        { code: 'TR', name: 'Turkey', flag: '🇹🇷', dial: '+90' },
        { code: 'AF', name: 'Afghanistan', flag: '🇦🇫', dial: '+93' },
        { code: 'AX', name: 'Åland Islands', flag: '🇦🇽', dial: '+358' },
        { code: 'AL', name: 'Albania', flag: '🇦🇱', dial: '+355' },
        { code: 'DZ', name: 'Algeria', flag: '🇩🇿', dial: '+213' },
        { code: 'AS', name: 'American Samoa', flag: '🇦🇸', dial: '+1' },
        { code: 'AD', name: 'Andorra', flag: '🇦🇩', dial: '+376' },
        { code: 'AO', name: 'Angola', flag: '🇦🇴', dial: '+244' },
        { code: 'AI', name: 'Anguilla', flag: '🇦🇮', dial: '+1' },
        { code: 'AR', name: 'Argentina', flag: '🇦🇷', dial: '+54' },
        { code: 'BR', name: 'Brazil', flag: '🇧🇷', dial: '+55' },
        { code: 'DE', name: 'Germany', flag: '🇩🇪', dial: '+49' },
        { code: 'FR', name: 'France', flag: '🇫🇷', dial: '+33' },
        { code: 'NL', name: 'Netherlands', flag: '🇳🇱', dial: '+31' },
        { code: 'AE', name: 'United Arab Emirates', flag: '🇦🇪', dial: '+971' }
    ];

    function renderCountryList(filterText = '') {
        const container = document.getElementById('countryListContainer');
        if (!container) return;

        const query = filterText.toLowerCase().trim();
        const filtered = countriesList.filter(c => 
            c.name.toLowerCase().includes(query) || c.dial.includes(query)
        );

        if (filtered.length === 0) {
            container.innerHTML = `<div style="padding: 0.75rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">Negara tidak ditemukan</div>`;
            return;
        }

        container.innerHTML = filtered.map(c => `
            <div onclick="selectCountryCode('${c.flag}', '${c.dial}')" style="padding: 0.55rem 0.75rem; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-radius: 6px; transition: background 0.15s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                <div style="display: flex; align-items: center; gap: 0.6rem; color: #fff;">
                    <span style="font-size: 1.1rem; line-height: 1;">${c.flag}</span>
                    <span style="font-weight: 500;">${c.name}</span>
                </div>
                <span style="color: var(--text-secondary); font-weight: 600;">${c.dial}</span>
            </div>
        `).join('');
    }

    function toggleCountryDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('countryDropdownMenu');
        if (!menu) return;

        const isHidden = menu.style.display === 'none';
        menu.style.display = isHidden ? 'block' : 'none';

        if (isHidden) {
            renderCountryList('');
            const searchInput = document.getElementById('countrySearchInput');
            if (searchInput) {
                searchInput.value = '';
                setTimeout(() => searchInput.focus(), 50);
            }
        }
    }

    function filterCountryList() {
        const query = document.getElementById('countrySearchInput').value;
        renderCountryList(query);
    }

    function selectCountryCode(flag, dial) {
        document.getElementById('selectedCountryFlag').innerText = flag;
        document.getElementById('selectedCountryDial').innerText = dial;
        document.getElementById('countryDialCodeInput').value = dial;
        document.getElementById('countryDropdownMenu').style.display = 'none';
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('countryDropdownMenu');
        const btn = document.getElementById('countryDropdownBtn');
        if (menu && menu.style.display !== 'none' && !menu.contains(e.target) && !btn.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
</script>
@endsection
