<?php

namespace App\Services\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Create a payment for the given order.
     *
     * @return array{success: bool, url?: string, error?: string}
     */
    public function createPayment(Order $order): array;

    /**
     * Handle the gateway callback/notification.
     *
     * @param  array  $data  Raw callback payload from the gateway.
     * @return array{success: bool, message?: string, error?: string, trxID?: string}
     */
    public function handleCallback(array $data): array;
}
