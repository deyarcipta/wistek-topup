<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('WhatsApp')
                    ->unique(ignoreRecord: true)
                    ->nullable(),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('points_balance')
                    ->label('Saldo Poin')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Select::make('tier_level')
                    ->label('Level / Tier Member')
                    ->options([
                        'regular' => 'Regular Member',
                        'gold' => 'Gold Member (VIP)',
                        'platinum' => 'Platinum Member (VVIP)',
                    ])
                    ->default('regular')
                    ->required()
                    ->helperText('Level member menentukan potongan harga khusus saat berbelanja di website.'),
                TextInput::make('referral_code')
                    ->label('Kode Referral')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Otomatis dibuat saat pendaftaran'),
            ]);
    }
}
