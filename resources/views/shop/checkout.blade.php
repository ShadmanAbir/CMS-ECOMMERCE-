@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@php
    $pgText = theme('ui.page_text');
    $cardText = theme('ui.card_text');
    $cardBg = theme('ui.card_bg');
    $cardBorder = theme('ui.card_border');
    $muted = theme('ui.muted_text');
    $muted2 = theme('ui.muted_text_2');
    $muted3 = theme('ui.muted_text_3');
    $inputBg = theme('ui.input_bg');
    $inputBorder = theme('ui.input_border');
    $inputText = theme('ui.input_text');
    $inputPh = theme('ui.input_placeholder');
    $divider = theme('ui.divider');
@endphp
<div class="container mx-auto px-4 py-8">
    <h1 class="font-heading text-3xl md:text-4xl {{ $pgText }} mb-8 tracking-wide">Checkout</h1>

    <form id="checkoutForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Checkout Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Address -->
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Shipping Address</h2>
                
                @if($addresses->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm {{ $muted }} mb-2">Select Saved Address</label>
                    <select id="addressSelect" name="address_id" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} focus:outline-none focus:border-accent" onchange="toggleAddressFields()">
                        <option value="">-- Choose an address --</option>
                        @foreach($addresses as $address)
                        <option value="{{ $address->id }}" {{ $defaultAddress && $defaultAddress->id == $address->id ? 'selected' : '' }}>
                            {{ $address->full_address }}
                        </option>
                        @endforeach
                        <option value="new">+ Add New Address</option>
                    </select>
                </div>
                @endif

                <div id="newAddressFields" class="{{ $addresses->count() == 0 ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm {{ $muted }} mb-2">Division</label>
                            <input type="text" name="division" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="e.g., Dhaka">
                        </div>
                        <div>
                            <label class="block text-sm {{ $muted }} mb-2">District</label>
                            <input type="text" name="district" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="e.g., Dhaka">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm {{ $muted }} mb-2">Thana/Upazila</label>
                            <input type="text" name="thana" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="e.g., Mirpur">
                        </div>
                        <div>
                            <label class="block text-sm {{ $muted }} mb-2">City</label>
                            <input type="text" name="city" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="e.g., Dhaka">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm {{ $muted }} mb-2">Full Address</label>
                        <textarea name="address" rows="3" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="House no, Road no, Area..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm {{ $muted }} mb-2">Postal Code (Optional)</label>
                        <input type="text" name="postal_code" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="e.g., 1216">
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Payment Method</h2>
                
                <div class="space-y-3">
                    <label class="flex items-center p-4 border {{ $cardBorder }} cursor-pointer hover:border-accent transition payment-option">
                        <input type="radio" name="payment_method" value="cod" checked class="h-4 w-4 text-accent focus:ring-accent">
                        <div class="ml-3">
                            <span class="font-medium {{ $cardText }}">Cash on Delivery</span>
                            <p class="text-sm {{ $muted2 }}">Pay when you receive your order</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border {{ $cardBorder }} cursor-pointer hover:border-accent transition payment-option">
                        <input type="radio" name="payment_method" value="bkash" class="h-4 w-4 text-accent focus:ring-accent">
                        <div class="ml-3">
                            <span class="font-medium {{ $cardText }}">bKash</span>
                            <p class="text-sm {{ $muted2 }}">Pay with bKash mobile wallet</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border {{ $cardBorder }} cursor-pointer hover:border-accent transition payment-option">
                        <input type="radio" name="payment_method" value="nagad" class="h-4 w-4 text-accent focus:ring-accent">
                        <div class="ml-3">
                            <span class="font-medium {{ $cardText }}">Nagad</span>
                            <p class="text-sm {{ $muted2 }}">Pay with Nagad mobile wallet</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border {{ $cardBorder }} cursor-pointer hover:border-accent transition payment-option">
                        <input type="radio" name="payment_method" value="sslcommerz" class="h-4 w-4 text-accent focus:ring-accent">
                        <div class="ml-3">
                            <span class="font-medium {{ $cardText }}">Credit/Debit Card</span>
                            <p class="text-sm {{ $muted2 }}">Pay with Visa, Mastercard, or other cards</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Order Notes (Optional)</h2>
                <textarea name="notes" rows="3" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="Any special instructions for your order..."></textarea>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6 sticky top-24">
                <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Order Summary</h2>

                <!-- Cart Items -->
                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    @foreach($cartItems as $item)
                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 flex-shrink-0 {{ $inputBg }}">
                            @if($item->product->images->count() > 0)
                                <img src="{{ storage_asset($item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                            @else
                                <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium {{ $cardText }} truncate">{{ $item->product->name }}</p>
                            <p class="text-xs {{ $muted2 }}">Qty: {{ $item->quantity }}</p>
                        </div>
                        <p class="text-sm font-bold text-accent">৳{{ number_format($item->subtotal, 2) }}</p>
                    </div>
                    @endforeach
                </div>

                <!-- Totals -->
                <div class="border-t {{ $divider }} pt-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="{{ $muted }}">Subtotal</span>
                        <span class="{{ $cardText }} font-medium">৳{{ number_format($totals['subtotal'], 2) }}</span>
                    </div>
                    @if(($totals['discount_amount'] ?? 0) > 0)
                    <div class="flex justify-between text-green-500">
                        <span>Discount</span>
                        <span>-৳{{ number_format($totals['discount_amount'], 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="{{ $muted }}">VAT (5%)</span>
                        <span class="{{ $cardText }} font-medium">৳{{ number_format($totals['vat_amount'], 2) }}</span>
                    </div>
                    <div class="border-t {{ $divider }} pt-3 flex justify-between text-lg font-bold">
                        <span class="{{ $cardText }}">Total</span>
                        <span class="text-accent">৳{{ number_format($totals['payable_amount'], 2) }}</span>
                    </div>
                </div>

                <!-- Place Order Button -->
                <button type="submit" class="w-full bg-accent text-black py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest mt-6">
                    Place Order
                </button>

                <p class="text-xs {{ $muted3 }} text-center mt-3">
                    By placing your order, you agree to our Terms & Conditions
                </p>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function toggleAddressFields() {
    const select = document.getElementById('addressSelect');
    const newAddressFields = document.getElementById('newAddressFields');
    
    if (select.value === 'new') {
        newAddressFields.classList.remove('hidden');
    } else {
        newAddressFields.classList.add('hidden');
    }
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('{{ route("checkout.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } else {
            alert(data.message || 'Checkout failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>
@endsection
