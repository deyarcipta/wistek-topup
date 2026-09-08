<?php

$urls = [
    'bca.png' => 'https://tripay.co.id/upload/payment-channel/YV48pLg2111580805096.png',
    'mandiri.png' => 'https://tripay.co.id/upload/payment-channel/S9J411q9vV1580807727.png',
    'bni.png' => 'https://tripay.co.id/upload/payment-channel/J1T1aG8F131580806450.png',
    'bri.png' => 'https://tripay.co.id/upload/payment-channel/Z5z00yA7Wb1580807604.png',
    'permata.png' => 'https://tripay.co.id/upload/payment-channel/n4d8P372Wz1580807954.png',
    'cimb.png' => 'https://tripay.co.id/upload/payment-channel/M4P103yD561580807865.png',
    'bsi.png' => 'https://tripay.co.id/upload/payment-channel/BSI-1634547480.png',
    'maybank.png' => 'https://tripay.co.id/upload/payment-channel/8122Y26G8H1580807901.png',
    'qris.png' => 'https://tripay.co.id/upload/payment-channel/DmgT8aI5Wv1628100523.png',
    'alfamart.png' => 'https://tripay.co.id/upload/payment-channel/M3j388kE761580808200.png',
    'indomaret.png' => 'https://tripay.co.id/upload/payment-channel/uX1N9cWJ6X1580808240.png',
    'shopeepay.png' => 'https://tripay.co.id/upload/payment-channel/m1w6JqS8dK1606806954.png',
    'dana.png' => 'https://tripay.co.id/upload/payment-channel/L2G73945vW1606806915.png',
    'ovo.png' => 'https://tripay.co.id/upload/payment-channel/vL71n5v5mG1606806877.png',
    'gopay.png' => 'https://tripay.co.id/upload/payment-channel/GoPay.png',
    'linkaja.png' => 'https://tripay.co.id/upload/payment-channel/LinkAja.png',
    'akulaku.png' => 'https://tripay.co.id/upload/payment-channel/Akulaku.png',
    'doku.png' => 'https://tripay.co.id/upload/payment-channel/DOKU.png',
];

$dir = __DIR__.'/../public/images/payments';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

foreach ($urls as $filename => $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $content && strlen($content) > 100) {
        file_put_contents($dir.'/'.$filename, $content);
        echo "Downloaded {$filename} (".strlen($content)." bytes)\n";
    } else {
        echo "Failed {$filename} (Code: {$httpCode})\n";
    }
}
