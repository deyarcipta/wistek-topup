@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">
            
            <!-- Top Line: User Info & Secondary Actions -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 1rem;">
                
                <!-- User Profile & Greeting -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <x-filament-panels::avatar.user
                        size="lg"
                        :user="$user"
                        loading="lazy"
                    />

                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #ffffff; margin: 0; line-height: 1.2;">
                                Selamat Datang, <span style="color: #f59e0b; font-weight: 800;">{{ filament()->getUserName($user) }}</span> 👋
                            </h2>
                            <span style="padding: 0.15rem 0.5rem; border-radius: 0.375rem; background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.7rem; font-weight: 700; color: #a5b4fc; text-transform: uppercase;">
                                {{ strtoupper($user->role ?? 'ADMIN') }}
                            </span>
                        </div>

                        <p style="font-size: 0.8125rem; color: #9ca3af; margin: 0.25rem 0 0 0;">
                            Panel Kontrol Utama Wistek Topup &bull; <span style="color: #d1d5db; font-weight: 500;">{{ $todayDate }}</span>
                        </p>
                    </div>
                </div>

                <!-- Secondary Actions (Toko Web & Logout) -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="{{ url('/') }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; border-radius: 0.5rem; background-color: rgba(99, 102, 241, 0.15); color: #c7d2fe; font-size: 0.8125rem; font-weight: 500; text-decoration: none; border: 1px solid rgba(99, 102, 241, 0.3); transition: all 0.15s ease;">
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
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.25); font-size: 0.775rem; font-weight: 600; color: #4ade80;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #4ade80;"></span>
                        Sistem Aktif
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; background: {{ $waStatus['enabled'] ? 'rgba(34, 197, 94, 0.12)' : 'rgba(245, 158, 11, 0.12)' }}; border: 1px solid {{ $waStatus['enabled'] ? 'rgba(34, 197, 94, 0.25)' : 'rgba(245, 158, 11, 0.25)' }}; font-size: 0.775rem; font-weight: 600; color: {{ $waStatus['enabled'] ? '#4ade80' : '#fbbf24' }};">
                        💬 Open-WA: <strong>{{ $waStatus['message'] }}</strong>
                    </span>


                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; border-radius: 0.5rem; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.25); font-size: 0.775rem; font-weight: 600; color: #818cf8;">
                        💳 Gateway: <strong>{{ $gatewayName }}</strong>
                    </span>
                </div>

                <!-- Primary Action Buttons -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="{{ url('/w1st3k/transactions') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background-color: #16a34a; color: #ffffff; font-size: 0.8125rem; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        <span>+ Order Cash (Kasir)</span>
                    </a>

                    <a href="{{ url('/w1st3k/manage-api-settings') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background-color: #374151; color: #f3f4f6; font-size: 0.8125rem; font-weight: 600; text-decoration: none; border: 1px solid #4b5563;">
                        <span>⚙️ Pengaturan API</span>
                    </a>
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
