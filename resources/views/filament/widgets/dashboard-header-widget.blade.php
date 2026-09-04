@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; width: 100%;">
            <!-- Left: Avatar & User Info & Badges -->
            <div style="display: flex; align-items: center; gap: 1rem;">
                <x-filament-panels::avatar.user
                    size="lg"
                    :user="$user"
                    loading="lazy"
                />

                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #ffffff; margin: 0; line-height: 1.3;">
                        Selamat Datang, <span style="color: #f59e0b; font-weight: 800;">{{ filament()->getUserName($user) }}</span> 👋
                    </h2>

                    <p style="font-size: 0.85rem; color: #9ca3af; margin: 0.2rem 0 0.5rem 0;">
                        Panel Kontrol Utama Wistek Topup • <span style="color: #e5e7eb;">{{ $todayDate }}</span>
                    </p>

                    <!-- Status Badges -->
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 0.375rem; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); font-size: 0.75rem; font-weight: 600; color: #4ade80;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #4ade80;"></span>
                            Sistem Online
                        </span>

                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 0.375rem; background: {{ $dfStatus['success'] ? 'rgba(34, 197, 94, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; border: 1px solid {{ $dfStatus['success'] ? 'rgba(34, 197, 94, 0.3)' : 'rgba(239, 68, 68, 0.3)' }}; font-size: 0.75rem; font-weight: 600; color: {{ $dfStatus['success'] ? '#4ade80' : '#f87171' }};">
                            ⚡ Digiflazz: {{ $dfStatus['success'] ? 'Rp '.number_format($dfStatus['balance'], 0, ',', '.') : 'Offline' }}
                        </span>

                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border-radius: 0.375rem; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.75rem; font-weight: 600; color: #818cf8;">
                            💳 Gateway: {{ $gatewayName }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ url('/w1st3k/transactions') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 0.5rem; background-color: #16a34a; color: #ffffff; font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: background-color 0.15s ease;">
                    <span>+ Order Cash (Kasir)</span>
                </a>

                <a href="{{ url('/w1st3k/manage-api-settings') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 0.5rem; background-color: #374151; color: #f3f4f6; font-size: 0.8125rem; font-weight: 500; text-decoration: none; border: 1px solid #4b5563;">
                    <span>⚙️ Pengaturan API</span>
                </a>

                <a href="{{ url('/') }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.9rem; border-radius: 0.5rem; background-color: rgba(99, 102, 241, 0.2); color: #c7d2fe; font-size: 0.8125rem; font-weight: 500; text-decoration: none; border: 1px solid rgba(99, 102, 241, 0.4);">
                    <span>🌐 Toko Web</span>
                </a>

                <form action="{{ filament()->getLogoutUrl() }}" method="post" style="display: inline-block;">
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
    </x-filament::section>
</x-filament-widgets::widget>
