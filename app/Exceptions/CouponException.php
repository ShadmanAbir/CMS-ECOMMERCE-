<?php

namespace App\Exceptions;

use Exception;

class CouponException extends Exception
{
    public static function notFound(): self
    {
        return new self('This coupon code is not valid.');
    }

    public static function inactive(): self
    {
        return new self('This coupon is no longer active.');
    }

    public static function expired(): self
    {
        return new self('This coupon has expired.');
    }

    public static function notStarted(): self
    {
        return new self('This coupon is not active yet.');
    }

    public static function minOrder(float $amount): self
    {
        return new self('This coupon requires a minimum order of ৳' . number_format($amount, 2) . '.');
    }

    public static function limitReached(): self
    {
        return new self('This coupon has reached its usage limit.');
    }

    public static function userLimitReached(): self
    {
        return new self('You have already used this coupon the maximum number of times.');
    }
}
