<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'WISTEKGRAND1K',
                'type' => 'fixed',
                'value' => 1000,
                'min_purchase' => 10000,
                'max_uses' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'WISTEKMECHA2K',
                'type' => 'fixed',
                'value' => 2000,
                'min_purchase' => 20000,
                'max_uses' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'WISTEKNEW3K',
                'type' => 'fixed',
                'value' => 3000,
                'min_purchase' => 30000,
                'max_uses' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'WISTEKMEMBER5K',
                'type' => 'fixed',
                'value' => 5000,
                'min_purchase' => 50000,
                'max_uses' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $v) {
            Voucher::updateOrCreate(['code' => $v['code']], $v);
        }
    }
}
