@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">
            
            <!-- Top Line: User Info & Secondary Actions -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%; padding-bottom: 1rem;" class="border-b border-gray-200 dark:border-gray-800">
                
                <!-- User Profile & Greeting -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <x-filament-panels::avatar.user
                        size="lg"
                        :user="$user"
                        loading="lazy"
                    />

                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <h2 class="text-gray-900 dark:text-white" style="font-size: 1.25rem; font-weight: 700; margin: 0; line-height: 1.2;">
                                Selamat Datang, <span class="text-amber-600 dark:text-amber-400" style="font-weight: 800;">{{ filament()->getUserName($user) }}</span> 👋
                            </h2>
                            <span class="bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800/60" style="padding: 0.15rem 0.5rem; border-radius: 0.375rem; border-width: 1px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                {{ strtoupper($user->role ?? 'ADMIN') }}
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-400" style="font-size: 0.8125rem; margin: 0.25rem 0 0 0;">
                            Panel Kontrol Utama Wistek Topup &bull; <span class="text-gray-800 dark:text-gray-300" style="font-weight: 600;">{{ $todayDate }}</span>
                        </p>
                    </div>
                </div>

                <!-- Secondary Actions (Toko Web & Logout) -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="{{ url('/') }}" target="_blank" class="bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:border-indigo-800/50 dark:hover:bg-indigo-900/50" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; text-decoration: none; border-width: 1px; transition: all 0.15s ease;">
                        <span>🌐 Lihat Web Store</span>
                    </a>

                    <form action="{{ filament()->getLogoutUrl() }}" method="post" style="margin: 0;">
                        @csrf
                        <x-filament::button
                            color="gray"
                            :icon="\Filament\Support\Icons\Heroicon::ArrowLeftEndOnRectangle"
                            labeled-from="sm"
                            tag="button"
                            type="submit"
                            size="sm"
                        >
                            Keluar
                        </x-filament::button>
                    </form>
                </div>
            </div>

            <!-- Bottom Line: Status Chips & Primary Quick Action Buttons -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%;">
                
                <!-- Live Status Pills -->
                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <span class="bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/50" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; border-width: 1px; font-size: 0.775rem; font-weight: 600;">
                        <span class="bg-emerald-500" style="width: 7px; height: 7px; border-radius: 50%;"></span>
                        Sistem Aktif
                    </span>

                    <span class="{{ $waStatus['enabled'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/50' : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800/50' }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; border-width: 1px; font-size: 0.775rem; font-weight: 600;">
                        💬 Open-WA: <strong>{{ $waStatus['message'] }}</strong>
                    </span>

                    @php
                        $isGatewayActive = !empty($gatewayStatus['success']);
                    @endphp
                    <span class="{{ $isGatewayActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800/50' : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800/50' }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; border-width: 1px; font-size: 0.775rem; font-weight: 600;">
                        💳 Gateway: <strong>{{ $gatewayName }} {{ $isGatewayActive ? 'Aktif' : 'Belum Konfigurasi' }}</strong>
                    </span>
                </div>

                <!-- Primary Action Buttons -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="{{ url('/w1st3k/transactions') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background-color: #16a34a; color: #ffffff; font-size: 0.8125rem; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                        <span>+ Order Cash (Kasir)</span>
                    </a>

                    <a href="{{ url('/w1st3k/manage-api-settings') }}" class="bg-gray-800 text-white border-gray-700 hover:bg-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; text-decoration: none; border-width: 1px;">
                        <span>⚙️ Pengaturan API</span>
                    </a>
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
