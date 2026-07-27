<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:cod,bkash,nagad,sslcommerz',
            'address_id' => 'nullable|exists:customer_addresses,id',
            'address' => 'required_without:address_id|string|max:500',
            'city' => 'required_without:address_id|string|max:100',
            'division' => 'required_without:address_id|string|max:100',
            'district' => 'required_without:address_id|string|max:100',
            'thana' => 'required_without:address_id|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Verify the address belongs to the authenticated user.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $addressId = $this->input('address_id');

            if ($addressId) {
                $belongsToUser = \App\Models\CustomerAddress::where('id', $addressId)
                    ->where('user_id', auth()->id())
                    ->exists();

                if (!$belongsToUser) {
                    $validator->errors()->add('address_id', 'Invalid address selected.');
                }
            }
        });
    }
}
