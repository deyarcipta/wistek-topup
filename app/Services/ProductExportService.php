<?php

namespace App\Services;

use App\Models\Product;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ProductExportService
{
    /**
     * Generate an Excel (XLSX) file containing all products with price and profit calculations.
     *
     * @return string Absolute file path to the generated XLSX file.
     */
    public static function exportToXlsx(): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'products_export_').'.xlsx';

        $writer = new Writer;
        $writer->openToFile($tempPath);

        // Header row
        $headers = [
            'No',
            'SKU',
            'Kategori',
            'Nama Produk',
            'Harga Modal',
            'Harga Jual Publik',
            'Harga Jual VIP (Gold)',
            'Harga Jual VVIP (Platinum)',
            'Potongan QRIS (0.7% + 750)',
            'Untung Publik',
            'Untung Gold',
            'Untung Platinum',
        ];

        $writer->addRow(Row::fromValues($headers));

        $products = Product::with(['category', 'subCategory'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $no = 1;
        foreach ($products as $product) {
            $cost = (float) $product->price_cost;
            $sellPublik = (float) $product->price_sell;

            $sellGold = ($product->price_gold !== null && (float) $product->price_gold > 0)
                ? (float) $product->price_gold
                : (float) DigiflazzService::calculateGoldPrice($cost);

            $sellPlatinum = ($product->price_platinum !== null && (float) $product->price_platinum > 0)
                ? (float) $product->price_platinum
                : (float) DigiflazzService::calculatePlatinumPrice($cost);

            // Compute Service Fees
            $pubFee = self::calculateServiceFee($sellPublik);
            $goldFee = self::calculateServiceFee($sellGold);
            $platFee = self::calculateServiceFee($sellPlatinum);

            // Total Pembayaran (Harga Jual + Biaya Layanan)
            $totalPublik = $sellPublik + $pubFee;
            $totalGold = $sellGold + $goldFee;
            $totalPlatinum = $sellPlatinum + $platFee;

            // Potongan QRIS (0.7% + 750)
            $qrisCutPublik = ($totalPublik * 0.007) + 750;
            $qrisCutGold = ($totalGold * 0.007) + 750;
            $qrisCutPlatinum = ($totalPlatinum * 0.007) + 750;

            // Untung (Harga Jual + Biaya Layanan - Potongan QRIS 0.7% + 750 - Harga Modal)
            $untungPublik = $totalPublik - $qrisCutPublik - $cost;
            $untungGold = $totalGold - $qrisCutGold - $cost;
            $untungPlatinum = $totalPlatinum - $qrisCutPlatinum - $cost;

            $rowValues = [
                $no++,
                (string) ($product->sku ?? ''),
                $product->category ? $product->category->name : '-',
                (string) $product->name,
                round($cost, 2),
                round($sellPublik, 2),
                round($sellGold, 2),
                round($sellPlatinum, 2),
                round($qrisCutPublik, 2),
                round($untungPublik, 2),
                round($untungGold, 2),
                round($untungPlatinum, 2),
            ];

            $writer->addRow(Row::fromValues($rowValues));
        }

        $writer->close();

        return $tempPath;
    }

    /**
     * Calculate service fee for a given selling price based on dynamic margin tier rules.
     */
    public static function calculateServiceFee(float $amount): float
    {
        $rule = TripayService::getServiceFeeRule($amount);

        if (($rule['percent'] ?? 0) > 0) {
            return (float) round(($amount * $rule['percent']) / 100);
        }

        return (float) ($rule['flat'] ?? 0);
    }
}
