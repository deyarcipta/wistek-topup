<?php

namespace App\Services;

use App\Models\Setting;

class PaymentGatewayManager
{
    protected string $activeGateway;

    public function __construct()
    {
        $this->activeGateway = Setting::get('active_payment_gateway', 'tripay');
    }

    /**
     * Get active payment gateway key ('tripay', 'midtrans')
     */
    public function getActiveGateway(): string
    {
        return $this->activeGateway;
    }

    /**
     * Get Human-readable active payment gateway name
     */
    public function getActiveGatewayName(): string
    {
        return match ($this->activeGateway) {
            'midtrans' => 'Midtrans Payment Gateway',
            default => 'Tripay Payment Gateway',
        };
    }

    /**
     * Get instance of active payment gateway driver
     */
    public function getDriver(): object
    {
        return match ($this->activeGateway) {
            'midtrans' => app(MidtransService::class),
            default => app(TripayService::class),
        };
    }

    /**
     * Get payment channels from the active payment gateway
     */
    public function getPaymentChannels(int $amount = 10000): array
    {
        $driver = $this->getDriver();

        return $driver->getPaymentChannels($amount);
    }

    /**
     * Create transaction using active payment gateway
     */
    public function createTransaction(string $invoice, string $productName, int $amount, ?string $customerPhone = null, ?string $paymentMethod = null): array
    {
        $driver = $this->getDriver();

        return $driver->createTransaction($invoice, $productName, $amount, $customerPhone, $paymentMethod);
    }

    /**
     * Get status details for active gateway
     */
    public function getStatusDetails(): array
    {
        $driver = $this->getDriver();

        return $driver->getStatusDetails();
    }

    /**
     * Check transaction status directly via active driver API
     */
    public function checkTransactionStatus(string $invoice, ?string $reference = null): array
    {
        $driver = $this->getDriver();
        if (method_exists($driver, 'checkTransactionStatus')) {
            return $driver->checkTransactionStatus($reference ?: $invoice);
        }

        return ['success' => false, 'message' => 'Driver status check not supported'];
    }
}
