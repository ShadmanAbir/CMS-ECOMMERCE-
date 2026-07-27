<?php

namespace App\Exceptions;

use Exception;

class OrderStatusException extends Exception
{
    public static function invalidTransition(string $from, string $to): self
    {
        return new self("Cannot transition order from '{$from}' to '{$to}'.");
    }

    public static function unknownStatus(string $status): self
    {
        return new self("Unknown order status '{$status}'.");
    }
}
