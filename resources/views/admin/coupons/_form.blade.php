@php
    $aInputBg     = theme('admin.input_bg');
    $aInputBorder = theme('admin.input_border');
    $aInputText   = theme('admin.input_text');
    $aMuted3      = theme('admin.muted_text_3');
    $aMuted2      = theme('admin.muted_text_2');

    $val = fn ($key, $default = '') => old($key, $coupon?->$key ?? $default);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Code -->
    <div class="md:col-span-2">
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Coupon Code *</label>
        <input type="text" name="code" required value="{{ $val('code') }}" placeholder="e.g. WELCOME10"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} font-mono uppercase focus:outline-none focus:border-accent transition">
        <p class="text-sm {{ $aMuted2 }} mt-1">Customers enter this code at checkout. Auto-uppercased.</p>
        @error('code')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Type -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Discount Type *</label>
        <select name="type" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
            <option value="percentage" {{ $val('type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
            <option value="fixed" {{ $val('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
        </select>
        @error('type')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Value -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Discount Value *</label>
        <input type="number" name="value" step="0.01" min="0" required value="{{ $val('value') }}" placeholder="10"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('value')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Min order amount -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Minimum Order Amount (৳)</label>
        <input type="number" name="min_order_amount" step="0.01" min="0" value="{{ $val('min_order_amount') }}" placeholder="No minimum"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('min_order_amount')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Max discount (percentage cap) -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Max Discount Cap (৳)</label>
        <input type="number" name="max_discount_amount" step="0.01" min="0" value="{{ $val('max_discount_amount') }}" placeholder="No cap"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        <p class="text-sm {{ $aMuted2 }} mt-1">Caps percentage discounts. Leave blank for unlimited.</p>
        @error('max_discount_amount')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Usage limit -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Total Usage Limit</label>
        <input type="number" name="usage_limit" min="1" value="{{ $val('usage_limit') }}" placeholder="Unlimited"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('usage_limit')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Per user limit -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Per-Customer Limit</label>
        <input type="number" name="per_user_limit" min="1" value="{{ $val('per_user_limit') }}" placeholder="Unlimited"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('per_user_limit')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Starts at -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Valid From</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('starts_at')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Expires at -->
    <div>
        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Expires At</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
        @error('expires_at')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Active -->
    <div class="md:col-span-2 flex items-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon?->is_active ?? true) ? 'checked' : '' }}
               class="w-5 h-5 rounded border-accent text-accent focus:ring-accent">
        <label class="text-sm font-medium {{ $aMuted3 }} uppercase tracking-wider">Active</label>
    </div>
</div>
