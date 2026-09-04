<?php

namespace App\Filament\Widgets;

use App\Services\DigiflazzService;
use App\Services\PaymentGatewayManager;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-header-widget';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();

        $digiflazz = new DigiflazzService;
        $dfStatus = $digiflazz->getStatusDetails();

        $paymentManager = new PaymentGatewayManager;
        $gatewayName = strtoupper($paymentManager->getActiveGateway());
        $gatewayFullName = $paymentManager->getActiveGatewayName();

        return [
            'user' => $user,
            'dfStatus' => $dfStatus,
            'gatewayName' => $gatewayName,
            'gatewayFullName' => $gatewayFullName,
            'todayDate' => now()->translatedFormat('l, d F Y'),
        ];
    }
}
