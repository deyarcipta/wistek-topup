<?php

namespace App\Filament\Widgets;

use App\Services\DigiflazzService;
use App\Services\PaymentGatewayManager;
use App\Services\WhatsappService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-header-widget';

    public int|string|array $columnSpan = 'full';

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    public function getViewData(): array
    {
        $user = Auth::user();

        $digiflazz = new DigiflazzService;
        $dfStatus = $digiflazz->getStatusDetails();

        $waService = new WhatsappService;
        $waStatus = $waService->getStatusDetails();

        $paymentManager = new PaymentGatewayManager;
        $gatewayName = strtoupper($paymentManager->getActiveGateway());
        $gatewayFullName = $paymentManager->getActiveGatewayName();

        return [
            'user' => $user,
            'dfStatus' => $dfStatus,
            'waStatus' => $waStatus,
            'gatewayName' => $gatewayName,
            'gatewayFullName' => $gatewayFullName,
            'todayDate' => now()->translatedFormat('l, d F Y'),
        ];
    }
}
