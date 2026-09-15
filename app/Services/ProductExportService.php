<?php

namespace App\Services;

use App\Models\Product;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class ProductExportService
{
    /**
     * Generate a professionally formatted Excel (XLSX) file containing all products.
     * All tier boundary constraints (Margins & Service Fees) are evaluated strictly against Harga Modal.
     *
     * @return string Absolute file path to the generated XLSX file.
     */
    public static function exportToXlsx(): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'products_export_').'.xlsx';

        $writer = new Writer;
        $writer->openToFile($tempPath);

        // Configure Column Widths for a clean, non-truncated layout
        $options = $writer->getOptions();
        $options->setColumnWidth(8, 1);    // No
        $options->setColumnWidth(16, 2);   // SKU
        $options->setColumnWidth(22, 3);   // Kategori
        $options->setColumnWidth(38, 4);   // Nama Produk
        $options->setColumnWidth(18, 5);   // Harga Modal
        $options->setColumnWidth(20, 6);   // Harga Jual Publik
        $options->setColumnWidth(24, 7);   // Harga Jual VIP (Gold)
        $options->setColumnWidth(26, 8);   // Harga Jual VVIP (Platinum)
        $options->setColumnWidth(18, 9);   // Untung Publik
        $options->setColumnWidth(18, 10);  // Untung Gold
        $options->setColumnWidth(18, 11);  // Untung Platinum

        // Define Reusable Styles
        $titleStyle = (new Style)
            ->setFontBold()
            ->setFontSize(14)
            ->setFontName('Segoe UI')
            ->setFontColor('0F172A');

        $metaStyle = (new Style)
            ->setFontItalic()
            ->setFontSize(10)
            ->setFontName('Segoe UI')
            ->setFontColor('64748B');

        $headerStyle = (new Style)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontName('Segoe UI')
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('1E293B')
            ->setCellAlignment(CellAlignment::CENTER);

        // Data Styles (White & Zebra backgrounds)
        $centerWhite = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setCellAlignment(CellAlignment::CENTER);
        $centerZebra = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setBackgroundColor('F8FAFC')->setCellAlignment(CellAlignment::CENTER);

        $leftWhite = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setCellAlignment(CellAlignment::LEFT);
        $leftZebra = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setBackgroundColor('F8FAFC')->setCellAlignment(CellAlignment::LEFT);

        $numWhite = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setCellAlignment(CellAlignment::RIGHT)->setFormat('Rp #,##0');
        $numZebra = (new Style)->setFontName('Segoe UI')->setFontSize(10)->setBackgroundColor('F8FAFC')->setCellAlignment(CellAlignment::RIGHT)->setFormat('Rp #,##0');

        $products = Product::with(['category', 'subCategory'])
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        // 1. Report Header Banner
        $writer->addRow(Row::fromValues(['LAPORAN KATALOG & ESTIMASI KEUNTUNGAN PRODUK WISTEK TOPUP'], $titleStyle));
        $writer->addRow(Row::fromValues(['Tanggal Export: '.date('d-m-Y H:i').' WIB | Total Produk: '.$products->count().' Item'], $metaStyle));
        $writer->addRow(Row::fromValues([''])); // Blank row

        // 2. Table Headers
        $headers = [
            'No',
            'SKU',
            'Kategori',
            'Nama Produk',
            'Harga Modal',
            'Harga Jual Publik',
            'Harga Jual VIP (Gold)',
            'Harga Jual VVIP (Platinum)',
            'Untung Publik',
            'Untung Gold',
            'Untung Platinum',
        ];
        $writer->addRow(Row::fromValues($headers, $headerStyle));

        // 3. Data Rows
        $no = 1;
        foreach ($products as $index => $product) {
            $isEven = ($index % 2 === 0);

            $cStyle = $isEven ? $centerZebra : $centerWhite;
            $lStyle = $isEven ? $leftZebra : $leftWhite;
            $nStyle = $isEven ? $numZebra : $numWhite;

            // Langkah A: Data Utama
            $cost = (float) $product->price_cost;

            // Langkah B: Hitung Harga Katalog = Harga Modal + (Harga Modal * Margin %)
            $sellPublik = (float) DigiflazzService::calculatePriceSell($cost);
            $sellGold = ($product->price_gold !== null && (float) $product->price_gold > 0)
                ? (float) $product->price_gold
                : (float) DigiflazzService::calculateGoldPrice($cost);
            $sellPlatinum = ($product->price_platinum !== null && (float) $product->price_platinum > 0)
                ? (float) $product->price_platinum
                : (float) DigiflazzService::calculatePlatinumPrice($cost);

            // Langkah C: Total Bayar Konsumen = Harga Katalog + Biaya Layanan Flat (Strictly evaluated against Harga Modal)
            $serviceFeeFlat = self::calculateServiceFee($cost);

            $totalPublik = $sellPublik + $serviceFeeFlat;
            $totalGold = $sellGold + $serviceFeeFlat;
            $totalPlatinum = $sellPlatinum + $serviceFeeFlat;

            // Langkah D: Potongan TriPay = (Total Bayar Konsumen * 0.7%) + Rp750
            $triPayCutPublik = ($totalPublik * 0.007) + 750;
            $triPayCutGold = ($totalGold * 0.007) + 750;
            $triPayCutPlatinum = ($totalPlatinum * 0.007) + 750;

            // Langkah E: Untung Bersih = Total Bayar Konsumen - Potongan TriPay - Harga Modal
            $untungPublik = $totalPublik - $triPayCutPublik - $cost;
            $untungGold = $totalGold - $triPayCutGold - $cost;
            $untungPlatinum = $totalPlatinum - $triPayCutPlatinum - $cost;

            $rowCells = [
                Cell::fromValue($no++, $cStyle),
                Cell::fromValue((string) ($product->sku ?? ''), $cStyle),
                Cell::fromValue($product->category ? $product->category->name : '-', $lStyle),
                Cell::fromValue((string) $product->name, $lStyle),
                Cell::fromValue(round($cost), $nStyle),
                Cell::fromValue(round($sellPublik), $nStyle),
                Cell::fromValue(round($sellGold), $nStyle),
                Cell::fromValue(round($sellPlatinum), $nStyle),
                Cell::fromValue(round($untungPublik), $nStyle),
                Cell::fromValue(round($untungGold), $nStyle),
                Cell::fromValue(round($untungPlatinum), $nStyle),
            ];

            $writer->addRow(new Row($rowCells));
        }

        $writer->close();

        return $tempPath;
    }

    /**
     * Calculate service fee for a given cost price based on dynamic margin tier rules.
     * Evaluates strictly against Harga Modal.
     */
    public static function calculateServiceFee(float $cost): float
    {
        $rule = TripayService::getServiceFeeRule($cost);

        if (($rule['percent'] ?? 0) > 0) {
            return (float) round(($cost * $rule['percent']) / 100);
        }

        return (float) ($rule['flat'] ?? 0);
    }
}
