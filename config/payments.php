<?php

use App\Services\PaymentGateways\BkashGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Map of payment method => gateway implementation class.
    | Each gateway must implement App\Services\Contracts\PaymentGatewayInterface.
    |
    | To add a new gateway:
    |   1. Create a class implementing PaymentGatewayInterface
    |   2. Add its credentials to config/services.php
    |   3. Register it here
    |
    */

    'gateways' => [
        'bkash' => BkashGateway::class,
        // 'nagad' => NagadGateway::class,
        // 'sslcommerz' => SslcommerzGateway::class,
    ],

];
