<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        protected string $productName,
        protected int $available,
        protected ?string $variantName = null
    ) {
        $detail = $variantName ? "{$productName} ({$variantName})" : $productName;
        parent::__construct("Insufficient stock for {$detail}. Available: {$available}");
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }
}
