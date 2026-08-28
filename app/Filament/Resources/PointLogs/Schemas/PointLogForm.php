<?php

namespace App\Filament\Resources\PointLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PointLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Member / User')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->label('Jumlah Poin')
                    ->numeric()
                    ->required(),
                Select::make('type')
                    ->label('Tipe Transaksi Poin')
                    ->options([
                        'earn' => 'Earn (Mendapatkan Poin Belanja)',
                        'spend' => 'Spend (Menggunakan Poin)',
                        'referral_bonus' => 'Referral Bonus (Undang Teman)',
                        'expire' => 'Expire (Kedaluwarsa)',
                    ])
                    ->required(),
                TextInput::make('description')
                    ->label('Keterangan / Alasan')
                    ->nullable(),
                DateTimePicker::make('expired_at')
                    ->label('Kedaluwarsa Pada')
                    ->nullable(),
                Toggle::make('is_expired')
                    ->label('Sudah Kedaluwarsa')
                    ->default(false),
            ]);
    }
}
