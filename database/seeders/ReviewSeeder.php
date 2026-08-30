<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'name' => 'Rian Gamers',
                'role_or_title' => 'Mobile Legends Player',
                'rating' => 5,
                'comment' => 'Gila! Cuma butuh waktu 5 detik diamond Mobile Legends langsung masuk ke akun saya. Biaya admin QRIS-nya juga murah banget dibandingkan toko sebelah.',
                'is_visible' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Amelia MLBB',
                'role_or_title' => 'Gamer / Streamer',
                'rating' => 5,
                'comment' => 'Baru pertama kali coba topup di Wistek langsung ketagihan. Prosesnya instan dan CS-nya ramah banget pas nanya konfirmasi via WA. Recommended!',
                'is_visible' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Reza Free Fire',
                'role_or_title' => 'Survivor',
                'rating' => 5,
                'comment' => 'Top up murah paling terpercaya! Saya selalu langganan di sini buat beli membership mingguan Free Fire, tidak pernah bermasalah dan selalu kilat.',
                'is_visible' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($reviews as $data) {
            Review::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
