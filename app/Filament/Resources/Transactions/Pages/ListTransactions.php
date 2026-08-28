<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Http\Controllers\CallbackController;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DigiflazzService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('orderCash')
                ->label('Order Cash (Manual)')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->form([
                    Select::make('user_id')
                        ->label('Pilih Member (Opsional)')
                        ->options(
                            User::where('role', 'member')
                                ->get()
                                ->mapWithKeys(fn ($user) => [
                                    $user->id => "{$user->name} ({$user->email} - {$user->phone})",
                                ])
                        )
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $user = User::find($state);
                            if ($user && $user->phone) {
                                $set('wa_notification', $user->phone);
                            }
                        }),
                    Select::make('product_id')
                        ->label('Pilih Produk')
                        ->options(
                            Product::where('status', true)
                                ->with('category')
                                ->get()
                                ->mapWithKeys(fn ($product) => [
                                    $product->id => "{$product->category?->name} - {$product->name} (Rp ".number_format($product->price_sell, 0, ',', '.').')',
                                ])
                        )
                        ->searchable()
                        ->reactive()
                        ->required(),
                    TextInput::make('user_id_ml')
                        ->label('User ID (Mobile Legends)')
                        ->placeholder('Contoh: 12345678')
                        ->required()
                        ->visible(function (callable $get) {
                            $productId = $get('product_id');
                            if (! $productId) {
                                return false;
                            }
                            $product = Product::with('category')->find($productId);
                            $categoryName = $product?->category?->name ?? '';
                            $categorySlug = $product?->category?->slug ?? '';

                            return Str::contains(strtolower($categoryName), 'mobile legends')
                                || Str::contains(strtolower($categorySlug), 'mobile-legends');
                        }),
                    TextInput::make('zone_id_ml')
                        ->label('Zone ID (Mobile Legends)')
                        ->placeholder('Contoh: 1234')
                        ->required()
                        ->visible(function (callable $get) {
                            $productId = $get('product_id');
                            if (! $productId) {
                                return false;
                            }
                            $product = Product::with('category')->find($productId);
                            $categoryName = $product?->category?->name ?? '';
                            $categorySlug = $product?->category?->slug ?? '';

                            return Str::contains(strtolower($categoryName), 'mobile legends')
                                || Str::contains(strtolower($categorySlug), 'mobile-legends');
                        }),
                    TextInput::make('target_no_game')
                        ->label('ID Akun / Target')
                        ->placeholder('Contoh: 523087265')
                        ->required()
                        ->visible(function (callable $get) {
                            $productId = $get('product_id');
                            if (! $productId) {
                                return false;
                            }
                            $product = Product::with('category')->find($productId);
                            $categoryName = $product?->category?->name ?? '';
                            $categorySlug = $product?->category?->slug ?? '';
                            $isML = Str::contains(strtolower($categoryName), 'mobile legends')
                                || Str::contains(strtolower($categorySlug), 'mobile-legends');

                            return $product && ! $isML && $product->category?->type === 'game';
                        }),
                    TextInput::make('target_no_phone')
                        ->label('Nomor Handphone / Tujuan')
                        ->placeholder('Contoh: 081234567890')
                        ->required()
                        ->visible(function (callable $get) {
                            $productId = $get('product_id');
                            if (! $productId) {
                                return false;
                            }
                            $product = Product::with('category')->find($productId);

                            return $product && in_array($product->category?->type, ['pulsa', 'emoney']);
                        }),
                    TextInput::make('target_no_fallback')
                        ->label('Target / Tujuan')
                        ->placeholder('Masukkan tujuan')
                        ->required()
                        ->visible(function (callable $get) {
                            $productId = $get('product_id');
                            if (! $productId) {
                                return false;
                            }
                            $product = Product::with('category')->find($productId);
                            if (! $product) {
                                return false;
                            }
                            $categoryName = $product?->category?->name ?? '';
                            $categorySlug = $product?->category?->slug ?? '';
                            $isML = Str::contains(strtolower($categoryName), 'mobile legends')
                                || Str::contains(strtolower($categorySlug), 'mobile-legends');

                            return ! $isML
                                && $product->category?->type !== 'game'
                                && ! in_array($product->category?->type, ['pulsa', 'emoney']);
                        }),
                    TextInput::make('wa_notification')
                        ->label('Nomor WhatsApp untuk Notifikasi')
                        ->placeholder('Contoh: 08123456789')
                        ->required()
                        ->helperText('Notifikasi WhatsApp perihal transaksi akan dikirim ke nomor ini.'),
                ])
                ->action(function (array $data) {
                    $product = Product::findOrFail($data['product_id']);
                    $user = $data['user_id'] ? User::find($data['user_id']) : null;

                    // Determine the correct target no based on product category
                    $targetNo = '';
                    $categoryName = $product->category?->name ?? '';
                    $categorySlug = $product->category?->slug ?? '';
                    $categoryType = $product->category?->type;
                    $isML = Str::contains(strtolower($categoryName), 'mobile legends')
                        || Str::contains(strtolower($categorySlug), 'mobile-legends');

                    if ($isML) {
                        $targetNo = $data['user_id_ml'].' ('.$data['zone_id_ml'].')';
                    } elseif ($categoryType === 'game') {
                        $targetNo = $data['target_no_game'];
                    } elseif (in_array($categoryType, ['pulsa', 'emoney'])) {
                        $targetNo = $data['target_no_phone'];
                    } else {
                        $targetNo = $data['target_no_fallback'];
                    }

                    $invoice = 'WSTK-CASH-'.strtoupper(uniqid());

                    $transaction = Transaction::create([
                        'user_id' => $user?->id,
                        'invoice' => $invoice,
                        'category_name' => $product->category?->name ?? 'Uncategorized',
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'target_no' => $targetNo,
                        'price' => $product->price_sell,
                        'payment_method' => 'CASH',
                        'payment_status' => 'unpaid',
                        'topup_status' => 'pending',
                        'customer_phone' => $data['wa_notification'],
                    ]);

                    // Trigger Digiflazz order automatically via simulatePaid
                    $controller = new CallbackController;
                    $response = $controller->simulatePaid($invoice, new DigiflazzService);

                    Notification::make()
                        ->title('Pesanan Cash Sukses')
                        ->body("Pesanan untuk {$product->name} telah diproses.<br>".strip_tags($response))
                        ->success()
                        ->send();
                }),
        ];
    }
}
