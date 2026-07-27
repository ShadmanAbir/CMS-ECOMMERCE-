<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use App\Services\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Gateway class bindings resolved from the container.
     *
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    protected array $gateways = [];

    public function __construct()
    {
        // Gateway map: method => implementation class
        // New gateways only need to be added here + bound in the container.
        $this->gateways = config('payments.gateways', []);
    }

    public function processPayment(Order $order, string $method): array
    {
        if ($method === 'cod') {
            return $this->processCOD($order);
        }

        $gateway = $this->resolveGateway($method);

        return $gateway->createPayment($order);
    }

    public function handlePaymentCallback(array $data, string $method): array
    {
        $gateway = $this->resolveGateway($method);

        return $gateway->handleCallback($data);
    }

    protected function processCOD(Order $order): array
    {
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        return ['success' => true];
    }

    protected function resolveGateway(string $method): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$method])) {
            throw new InvalidArgumentException("Payment method [{$method}] is not supported.");
        }

        $gateway = app($this->gateways[$method]);

        if (!$gateway instanceof PaymentGatewayInterface) {
            throw new InvalidArgumentException(
                "Gateway for [{$method}] must implement PaymentGatewayInterface."
            );
        }

        return $gateway;
    }
}
