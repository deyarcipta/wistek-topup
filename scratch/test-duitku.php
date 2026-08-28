<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\DuitkuService;
use Illuminate\Contracts\Console\Kernel;

$duitku = new DuitkuService;

$invoice = 'TEST-'.time();
$productName = '86 Diamonds';
$price = 20000;
$method = 'BCAVA';
$phone = '081234567890';

echo "Testing Duitku createTransaction...\n";
echo "Invoice: $invoice\n";
echo "Product: $productName\n";
echo "Price: $price\n";
echo "Method: $method\n\n";

$response = $duitku->createTransaction($invoice, $productName, $price, $method, $phone);

print_r($response);
