<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->boot();

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

$u = Setting::get('digiflazz_username', env('DIGIFLAZZ_USERNAME'));
$k = Setting::get('digiflazz_api_key', env('DIGIFLAZZ_API_KEY'));
$sign = md5($u.$k.'depo');

try {
    $response = Http::post('https://api.digiflazz.com/v1/cek-saldo', [
        'cmd' => 'depo',
        'username' => $u,
        'sign' => $sign,
    ]);

    echo 'Status Code: '.$response->status()."\n";
    print_r($response->json());
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
