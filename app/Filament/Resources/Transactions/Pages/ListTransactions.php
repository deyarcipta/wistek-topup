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
                        ->searchable(),
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
                        ->required(),
                    TextInput::make('target_no')
                        ->label('Nomor Target / Tujuan')
                        ->placeholder('Contoh: ID Game atau Nomor HP')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $product = Product::findOrFail($data['product_id']);
                    $user = $data['user_id'] ? User::find($data['user_id']) : null;

                    $invoice = 'WSTK-CASH-'.strtoupper(uniqid());

                    $transaction = Transaction::create([
                        'user_id' => $user?->id,
                        'invoice' => $invoice,
                        'category_name' => $product->category?->name ?? 'Uncategorized',
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'target_no' => $data['target_no'],
                        'price' => $product->price_sell,
                        'payment_method' => 'CASH',
                        'payment_status' => 'unpaid',
                        'topup_status' => 'pending',
                        'customer_phone' => $user?->phone,
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
