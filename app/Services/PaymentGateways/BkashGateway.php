<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class BkashGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;
    protected ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = config('services.bkash.base_url');
        $this->appKey = config('services.bkash.app_key');
        $this->appSecret = config('services.bkash.app_secret');
        $this->username = config('services.bkash.username');
        $this->password = config('services.bkash.password');
    }

    public function createPayment(Order $order): array
    {
        try {
            $this->authenticate();

            $response = Http::withHeaders([
                'Authorization' => $this->token,
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode' => '0011',
                'payerReference' => $order->user_id,
                'callbackURL' => route('payment.callback', ['method' => 'bkash']),
                'amount' => $order->total_amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'bkash',
                    'transaction_id' => $data['paymentID'] ?? null,
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'payment_response' => $data,
                ]);

                return ['success' => true, 'url' => $data['bkashURL'] ?? null];
            }

            return [
                'success' => false,
                'error' => $response->json('errorMessage') ?? 'bKash payment creation failed',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function handleCallback(array $data): array
    {
        try {
            $this->authenticate();

            $response = Http::withHeaders([
                'Authorization' => $this->token,
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $data['paymentID'],
            ]);

            if ($response->successful()) {
                $result = $response->json();

                $payment = Payment::where('transaction_id', $data['paymentID'])->first();

                if (!$payment) {
                    return ['success' => false, 'error' => 'Payment not found'];
                }

                if ($result['statusCode'] === '0000') {
                    $payment->update([
                        'status' => 'success',
                        'transaction_id' => $result['trxID'],
                        'paid_at' => now(),
                        'payment_response' => $result,
                    ]);

                    $payment->order->update([
                        'payment_status' => 'paid',
                        'transaction_id' => $result['trxID'],
                        'status' => 'processing',
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Payment successful',
                        'trxID' => $result['trxID'],
                    ];
                }

                $payment->update([
                    'status' => 'failed',
                    'payment_response' => $result,
                ]);

                return [
                    'success' => false,
                    'error' => $result['statusMessage'] ?? 'Payment failed',
                ];
            }

            return ['success' => false, 'error' => 'Payment execution failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function authenticate(): void
    {
        $response = Http::post("{$this->baseUrl}/tokenized/checkout/token/grant", [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
        ]);

        if ($response->successful()) {
            $this->token = $response->json('id_token');
            return;
        }

        throw new \App\Exceptions\PaymentFailedException('bKash', 'Authentication failed: ' . $response->body());
    }
}
