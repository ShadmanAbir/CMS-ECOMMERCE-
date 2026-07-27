<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:{{ $accentColor }}; padding:28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; letter-spacing:2px; text-transform:uppercase;">{{ $siteName }}</h1>
                        </td>
                    </tr>

                    {{-- Hero --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:40px 32px 24px; text-align:center;">
                            <div style="width:56px; height:56px; margin:0 auto 16px; border-radius:50%; background-color:{{ $accentColor }}; line-height:56px; text-align:center;">
                                <span style="color:#ffffff; font-size:26px;">&#10003;</span>
                            </div>
                            <h2 style="margin:0 0 8px; color:#1a1a1a; font-size:22px;">Thank you for your order!</h2>
                            <p style="margin:0; color:#666666; font-size:14px; line-height:1.6;">
                                Hi {{ $order->user->name ?? 'there' }}, we've received your order and are getting it ready.
                            </p>
                        </td>
                    </tr>

                    {{-- Order meta --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9f9f9; border:1px solid #eeeeee;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:13px; color:#666666;">
                                                    <strong style="color:#1a1a1a;">Order Number</strong><br>
                                                    #{{ $order->order_number }}
                                                </td>
                                                <td style="font-size:13px; color:#666666; text-align:right;">
                                                    <strong style="color:#1a1a1a;">Order Date</strong><br>
                                                    {{ $order->created_at->format('M d, Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:13px; color:#666666; padding-top:12px;">
                                                    <strong style="color:#1a1a1a;">Payment Method</strong><br>
                                                    {{ strtoupper(str_replace('_', ' ', $order->payment_method ?? '—')) }}
                                                </td>
                                                <td style="font-size:13px; color:#666666; padding-top:12px; text-align:right;">
                                                    <strong style="color:#1a1a1a;">Status</strong><br>
                                                    {{ ucfirst($order->status) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Items --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 24px;">
                            <h3 style="margin:0 0 12px; color:#1a1a1a; font-size:14px; text-transform:uppercase; letter-spacing:1px;">Order Items</h3>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-transform:uppercase;">Item</td>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-align:center; text-transform:uppercase;">Qty</td>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-align:right; text-transform:uppercase;">Total</td>
                                </tr>
                                @foreach($order->items as $item)
                                <tr>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; color:#333333;">
                                        {{ $item->product->name ?? 'Product' }}
                                        @if($item->variant)
                                            <br><span style="font-size:12px; color:#888888;">{{ $item->variant->name }}</span>
                                        @endif
                                    </td>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; color:#333333; text-align:center;">{{ $item->quantity }}</td>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; color:#333333; text-align:right;">{{ theme('currency.symbol', '৳') }}{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Totals --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:13px; color:#666666; padding:4px 0;">Subtotal</td>
                                    <td style="font-size:13px; color:#333333; padding:4px 0; text-align:right;">{{ theme('currency.symbol', '৳') }}{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                @if($order->discount_amount > 0)
                                <tr>
                                    <td style="font-size:13px; color:#666666; padding:4px 0;">Discount</td>
                                    <td style="font-size:13px; color:#2e7d32; padding:4px 0; text-align:right;">&minus; {{ theme('currency.symbol', '৳') }}{{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($order->vat_amount > 0)
                                <tr>
                                    <td style="font-size:13px; color:#666666; padding:4px 0;">VAT</td>
                                    <td style="font-size:13px; color:#333333; padding:4px 0; text-align:right;">{{ theme('currency.symbol', '৳') }}{{ number_format($order->vat_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($order->shipping_amount > 0)
                                <tr>
                                    <td style="font-size:13px; color:#666666; padding:4px 0;">Shipping</td>
                                    <td style="font-size:13px; color:#333333; padding:4px 0; text-align:right;">{{ theme('currency.symbol', '৳') }}{{ number_format($order->shipping_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="border-top:2px solid {{ $accentColor }}; padding-top:12px; font-size:16px; font-weight:bold; color:#1a1a1a;">Total</td>
                                    <td style="border-top:2px solid {{ $accentColor }}; padding-top:12px; font-size:16px; font-weight:bold; color:{{ $accentColor }}; text-align:right;">{{ theme('currency.symbol', '৳') }}{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Notes --}}
                    @if($order->notes)
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 32px;">
                            <p style="margin:0; padding:12px 16px; background-color:#f9f9f9; border-left:3px solid {{ $accentColor }}; font-size:13px; color:#666666;">
                                <strong style="color:#1a1a1a;">Order Notes:</strong> {{ $order->notes }}
                            </p>
                        </td>
                    </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#1a1a1a; padding:28px 32px; text-align:center;">
                            <p style="margin:0 0 8px; color:#ffffff; font-size:13px;">Questions about your order? Reach us at {{ $contactEmail }}</p>
                            <p style="margin:0; color:#888888; font-size:12px;">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
