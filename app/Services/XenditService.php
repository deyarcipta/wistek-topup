<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;

class XenditService
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $verificationToken;

    protected string $mode;

    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = Setting::get('xendit_secret_key', env('XENDIT_SECRET_KEY', ''));
        $this->publicKey = Setting::get('xendit_public_key', env('XENDIT_PUBLIC_KEY', ''));
        $this->verificationToken = Setting::get('xendit_verification_token', env('XENDIT_VERIFICATION_TOKEN', ''));
        $this->mode = Setting::get('xendit_mode', env('XENDIT_MODE', 'development'));
        $this->baseUrl = 'https://api.xendit.co/';
    }

    /**
     * Get list of standard payment channels available in Xendit
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        return [
            ['code' => 'QRIS', 'name' => 'QRIS (GoPay, OVO, Dana, ShopeePay)', 'fee_flat' => 0, 'fee_percent' => 0.7, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg'],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'fee_flat' => 4500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia_logo.svg'],
            ['code' => 'MANDIRIVA', 'name' => 'Mandiri Virtual Account', 'fee_flat' => 4500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg'],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'fee_flat' => 4500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg'],
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'fee_flat' => 4500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_2020.svg'],
            ['code' => 'PERMATAVA', 'name' => 'Permata Virtual Account', 'fee_flat' => 4500, 'fee_percent' => 0, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Permata_Bank_logo.svg'],
            ['code' => 'OVO', 'name' => 'OVO E-Wallet', 'fee_flat' => 0, 'fee_percent' => 1.5, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_blue.svg'],
            ['code' => 'DANA', 'name' => 'DANA E-Wallet', 'fee_flat' => 0, 'fee_percent' => 1.5, 'icon' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg'],
        ];
    }

    /**
     * Create Invoice on Xendit
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        if (empty($this->secretKey)) {
            throw new Exception('Xendit Secret Key belum diatur di Pengaturan API.');
        }

        $payload = [
            'external_id' => $invoice,
            'amount' => $amount,
            'description' => $productName,
            'customer' => [
                'given_names' => 'Pelanggan Wistek',
                'mobile_number' => $customerPhone ?: '+6281234567890',
            ],
            'success_redirect_url' => url('/'),
            'failure_redirect_url' => url('/'),
        ];

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl.'v2/invoices', $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_url' => $data['invoice_url'] ?? '',
                    'reference' => $data['id'] ?? $invoice,
                ];
            }

            $msg = $response->json()['message'] ?? $response->body();
            throw new Exception('Xendit Error: '.$msg);
        } catch (Exception $e) {
            logger()->error('Xendit createTransaction failed: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Validate Xendit webhook token header
     */
    public function validateCallbackToken(?string $tokenHeader): bool
    {
        if (empty($this->verificationToken)) {
            return true; // Bypass if not set
        }

        return hash_equals($this->verificationToken, (string) $tokenHeader);
    }

    /**
     * Get connection status details for Admin Panel
     */
    public function getStatusDetails(): array
    {
        if (empty($this->secretKey)) {
            return [
                'success' => false,
                'message' => 'Xendit Secret Key belum diisi',
            ];
        }

        return [
            'success' => true,
            'message' => 'Xendit Siap digunakan (Mode: '.ucfirst($this->mode).')',
        ];
    }
}
