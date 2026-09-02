<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\DigiflazzService;
use App\Services\DuitkuService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use UnitEnum;

class ManageApiSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Pengaturan API';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Pengaturan Integrasi API & Provider';

    protected string $view = 'filament.pages.manage-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncProducts')
                ->label('Sinkronkan Produk Digiflazz')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(function () {
                    $exitCode = Artisan::call('products:sync-digiflazz');
                    $output = Artisan::output();

                    if ($exitCode === 0) {
                        Notification::make()
                            ->title('Sinkronisasi Sukses')
                            ->body(str_replace("\n", '<br>', $output))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Sinkronisasi Gagal')
                            ->body(str_replace("\n", '<br>', $output))
                            ->danger()
                            ->send();
                    }

                    // Refresh page state to show updated balance
                    $this->mount();
                }),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormState());
    }

    protected function getFormState(): array
    {
        $digiflazz = new DigiflazzService;
        $status = $digiflazz->getStatusDetails();

        $duitku = new DuitkuService;
        $duitkuStatus = $duitku->getStatusDetails();

        return [
            'duitku_merchant_code' => Setting::get('duitku_merchant_code'),
            'duitku_api_key' => Setting::get('duitku_api_key'),
            'duitku_mode' => Setting::get('duitku_mode', 'sandbox'),
            'duitku_callback_url' => url('/callback/duitku'),
            'duitku_connection_status' => ($duitkuStatus['success'] ?? false) ? '🟢 Terhubung (OK)' : '🔴 Gagal: '.($duitkuStatus['message'] ?? 'Belum dikonfigurasi'),
            'digiflazz_username' => Setting::get('digiflazz_username'),
            'digiflazz_api_key' => Setting::get('digiflazz_api_key'),
            'digiflazz_webhook_secret' => Setting::get('digiflazz_webhook_secret'),
            'digiflazz_mode' => Setting::get('digiflazz_mode', 'development'),
            'digiflazz_trusted_seller_enabled' => Setting::get('digiflazz_trusted_seller_enabled', '1'),
            'digiflazz_price_tolerance' => Setting::get('digiflazz_price_tolerance', '200'),
            'digiflazz_connection_status' => ($status['success'] ?? false) ? '🟢 Terhubung (OK)' : '🔴 Gagal: '.($status['message'] ?? 'Belum dikonfigurasi'),
            'digiflazz_balance' => ($status['success'] ?? false) ? 'Rp '.number_format((float) ($status['balance'] ?? 0), 0, ',', '.') : 'Rp 0',
            'whatsapp_enabled' => Setting::get('whatsapp_enabled', '0'),
            'whatsapp_api_url' => Setting::get('whatsapp_api_url', 'http://localhost:2785/api'),
            'whatsapp_api_token' => Setting::get('whatsapp_api_token'),
            'whatsapp_session_id' => Setting::get('whatsapp_session_id', 'default'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Duitku Payment Gateway')
                    ->description('Hubungkan sistem pembayaran dengan Duitku. Salin Callback URL ke Duitku Merchant Portal.')
                    ->schema([
                        TextInput::make('duitku_merchant_code')
                            ->label('Merchant Code')
                            ->placeholder('Masukkan Duitku Merchant Code'),
                        TextInput::make('duitku_api_key')
                            ->label('Merchant Key / API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Masukkan Duitku Merchant Key'),
                        Select::make('duitku_mode')
                            ->label('Mode')
                            ->options([
                                'sandbox' => 'Sandbox (Testing)',
                                'production' => 'Production (Live)',
                            ]),
                        TextInput::make('duitku_callback_url')
                            ->label('Callback / Webhook URL (HTTP POST)')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Salin URL ini ke Duitku Merchant Dashboard (Project Callback URL) agar Duitku dapat mengirimkan respon transaksi (HTTP POST).'),
                    ])->columns(2),

                Section::make('Digiflazz H2H Topup')
                    ->description('Hubungkan sistem topup dengan supplier Digiflazz')
                    ->schema([
                        TextInput::make('digiflazz_username')
                            ->label('Username')
                            ->placeholder('Masukkan Username Digiflazz'),
                        TextInput::make('digiflazz_api_key')
                            ->label('API Key / Production Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Masukkan API Key Digiflazz'),
                        TextInput::make('digiflazz_webhook_secret')
                            ->label('Webhook Secret (Opsional)')
                            ->password()
                            ->revealable()
                            ->placeholder('Masukkan Webhook Secret jika ada'),
                        Select::make('digiflazz_mode')
                            ->label('Mode')
                            ->options([
                                'development' => 'Development (Testing)',
                                'production' => 'Production (Live)',
                            ]),
                        Select::make('digiflazz_trusted_seller_enabled')
                            ->label('Algoritma Pencarian Seller')
                            ->options([
                                '1' => 'Smart Trusted Seller (Termurah + Paling Terpercaya/Cepat)',
                                '0' => 'Harga Termurah Saja (Tanpa Filter Kestabilan)',
                            ])
                            ->default('1')
                            ->required()
                            ->helperText('Saat aktif, sistem memilih seller terpercaya & cepat jika selisih harga masih dalam batas toleransi.'),
                        TextInput::make('digiflazz_price_tolerance')
                            ->label('Toleransi Selisih Harga Modal (Rp)')
                            ->numeric()
                            ->default(200)
                            ->placeholder('200')
                            ->helperText('Batas maksimal selisih harga modal (Rp) di mana sistem akan mengutamakan seller yang lebih terpercaya & cepat.'),
                    ])->columns(2),

                Section::make('WhatsApp Notification Gateway (open-wa)')
                    ->description('Konfigurasi notifikasi WhatsApp otomatis untuk pelanggan')
                    ->schema([
                        Select::make('whatsapp_enabled')
                            ->label('Status Fitur')
                            ->options([
                                '0' => 'Nonaktif (Matikan Notifikasi)',
                                '1' => 'Aktif (Kirim Notifikasi)',
                            ])
                            ->required(),
                        TextInput::make('whatsapp_api_url')
                            ->label('API URL Base (open-wa)')
                            ->placeholder('Contoh: http://localhost:2785/api')
                            ->required(),
                        TextInput::make('whatsapp_api_token')
                            ->label('API Bearer Token (Opsional)')
                            ->password()
                            ->revealable()
                            ->placeholder('Masukkan authorization token jika ada'),
                        TextInput::make('whatsapp_session_id')
                            ->label('Session ID WhatsApp')
                            ->placeholder('Masukkan Session ID, contoh: wistek')
                            ->required(),
                        Placeholder::make('whatsapp_qr')
                            ->label('Status Sesi & QR Code')
                            ->content(fn () => view('filament.components.whatsapp-qr'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Status Integrasi & Koneksi API')
                    ->description('Ringkasan status koneksi real-time ke provider payment gateway dan top-up supplier.')
                    ->schema([
                        TextInput::make('duitku_connection_status')
                            ->label('Status Koneksi Duitku')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('digiflazz_connection_status')
                            ->label('Status Koneksi Digiflazz')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('digiflazz_balance')
                            ->label('Saldo Digiflazz')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ([
            'duitku_merchant_code', 'duitku_api_key', 'duitku_mode',
            'digiflazz_username', 'digiflazz_api_key', 'digiflazz_webhook_secret', 'digiflazz_mode',
            'digiflazz_trusted_seller_enabled', 'digiflazz_price_tolerance',
            'whatsapp_enabled', 'whatsapp_api_url', 'whatsapp_api_token', 'whatsapp_session_id',
        ] as $key) {
            Setting::set($key, (string) ($data[$key] ?? ''));
        }

        $this->form->fill($this->getFormState());

        Notification::make()
            ->title('Pengaturan API Berhasil Disimpan')
            ->body('Konfigurasi API Duitku, Digiflazz, dan WhatsApp telah diperbarui.')
            ->success()
            ->send();
    }

    /**
     * Fetch WhatsApp Gateway connection status and QR code live from open-wa server
     */
    public function getWhatsappStatus(): array
    {
        $enabled = Setting::get('whatsapp_enabled', '0') === '1';
        $apiUrl = Setting::get('whatsapp_api_url', 'http://localhost:2785/api');
        $apiToken = Setting::get('whatsapp_api_token');
        $sessionId = Setting::get('whatsapp_session_id', 'default');

        if (! $enabled || empty($apiUrl)) {
            return [
                'success' => false,
                'message' => 'Status Fitur: Nonaktif (Silakan aktifkan terlebih dahulu)',
                'connected' => false,
                'status' => 'DISABLED',
                'qrCode' => null,
            ];
        }

        $baseUrl = rtrim($apiUrl, '/');

        $headers = ['Content-Type' => 'application/json'];
        if (! empty($apiToken)) {
            $headers['Authorization'] = 'Bearer '.$apiToken;
            $headers['X-API-Key'] = $apiToken;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(4)
                ->get("{$baseUrl}/sessions/{$sessionId}");

            if ($response->successful()) {
                $data = $response->json();
                $rawStatus = $data['status'] ?? '';

                $status = 'UNKNOWN';
                if ($rawStatus === 'created' || $rawStatus === 'NOT_STARTED') {
                    $status = 'NOT_STARTED';
                } elseif ($rawStatus === 'ready' || $rawStatus === 'connected' || $rawStatus === 'WORKING' || $rawStatus === 'CONNECTED') {
                    $status = 'CONNECTED';
                } else {
                    $status = $rawStatus;
                }

                $connected = ($status === 'CONNECTED');
                $message = $connected ? 'WhatsApp Terhubung!' : 'Sesi aktif tetapi belum terhubung (Status: '.$status.')';

                $qrCode = null;
                if (! $connected) {
                    $qrResponse = Http::withHeaders($headers)
                        ->timeout(4)
                        ->get("{$baseUrl}/sessions/{$sessionId}/qr");
                    if ($qrResponse->successful()) {
                        $qrData = $qrResponse->json();
                        $qrCode = $qrData['qrCode'] ?? ($qrData['qr'] ?? null);
                    }
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'connected' => $connected,
                    'status' => $status,
                    'qrCode' => $qrCode,
                ];
            } else {
                $statusCode = $response->status();
                if ($statusCode === 404) {
                    return [
                        'success' => true,
                        'message' => 'Sesi belum terdaftar/dimulai di server OpenWA.',
                        'connected' => false,
                        'status' => 'NOT_STARTED',
                        'qrCode' => null,
                    ];
                } elseif ($statusCode === 409) {
                    $qrCode = null;
                    $qrResponse = Http::withHeaders($headers)
                        ->timeout(4)
                        ->get("{$baseUrl}/sessions/{$sessionId}/qr");
                    if ($qrResponse->successful()) {
                        $qrData = $qrResponse->json();
                        $qrCode = $qrData['qrCode'] ?? ($qrData['qr'] ?? null);
                    }

                    return [
                        'success' => true,
                        'message' => 'WhatsApp belum terhubung (Conflict 409).',
                        'connected' => false,
                        'status' => 'NOT_READY',
                        'qrCode' => $qrCode,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Server OpenWA merespon dengan status: '.$statusCode,
                        'connected' => false,
                        'status' => 'ERROR',
                        'qrCode' => null,
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server open-wa: '.$e->getMessage(),
                'connected' => false,
                'status' => 'ERROR',
                'qrCode' => null,
            ];
        }
    }

    /**
     * Register and start the WhatsApp session on open-wa server
     */
    public function startWhatsappSession(): void
    {
        $enabled = Setting::get('whatsapp_enabled', '0') === '1';
        $apiUrl = Setting::get('whatsapp_api_url', 'http://localhost:2785/api');
        $apiToken = Setting::get('whatsapp_api_token');
        $sessionId = Setting::get('whatsapp_session_id', 'default');

        if (! $enabled || empty($apiUrl)) {
            Notification::make()
                ->title('Gagal Memulai Sesi')
                ->body('Status fitur harus aktif untuk memulai sesi.')
                ->warning()
                ->send();

            return;
        }

        // Always generate a fresh UUID to ensure no conflict 409 and valid UUID format
        $sessionId = (string) Str::uuid();
        Setting::set('whatsapp_session_id', $sessionId);

        // Update Filament form state so the UI displays the generated UUID instantly
        $this->data['whatsapp_session_id'] = $sessionId;

        $baseUrl = rtrim($apiUrl, '/');

        $headers = ['Content-Type' => 'application/json'];
        if (! empty($apiToken)) {
            $headers['Authorization'] = 'Bearer '.$apiToken;
            $headers['X-API-Key'] = $apiToken;
        }

        try {
            // 1. Try to register/create session first
            $regResponse = Http::withHeaders($headers)
                ->timeout(5)
                ->post("{$baseUrl}/sessions", [
                    'name' => $sessionId,
                ]);

            if (! $regResponse->successful() && $regResponse->status() !== 409) {
                Notification::make()
                    ->title('Gagal Mendaftarkan Sesi')
                    ->body('Server OpenWA menolak pendaftaran: '.$regResponse->body())
                    ->danger()
                    ->send();

                return;
            }

            // Extract the actual registered ID returned by the open-wa server
            if ($regResponse->successful()) {
                $regData = $regResponse->json();
                $returnedId = $regData['id'] ?? null;

                if ($returnedId && $returnedId !== $sessionId) {
                    $sessionId = $returnedId;
                    Setting::set('whatsapp_session_id', $sessionId);

                    // Update Filament form state so the UI updates to show the correct ID
                    $this->data['whatsapp_session_id'] = $sessionId;
                }
            }

            // 2. Start the session Spec (increased timeout to 60s since launching Chromium is a heavy task)
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->post("{$baseUrl}/sessions/{$sessionId}/start");

            if ($response->successful()) {
                Notification::make()
                    ->title('Sesi WhatsApp Dimulai')
                    ->body('Sesi berhasil diinisialisasi dengan ID: '.$sessionId.'. Silakan tunggu beberapa saat dan klik "Cek Koneksi".')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Gagal Memulai Sesi')
                    ->body('Server OpenWA mengembalikan pesan: '.$response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Koneksi Gagal')
                ->body('Gagal menghubungi OpenWA Server: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
