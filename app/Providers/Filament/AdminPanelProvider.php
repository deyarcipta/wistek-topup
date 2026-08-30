<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('w1st3k')
            ->brandLogo(fn () => new HtmlString('
                <div style="display: flex; align-items: center; gap: 0.65rem; padding-left: 0.25rem;">
                    <img src="'.asset('logo.png').'" alt="Logo" style="height: 34px; width: 34px; object-fit: contain;">
                    <span style="font-family: \'Outfit\', sans-serif; font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; display: inline-flex; align-items: center;">
                        <span style="color: #6366f1;">Wistek</span><span style="color: #8b5cf6; font-weight: 500; margin-left: 0.25rem;">Topup</span>
                    </span>
                </div>
            '))
            ->brandLogoHeight('38px')
            ->favicon(fn () => asset('logo.png'))
            ->login()
            ->profile(EditProfile::class, isSimple: false)
            ->userMenuItems([
                'profile' => fn (Action $action) => $action->label('Edit Profil Saya'),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make('Katalog Produk')->collapsible(),
                NavigationGroup::make('Pengguna & Member')->collapsible(),
                NavigationGroup::make('Promosi & Konten')->collapsible(),
                NavigationGroup::make('Pengaturan')->collapsible(),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('
                    <style>
                        /* Rapatkan jarak antar menu dropdown agar sejajar & rapat seperti Dasbor - Transaksi */
                        .fi-sidebar-nav-groups {
                            gap: 0.25rem !important;
                        }
                        .fi-sidebar-group {
                            margin-top: 0 !important;
                            margin-bottom: 0.15rem !important;
                        }

                        /* Main Menu Dropdown Header Styling */
                        .fi-sidebar-group-btn {
                            display: flex !important;
                            align-items: center !important;
                            cursor: pointer !important;
                            padding: 0.45rem 0.65rem !important;
                            border-radius: 0.5rem !important;
                            transition: all 0.15s ease !important;
                        }
                        .fi-sidebar-group-btn:hover {
                            background-color: rgba(255, 255, 255, 0.04) !important;
                        }
                        .fi-sidebar-group-label {
                            font-weight: 500 !important;
                            font-size: 0.875rem !important;
                            color: #9ca3af !important; /* Warna netral abu-abu seperti Transaksi */
                            flex: 1 !important;
                            transition: color 0.15s ease !important;
                        }
                        .fi-sidebar-group-collapse-btn {
                            color: #9ca3af !important;
                            transition: color 0.15s ease !important;
                        }

                        /* INACTIVE Icons: Warna abu-abu netral seperti menu Transaksi */
                        .fi-sidebar-group[data-group-label="Katalog Produk"] .fi-sidebar-group-btn::before {
                            content: "";
                            display: inline-block;
                            width: 1.2rem;
                            height: 1.2rem;
                            margin-right: 0.65rem;
                            flex-shrink: 0;
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: center;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%239ca3af\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z\' /%3E%3C/svg%3E");
                        }

                        .fi-sidebar-group[data-group-label="Pengguna & Member"] .fi-sidebar-group-btn::before {
                            content: "";
                            display: inline-block;
                            width: 1.2rem;
                            height: 1.2rem;
                            margin-right: 0.65rem;
                            flex-shrink: 0;
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: center;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%239ca3af\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z\' /%3E%3C/svg%3E");
                        }

                        .fi-sidebar-group[data-group-label="Promosi & Konten"] .fi-sidebar-group-btn::before {
                            content: "";
                            display: inline-block;
                            width: 1.2rem;
                            height: 1.2rem;
                            margin-right: 0.65rem;
                            flex-shrink: 0;
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: center;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%239ca3af\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 1 18.75 10.5c0 1.868-.214 3.687-.625 5.435m1.05-11.76a23.83 23.83 0 0 1 1.575 5.865c0 .248-.008.495-.023.74\' /%3E%3C/svg%3E");
                        }

                        .fi-sidebar-group[data-group-label="Pengaturan"] .fi-sidebar-group-btn::before {
                            content: "";
                            display: inline-block;
                            width: 1.2rem;
                            height: 1.2rem;
                            margin-right: 0.65rem;
                            flex-shrink: 0;
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: center;
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%239ca3af\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z\' /%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z\' /%3E%3C/svg%3E");
                        }

                        /* ACTIVE State: Ikon & Teks Berubah Jadi Kuning (#f59e0b) ketika grup dipilih / aktif */
                        .fi-sidebar-group.group-is-active .fi-sidebar-group-label,
                        .fi-sidebar-group.fi-active .fi-sidebar-group-label {
                            color: #f59e0b !important;
                            font-weight: 700 !important;
                        }
                        .fi-sidebar-group.group-is-active .fi-sidebar-group-collapse-btn,
                        .fi-sidebar-group.fi-active .fi-sidebar-group-collapse-btn {
                            color: #f59e0b !important;
                        }

                        .fi-sidebar-group.group-is-active[data-group-label="Katalog Produk"] .fi-sidebar-group-btn::before,
                        .fi-sidebar-group.fi-active[data-group-label="Katalog Produk"] .fi-sidebar-group-btn::before {
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%23f59e0b\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z\' /%3E%3C/svg%3E") !important;
                        }

                        .fi-sidebar-group.group-is-active[data-group-label="Pengguna & Member"] .fi-sidebar-group-btn::before,
                        .fi-sidebar-group.fi-active[data-group-label="Pengguna & Member"] .fi-sidebar-group-btn::before {
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%23f59e0b\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z\' /%3E%3C/svg%3E") !important;
                        }

                        .fi-sidebar-group.group-is-active[data-group-label="Promosi & Konten"] .fi-sidebar-group-btn::before,
                        .fi-sidebar-group.fi-active[data-group-label="Promosi & Konten"] .fi-sidebar-group-btn::before {
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%23f59e0b\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 1 18.75 10.5c0 1.868-.214 3.687-.625 5.435m1.05-11.76a23.83 23.83 0 0 1 1.575 5.865c0 .248-.008.495-.023.74\' /%3E%3C/svg%3E") !important;
                        }

                        .fi-sidebar-group.group-is-active[data-group-label="Pengaturan"] .fi-sidebar-group-btn::before,
                        .fi-sidebar-group.fi-active[data-group-label="Pengaturan"] .fi-sidebar-group-btn::before {
                            background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'%23f59e0b\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z\' /%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z\' /%3E%3C/svg%3E") !important;
                        }

                        /* Sub-Menu Indentation & Compact Font Styling - HANYA untuk grup dropdown bertingkat */
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items {
                            padding-left: 1.25rem !important;
                            margin-left: 0.65rem !important;
                            border-left: 1.5px dashed rgba(255, 255, 255, 0.12) !important;
                            margin-top: 0.25rem !important;
                            margin-bottom: 0.35rem !important;
                        }
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items .fi-sidebar-item-btn {
                            padding-top: 0.32rem !important;
                            padding-bottom: 0.32rem !important;
                            padding-left: 0.5rem !important;
                        }
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items .fi-sidebar-item-label {
                            font-size: 0.8125rem !important; /* Dikecilkan 1-2px (13px) */
                            letter-spacing: 0.01em !important;
                            font-weight: 500 !important;
                            color: #9ca3af !important;
                        }
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items .fi-sidebar-item.fi-active .fi-sidebar-item-label,
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
                            color: #f59e0b !important;
                            font-weight: 600 !important;
                        }
                        .fi-sidebar-group[data-group-label]:not([data-group-label=""]) .fi-sidebar-group-items .fi-sidebar-item-icon {
                            width: 1.15rem !important;
                            height: 1.15rem !important;
                            color: #9ca3af !important;
                        }

                        /* Standalone Main Menus (Dasbor, Transaksi, dll) - Normal, Tebal & Tanpa Garis Putus-Putus */
                        .fi-sidebar-group:not([data-group-label]) .fi-sidebar-group-items,
                        .fi-sidebar-group[data-group-label=""] .fi-sidebar-group-items {
                            padding-left: 0 !important;
                            margin-left: 0 !important;
                            border-left: none !important;
                        }
                        .fi-sidebar-group:not([data-group-label]) .fi-sidebar-group-items .fi-sidebar-item-label,
                        .fi-sidebar-group[data-group-label=""] .fi-sidebar-group-items .fi-sidebar-item-label {
                            font-size: 0.9rem !important;
                            font-weight: 600 !important;
                        }
                        .fi-sidebar-group:not([data-group-label]) .fi-sidebar-group-items .fi-sidebar-item-icon,
                        .fi-sidebar-group[data-group-label=""] .fi-sidebar-group-items .fi-sidebar-item-icon {
                            width: 1.35rem !important;
                            height: 1.35rem !important;
                        }
                    </style>
                    <script>
                        (function() {
                            function syncSidebarAccordion() {
                                var groups = document.querySelectorAll(".fi-sidebar-group[data-group-label]:not([data-group-label=\'\'])");
                                if (!groups.length) return;

                                var activeLabels = [];
                                groups.forEach(function(group) {
                                    var hasActive = group.classList.contains("fi-active") || group.querySelector(".fi-sidebar-item.fi-active, [aria-current=\'page\']");
                                    if (hasActive) {
                                        activeLabels.push(group.dataset.groupLabel);
                                        group.classList.add("group-is-active");
                                    } else {
                                        group.classList.remove("group-is-active");
                                    }
                                });

                                var allLabels = [];
                                groups.forEach(function(g) {
                                    allLabels.push(g.dataset.groupLabel);
                                });

                                var collapsedList = allLabels.filter(function(lbl) {
                                    return !activeLabels.includes(lbl);
                                });

                                localStorage.setItem("collapsedGroups", JSON.stringify(collapsedList));

                                if (window.Alpine && Alpine.store && Alpine.store("sidebar")) {
                                    var store = Alpine.store("sidebar");
                                    allLabels.forEach(function(lbl) {
                                        var shouldBeCollapsed = !activeLabels.includes(lbl);
                                        if (store.groupIsCollapsed(lbl) !== shouldBeCollapsed) {
                                            store.toggleCollapsedGroup(lbl);
                                        }
                                    });
                                }
                            }

                            document.addEventListener("DOMContentLoaded", syncSidebarAccordion);
                            document.addEventListener("alpine:initialized", syncSidebarAccordion);
                            setTimeout(syncSidebarAccordion, 50);
                            setTimeout(syncSidebarAccordion, 250);
                        })();
                    </script>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
