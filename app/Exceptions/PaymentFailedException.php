<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct(
        protected string $gateway,
        string $reason,
        protected ?string $transactionId = null
    ) {
        parent::__construct("Payment via {$gateway} failed: {$reason}");
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
