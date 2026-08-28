<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Admin User
        User::updateOrCreate(
            ['email' => 'admin@wistek.com'],
            [
                'name' => 'Admin Wistek',
                'password' => bcrypt('password'),
            ]
        );

        // Seed Categories
        $ml = Category::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'thumbnail' => 'https://img.df.gameloop.com/g/1000/100021/logo.png',
            'type' => 'game',
            'status' => true,
        ]);

        $ff = Category::create([
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'thumbnail' => 'https://upload.wikimedia.org/wikipedia/id/c/cb/Logo_Free_Fire.png',
            'type' => 'game',
            'status' => true,
        ]);

        $tssel = Category::create([
            'name' => 'Telkomsel Pulsa',
            'slug' => 'telkomsel-pulsa',
            'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Telkomsel_2021_icon.svg',
            'type' => 'pulsa',
            'status' => true,
        ]);

        $gopay = Category::create([
            'name' => 'GoPay E-Money',
            'slug' => 'gopay',
            'thumbnail' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
            'type' => 'emoney',
            'status' => true,
        ]);

        // Seed Products for MLBB
        $mlProducts = [
            ['name' => '86 Diamonds', 'sku' => 'MLBB86', 'price_cost' => 18000, 'price_sell' => 20000],
            ['name' => '172 Diamonds', 'sku' => 'MLBB172', 'price_cost' => 35000, 'price_sell' => 39000],
            ['name' => '257 Diamonds', 'sku' => 'MLBB257', 'price_cost' => 52000, 'price_sell' => 58000],
            ['name' => '706 Diamonds', 'sku' => 'MLBB706', 'price_cost' => 140000, 'price_sell' => 155000],
        ];

        foreach ($mlProducts as $p) {
            Product::create(array_merge($p, ['category_id' => $ml->id, 'status' => true]));
        }

        // Seed Products for Free Fire
        $ffProducts = [
            ['name' => '50 Diamonds', 'sku' => 'FF50', 'price_cost' => 7000, 'price_sell' => 8500],
            ['name' => '70 Diamonds', 'sku' => 'FF70', 'price_cost' => 9000, 'price_sell' => 10500],
            ['name' => '140 Diamonds', 'sku' => 'FF140', 'price_cost' => 18000, 'price_sell' => 20500],
            ['name' => '355 Diamonds', 'sku' => 'FF355', 'price_cost' => 45000, 'price_sell' => 51000],
        ];

        foreach ($ffProducts as $p) {
            Product::create(array_merge($p, ['category_id' => $ff->id, 'status' => true]));
        }

        // Seed Products for Telkomsel Pulsa
        $tsselProducts = [
            ['name' => 'Pulsa 5.000', 'sku' => 'H5', 'price_cost' => 5300, 'price_sell' => 6500],
            ['name' => 'Pulsa 10.000', 'sku' => 'H10', 'price_cost' => 10250, 'price_sell' => 11500],
            ['name' => 'Pulsa 20.000', 'sku' => 'H20', 'price_cost' => 19800, 'price_sell' => 21500],
            ['name' => 'Pulsa 50.000', 'sku' => 'H50', 'price_cost' => 49100, 'price_sell' => 51500],
        ];

        foreach ($tsselProducts as $p) {
            Product::create(array_merge($p, ['category_id' => $tssel->id, 'status' => true]));
        }

        // Seed Products for GoPay
        $gopayProducts = [
            ['name' => 'GoPay 10.000', 'sku' => 'GP10', 'price_cost' => 10100, 'price_sell' => 11500],
            ['name' => 'GoPay 20.000', 'sku' => 'GP20', 'price_cost' => 20100, 'price_sell' => 21500],
            ['name' => 'GoPay 50.000', 'sku' => 'GP50', 'price_cost' => 50100, 'price_sell' => 51500],
        ];

        foreach ($gopayProducts as $p) {
            Product::create(array_merge($p, ['category_id' => $gopay->id, 'status' => true]));
        }
    }
}
