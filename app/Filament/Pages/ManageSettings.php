<?php

namespace App\Filament\Pages;

use App\Models\PointLog;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Sistem';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengaturan Sistem, Promo & Tampilan';

    protected string $view = 'filament.pages.manage-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormState());
    }

    protected function getFormState(): array
    {
        $startedAt = Setting::get('promo_grand_opening_started_at');
        $query = PointLog::where('type', 'welcome_bonus');
        if (! empty($startedAt)) {
            $query->where('created_at', '>=', $startedAt);
        }
        $claimedCount = $query->count();
        $quota = (int) Setting::get('promo_grand_opening_quota', '100');
        $progressText = $quota > 0
            ? "{$claimedCount} dari {$quota} kuota member telah menerima bonus"
            : "{$claimedCount} member telah menerima bonus (Tanpa batas kuota)";

        return [
            'promo_grand_opening_active' => Setting::get('promo_grand_opening_active', '0'),
            'promo_grand_opening_points' => Setting::get('promo_grand_opening_points', '2000'),
            'promo_grand_opening_quota' => Setting::get('promo_grand_opening_quota', '100'),
            'promo_grand_opening_progress' => $progressText,
            'review_section_enabled' => Setting::get('review_section_enabled', '1'),
            'review_display_limit' => Setting::get('review_display_limit', '3'),
            'review_autoplay_speed' => Setting::get('review_autoplay_speed', '5'),
            'cs_whatsapp_url' => Setting::get('cs_whatsapp_url', 'https://wa.me/6281234567890'),
            'social_instagram' => Setting::get('social_instagram', 'https://instagram.com'),
            'social_tiktok' => Setting::get('social_tiktok', 'https://tiktok.com'),
            'social_youtube' => Setting::get('social_youtube', 'https://youtube.com'),
            'social_whatsapp' => Setting::get('social_whatsapp', 'https://wa.me/6281234567890'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Promo Grand Opening & Welcome Bonus Poin')
                    ->description('Berikan bonus poin gratis secara otomatis kepada member baru yang mendaftar dan memverifikasi WhatsApp saat periode Grand Opening.')
                    ->headerActions([
                        Action::make('resetPromoCounter')
                            ->label('Reset Hitungan / Mulai Sesi Baru')
                            ->icon('heroicon-o-arrow-path')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Reset Hitungan Kuota Promo?')
                            ->modalDescription('Hitungan member yang menerima bonus akan dimulai kembali dari 0 untuk periode baru. Poin yang sudah diterima member lama tetap aman dan tidak akan hilang.')
                            ->modalSubmitActionLabel('Ya, Reset ke 0')
                            ->action(function () {
                                Setting::set('promo_grand_opening_started_at', now()->toDateTimeString());
                                $this->mount();
                                Notification::make()
                                    ->title('Hitungan Kuota Berhasil Di-reset')
                                    ->body('Sesi promo baru telah dimulai. Hitungan kuota kini dimulai kembali dari 0.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Select::make('promo_grand_opening_active')
                            ->label('Status Promo')
                            ->options([
                                '0' => 'Nonaktif (OFF)',
                                '1' => 'Aktif (ON)',
                            ])
                            ->required(),
                        TextInput::make('promo_grand_opening_points')
                            ->label('Nominal Poin Bonus')
                            ->numeric()
                            ->minValue(0)
                            ->default(2000)
                            ->helperText('Jumlah poin gratis yang diberikan ke setiap member baru.'),
                        TextInput::make('promo_grand_opening_quota')
                            ->label('Batas Kuota Pendaftar')
                            ->numeric()
                            ->minValue(0)
                            ->default(100)
                            ->helperText('Contoh: 100 untuk 100 member pertama. Isi 0 jika tanpa batas kuota.'),
                        TextInput::make('promo_grand_opening_progress')
                            ->label('Progres Klaim Poin')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Section::make('Pengaturan Tampilan Ulasan Pelanggan')
                    ->description('Atur apakah ulasan pelanggan ditampilkan di halaman depan (homepage) dan tentukan batas jumlah ulasan yang tampil.')
                    ->schema([
                        Select::make('review_section_enabled')
                            ->label('Status Tampilan Ulasan di Homepage')
                            ->options([
                                '1' => 'Tampilkan (ON)',
                                '0' => 'Sembunyikan (OFF)',
                            ])
                            ->required(),
                        Select::make('review_display_limit')
                            ->label('Jumlah Ulasan per Slide (Maksimal 6)')
                            ->options([
                                '3' => '3 Ulasan per Slide',
                                '6' => '6 Ulasan per Slide (Maksimal)',
                            ])
                            ->default('3')
                            ->required()
                            ->helperText('Pilih 3 atau 6 ulasan per slide. Jika jumlah ulasan aktif melebihi angka ini, sistem otomatis menampilkan tombol slider navigasi.'),
                        Select::make('review_autoplay_speed')
                            ->label('Kecepatan Slide Otomatis (Autoplay)')
                            ->options([
                                '3' => '3 Detik (Cepat)',
                                '5' => '5 Detik (Sedang - Standar)',
                                '7' => '7 Detik (Santai)',
                                '10' => '10 Detik (Lambat)',
                                '0' => 'Nonaktifkan (Hanya Geser Manual)',
                            ])
                            ->default('5')
                            ->required()
                            ->helperText('Tentukan durasi perpindahan slide ulasan secara otomatis di halaman beranda.'),
                    ])->columns(3),

                Section::make('Kontak Layanan Pelanggan (CS) & Media Sosial')
                    ->description('Kelola tautan menu Hubungi CS dan ikon media sosial resmi yang tampil pada footer website.')
                    ->schema([
                        TextInput::make('cs_whatsapp_url')
                            ->label('Tautan Menu "Hubungi CS"')
                            ->placeholder('https://wa.me/6281234567890')
                            ->helperText('Tautan saat pelanggan mengklik menu Hubungi CS di footer (misal: WhatsApp, Telegram, atau livechat).')
                            ->columnSpanFull(),
                        TextInput::make('social_instagram')
                            ->label('URL Akun Instagram')
                            ->placeholder('https://instagram.com/username')
                            ->helperText('Tautan ikon Instagram di footer. Kosongkan jika ingin disembunyikan.'),
                        TextInput::make('social_tiktok')
                            ->label('URL Akun TikTok')
                            ->placeholder('https://tiktok.com/@username')
                            ->helperText('Tautan ikon TikTok di footer. Kosongkan jika ingin disembunyikan.'),
                        TextInput::make('social_youtube')
                            ->label('URL Channel YouTube')
                            ->placeholder('https://youtube.com/@channel')
                            ->helperText('Tautan ikon YouTube di footer. Kosongkan jika ingin disembunyikan.'),
                        TextInput::make('social_whatsapp')
                            ->label('URL Ikon WhatsApp Footer')
                            ->placeholder('https://wa.me/6281234567890')
                            ->helperText('Tautan saat mengklik ikon WhatsApp di footer. Kosongkan jika ingin disembunyikan.'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ([
            'promo_grand_opening_active', 'promo_grand_opening_points', 'promo_grand_opening_quota',
            'review_section_enabled', 'review_display_limit', 'review_autoplay_speed',
            'cs_whatsapp_url', 'social_instagram', 'social_tiktok', 'social_youtube', 'social_whatsapp',
        ] as $key) {
            Setting::set($key, (string) ($data[$key] ?? ''));
        }

        $this->form->fill($this->getFormState());

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Pengaturan sistem, promo, dan tampilan telah diperbarui.')
            ->success()
            ->send();
    }
}
