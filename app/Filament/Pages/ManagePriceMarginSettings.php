<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

class ManagePriceMarginSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Pengaturan Harga & Margin';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pengaturan Formula Tier Margin & Biaya Layanan';

    protected string $view = 'filament.pages.manage-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public ?array $data = [];

    public static function getDefaultTierRules(): array
    {
        return [
            ['max_amount' => 5000, 'margin_online' => 600, 'margin_cash' => 400, 'service_fee_percent' => 8.0, 'note' => 'Nominal ≤ Rp 5.000'],
            ['max_amount' => 10000, 'margin_online' => 1000, 'margin_cash' => 700, 'service_fee_percent' => 8.0, 'note' => 'Nominal ≤ Rp 10.000'],
            ['max_amount' => 50000, 'margin_online' => 1500, 'margin_cash' => 1000, 'service_fee_percent' => 5.0, 'note' => 'Nominal ≤ Rp 50.000'],
            ['max_amount' => 100000, 'margin_online' => 2200, 'margin_cash' => 1500, 'service_fee_percent' => 3.5, 'note' => 'Nominal ≤ Rp 100.000'],
            ['max_amount' => 0, 'margin_online' => 3.5, 'margin_cash' => 2.5, 'service_fee_percent' => 2.0, 'note' => 'Nominal > Rp 100.000 (Default / Persen)'],
        ];
    }

    public static function getTierRules(): array
    {
        $raw = Setting::get('tiered_margin_rules');
        if (empty($raw)) {
            return self::getDefaultTierRules();
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) && count($decoded) > 0 ? $decoded : self::getDefaultTierRules();
    }

    public function mount(): void
    {
        $this->form->fill([
            'tiered_margin_rules' => self::getTierRules(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculatePrices')
                ->label('Hitung & Sinkronkan Semua Harga (Cash & Online)')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Hitung Ulang Semua Harga Produk')
                ->modalDescription('Apakah Anda yakin ingin memproses ulang seluruh harga Online dan harga Cash (dengan pembulatan pintar) untuk 275+ produk berdasarkan tabel tier terbaru ini?')
                ->action(function () {
                    $exitCode = Artisan::call('products:recalculate-prices');
                    $output = Artisan::output();

                    if ($exitCode === 0) {
                        Notification::make()
                            ->title('Harga Berhasil Diperbarui!')
                            ->body('Seluruh harga Online dan Cash telah di-recalculate dengan sukses.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal Rekalkulasi Harga')
                            ->body($output)
                            ->danger()
                            ->send();
                    }

                    $this->mount();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tabel Pengaturan Formula Tier Margin & Biaya Layanan')
                    ->description('Kelola batas nominal modal, margin keuntungan Online (Rp / %), margin Cash (Rp / %), dan Persentase Biaya Layanan Checkout.')
                    ->schema([
                        Repeater::make('tiered_margin_rules')
                            ->label('Daftar Aturan Tier Margin & Biaya Layanan')
                            ->schema([
                                TextInput::make('max_amount')
                                    ->label('Batas Nominal Maksimal (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Isi 0 untuk tier sisa / default di atas nominal terbesar.'),

                                TextInput::make('margin_online')
                                    ->label('Margin Online (Rp / %)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Nominal Rp (untuk <= 100k) atau Persen % (untuk > 100k).'),

                                TextInput::make('margin_cash')
                                    ->label('Margin Cash (Rp / %)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Nominal Rp (untuk <= 100k) atau Persen % (untuk > 100k).'),

                                TextInput::make('service_fee_percent')
                                    ->label('Persentase Biaya Layanan (%)')
                                    ->numeric()
                                    ->step('0.1')
                                    ->required()
                                    ->helperText('Persentase biaya layanan checkout (misal: 8.0, 5.0, 3.5, 2.0).'),

                                TextInput::make('note')
                                    ->label('Catatan / Keterangan')
                                    ->placeholder('Contoh: Tier Nominal <= Rp 5.000')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(5)
                            ->reorderable()
                            ->addActionLabel('Tambah Tier Margin Baru')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('tiered_margin_rules', json_encode($data['tiered_margin_rules'] ?? []));

        // Auto recalculate prices after saving tier settings
        Artisan::call('products:recalculate-prices');

        Notification::make()
            ->title('Pengaturan Margin Disimpan & Harga Diperbarui')
            ->body('Pengaturan tier margin baru berhasil disimpan dan seluruh harga produk di database telah otomatis dihitung ulang.')
            ->success()
            ->send();
    }
}
