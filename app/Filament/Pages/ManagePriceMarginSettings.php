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
            ['max_amount' => 5000, 'margin_online' => 4.0, 'margin_gold' => 3.0, 'margin_platinum' => 2.0, 'margin_cash' => 5.0, 'service_fee_percent' => 800, 'note' => 'Tier 1: Nominal ≤ Rp 5.000'],
            ['max_amount' => 35000, 'margin_online' => 2.5, 'margin_gold' => 1.5, 'margin_platinum' => 0.5, 'margin_cash' => 3.0, 'service_fee_percent' => 850, 'note' => 'Tier 2: Nominal ≤ Rp 35.000'],
            ['max_amount' => 100000, 'margin_online' => 2.0, 'margin_gold' => 1.2, 'margin_platinum' => 0.4, 'margin_cash' => 2.5, 'service_fee_percent' => 1100, 'note' => 'Tier 3: Nominal ≤ Rp 100.000'],
            ['max_amount' => 300000, 'margin_online' => 1.8, 'margin_gold' => 1.5, 'margin_platinum' => 1.0, 'margin_cash' => 2.0, 'service_fee_percent' => 1300, 'note' => 'Tier 4: Nominal ≤ Rp 300.000'],
            ['max_amount' => 0, 'margin_online' => 4.0, 'margin_gold' => 3.5, 'margin_platinum' => 3.0, 'margin_cash' => 2.0, 'service_fee_percent' => 1600, 'note' => 'Tier 5: Default (> Rp 300.000)'],
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
                ->label('Hitung & Sinkronkan Semua Harga (Member, Cash & Online)')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Hitung Ulang Semua Harga Produk')
                ->modalDescription('Apakah Anda yakin ingin memproses ulang seluruh harga Regular, Gold (VIP), Platinum (VVIP), dan Cash untuk 275+ produk berdasarkan tabel tier terbaru ini?')
                ->action(function () {
                    $exitCode = Artisan::call('products:recalculate-prices');
                    $output = Artisan::output();

                    if ($exitCode === 0) {
                        Notification::make()
                            ->title('Harga Berhasil Diperbarui!')
                            ->body('Seluruh harga Member (Regular, Gold, Platinum) dan Cash telah di-recalculate dengan sukses.')
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
                    ->description('Kelola batas nominal modal, margin keuntungan Regular, Gold Member (VIP), Platinum Member (VVIP), Cash, dan Persentase Biaya Layanan Checkout.')
                    ->schema([
                        Repeater::make('tiered_margin_rules')
                            ->label('Daftar Aturan Tier Margin Member & Biaya Layanan')
                            ->schema([
                                TextInput::make('max_amount')
                                    ->label('Batas Nominal Maksimal (Rp)')
                                    ->required()
                                    ->helperText('Isi 0 untuk tier sisa / default.'),

                                TextInput::make('margin_online')
                                    ->label('Margin Regular (Rp / %)')
                                    ->required()
                                    ->helperText('Angka ≤ 100 = Persen (%), > 100 = Rupiah (Rp).'),

                                TextInput::make('margin_gold')
                                    ->label('Margin Gold VIP (Rp / %)')
                                    ->required()
                                    ->helperText('Angka ≤ 100 = Persen (%), > 100 = Rupiah (Rp).'),

                                TextInput::make('margin_platinum')
                                    ->label('Margin Platinum VVIP (Rp / %)')
                                    ->required()
                                    ->helperText('Angka ≤ 100 = Persen (%), > 100 = Rupiah (Rp).'),

                                TextInput::make('margin_cash')
                                    ->label('Margin Cash (Rp / %)')
                                    ->required()
                                    ->helperText('Angka ≤ 100 = Persen (%), > 100 = Rupiah (Rp).'),

                                TextInput::make('service_fee_percent')
                                    ->label('Biaya Layanan (Rp / %)')
                                    ->required()
                                    ->helperText('Angka ≤ 100 = Persen (%), > 100 = Rupiah (Rp).'),

                                TextInput::make('note')
                                    ->label('Catatan / Keterangan')
                                    ->placeholder('Contoh: Tier Nominal <= Rp 5.000')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->defaultItems(5)
                            ->reorderable()
                            ->addActionLabel('Tambah Tier Margin Baru')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $rules = $data['tiered_margin_rules'] ?? [];
        foreach ($rules as &$rule) {
            $rule['max_amount'] = (float) str_replace(',', '.', (string) ($rule['max_amount'] ?? 0));
            $rule['margin_online'] = (float) str_replace(',', '.', (string) ($rule['margin_online'] ?? 0));
            $rule['margin_gold'] = (float) str_replace(',', '.', (string) ($rule['margin_gold'] ?? 0));
            $rule['margin_platinum'] = (float) str_replace(',', '.', (string) ($rule['margin_platinum'] ?? 0));
            $rule['margin_cash'] = (float) str_replace(',', '.', (string) ($rule['margin_cash'] ?? 0));
            $rule['service_fee_percent'] = (float) str_replace(',', '.', (string) ($rule['service_fee_percent'] ?? 0));
        }
        unset($rule);

        Setting::set('tiered_margin_rules', json_encode($rules));

        // Auto recalculate prices after saving tier settings
        Artisan::call('products:recalculate-prices');

        $this->form->fill([
            'tiered_margin_rules' => self::getTierRules(),
        ]);

        Notification::make()
            ->title('Pengaturan Margin Disimpan & Harga Diperbarui')
            ->body('Pengaturan tier margin baru berhasil disimpan dan seluruh harga produk (Regular, VIP, VVIP, Cash) telah otomatis dihitung ulang.')
            ->success()
            ->send();
    }
}
