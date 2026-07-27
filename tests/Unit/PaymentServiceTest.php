<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Ensure gateway config is available in tests
    config(['payments.gateways' => [
        'bkash' => \App\Services\PaymentGateways\BkashGateway::class,
    ]]);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->order = Order::create([
        'user_id' => $this->user->id,
        'order_number' => 'ORD-PAY-TEST-001',
        'payment_method' => 'cod',
        'subtotal' => 900,
        'vat_amount' => 45,
        'total_amount' => 945,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->paymentService = new PaymentService();
});

test('process COD payment creates pending payment', function () {
    $result = $this->paymentService->processPayment($this->order, 'cod');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();

    $this->assertDatabaseHas('payments', [
        'order_id' => $this->order->id,
        'payment_method' => 'cod',
        'status' => 'pending',
    ]);
});

test('unregistered payment method throws exception', function () {
    expect(fn () => $this->paymentService->processPayment($this->order, 'nagad'))
        ->toThrow(InvalidArgumentException::class, 'not supported');
});

test('invalid payment method throws exception', function () {
    expect(fn () => $this->paymentService->processPayment($this->order, 'bitcoin'))
        ->toThrow(InvalidArgumentException::class, 'not supported');
});

test('bkash payment uses mocked HTTP', function () {
    Http::fake([
        '*token/grant*' => Http::response(['id_token' => 'fake-token'], 200),
        '*create*' => Http::response([
            'paymentID' => 'TRX-12345',
            'bkashURL' => 'https://checkout.bkash.com/payment',
        ], 200),
    ]);

    $result = $this->paymentService->processPayment($this->order, 'bkash');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['url'])->toBe('https://checkout.bkash.com/payment');

    $this->assertDatabaseHas('payments', [
        'order_id' => $this->order->id,
        'payment_method' => 'bkash',
        'transaction_id' => 'TRX-12345',
        'status' => 'pending',
    ]);
});

test('bkash payment handles authentication failure', function () {
    Http::fake([
        '*token/grant*' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $result = $this->paymentService->processPayment($this->order, 'bkash');

    expect($result)->toBeArray();
    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBeString();
});

test('bkash payment handles creation failure', function () {
    Http::fake([
        '*token/grant*' => Http::response(['id_token' => 'fake-token'], 200),
        '*create*' => Http::response([
            'errorMessage' => 'Insufficient balance',
        ], 400),
    ]);

    $result = $this->paymentService->processPayment($this->order, 'bkash');

    expect($result)->toBeArray();
    expect($result['success'])->toBeFalse();
});

test('handle payment callback for unregistered method throws exception', function () {
    expect(fn () => $this->paymentService->handlePaymentCallback([], 'invalid'))
        ->toThrow(InvalidArgumentException::class, 'not supported');
});

test('handle bkash callback processes successful payment', function () {
    // First create a pending payment record
    $payment = Payment::create([
        'order_id' => $this->order->id,
        'payment_method' => 'bkash',
        'transaction_id' => 'TRX-EXEC-001',
        'amount' => $this->order->total_amount,
        'status' => 'pending',
    ]);

    Http::fake([
        '*token/grant*' => Http::response(['id_token' => 'fake-token'], 200),
        '*execute*' => Http::response([
            'statusCode' => '0000',
            'trxID' => 'TRX-FINAL-001',
            'paymentID' => 'TRX-EXEC-001',
        ], 200),
    ]);

    $result = $this->paymentService->handlePaymentCallback(
        ['paymentID' => 'TRX-EXEC-001'],
        'bkash'
    );

    expect($result['success'])->toBeTrue();
    expect($result['trxID'])->toBe('TRX-FINAL-001');

    $payment->refresh();
    expect($payment->status)->toBe('success');
    expect($this->order->fresh()->payment_status)->toBe('paid');
    expect($this->order->fresh()->status)->toBe('processing');
});

test('handle bkash callback processes failed payment', function () {
    Payment::create([
        'order_id' => $this->order->id,
        'payment_method' => 'bkash',
        'transaction_id' => 'TRX-FAIL-001',
        'amount' => $this->order->total_amount,
        'status' => 'pending',
    ]);

    Http::fake([
        '*token/grant*' => Http::response(['id_token' => 'fake-token'], 200),
        '*execute*' => Http::response([
            'statusCode' => '9999',
            'statusMessage' => 'Payment declined',
            'paymentID' => 'TRX-FAIL-001',
        ], 200),
    ]);

    $result = $this->paymentService->handlePaymentCallback(
        ['paymentID' => 'TRX-FAIL-001'],
        'bkash'
    );

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Payment declined');
});

test('handle bkash callback returns error when payment not found', function () {
    Http::fake([
        '*token/grant*' => Http::response(['id_token' => 'fake-token'], 200),
        '*execute*' => Http::response([
            'statusCode' => '0000',
            'trxID' => 'TRX-000',
        ], 200),
    ]);

    $result = $this->paymentService->handlePaymentCallback(
        ['paymentID' => 'NON-EXISTENT-ID'],
        'bkash'
    );

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Payment not found');
});
