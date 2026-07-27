<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Digest</title>
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

                    {{-- Intro --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:36px 32px 20px; text-align:center;">
                            <h2 style="margin:0 0 8px; color:#1a1a1a; font-size:20px;">Low Stock Digest</h2>
                            <p style="margin:0; color:#666666; font-size:14px; line-height:1.6;">
                                {{ $alerts->count() }} {{ \Illuminate\Support\Str::plural('product', $alerts->count()) }}
                                {{ $alerts->count() === 1 ? 'needs' : 'need' }} restocking. Review the list below.
                            </p>
                        </td>
                    </tr>

                    {{-- Alert table --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-transform:uppercase;">Product</td>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-align:center; text-transform:uppercase;">In Stock</td>
                                    <td style="border-bottom:2px solid {{ $accentColor }}; padding:8px 0; font-size:12px; font-weight:bold; color:#1a1a1a; text-align:center; text-transform:uppercase;">Threshold</td>
                                </tr>
                                @foreach($alerts as $alert)
                                <tr>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; color:#333333;">
                                        {{ $alert->product->name ?? 'Product #' . $alert->product_id }}
                                        @if($alert->product && $alert->product->sku)
                                            <br><span style="font-size:12px; color:#888888;">SKU: {{ $alert->product->sku }}</span>
                                        @endif
                                    </td>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; font-weight:bold; color:#c62828; text-align:center;">
                                        {{ $alert->product?->totalStock() ?? $alert->stock_at_alert }}
                                    </td>
                                    <td style="border-bottom:1px solid #eeeeee; padding:12px 0; font-size:14px; color:#333333; text-align:center;">{{ $alert->threshold }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:0 32px 36px; text-align:center;">
                            <a href="{{ route('admin.alerts.index') }}" style="display:inline-block; background-color:{{ $accentColor }}; color:#ffffff; text-decoration:none; font-size:13px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; padding:14px 28px; border-radius:6px;">
                                Manage Inventory Alerts
                            </a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#1a1a1a; padding:28px 32px; text-align:center;">
                            <p style="margin:0; color:#888888; font-size:12px;">&copy; {{ date('Y') }} {{ $siteName }}. Automated inventory notification.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
