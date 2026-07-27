<?php

namespace App\Exceptions;

use Exception;

class OrderCancellationException extends Exception
{
    public static function unauthorized(): self
    {
        return new self('You are not authorized to cancel this order.');
    }

    public static function invalidStatus(string $currentStatus): self
    {
        return new self("Only pending or processing orders can be cancelled. Current status: {$currentStatus}.");
    }
}
