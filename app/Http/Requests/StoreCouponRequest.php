<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Normalize the coupon code to uppercase before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code', ''))),
        ]);
    }

    /**
     * Prepare data for coupon creation/update.
     */
    public function couponData(): array
    {
        return [
            'code' => $this->input('code'),
            'type' => $this->input('type'),
            'value' => $this->input('value'),
            'min_order_amount' => $this->input('min_order_amount'),
            'max_discount_amount' => $this->input('max_discount_amount'),
            'usage_limit' => $this->input('usage_limit'),
            'per_user_limit' => $this->input('per_user_limit'),
            'starts_at' => $this->input('starts_at'),
            'expires_at' => $this->input('expires_at'),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
