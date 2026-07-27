<?php

namespace App\Http\Requests;

class UpdateCouponRequest extends StoreCouponRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $coupon = $this->route('coupon');
        $couponId = is_object($coupon) ? $coupon->id : $coupon;

        $rules['code'] = 'required|string|max:50|unique:coupons,code,' . $couponId;

        return $rules;
    }
}
