<?php

use App\Events\OrderStatusChanged;
use App\Jobs\SendOrderStatusUpdate;
use App\Listeners\QueueOrderStatusNotification;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Notify Customer',
        'email' => 'notifycustomer@test.com',
        'password' => bcrypt('password'),
    ]);

    $category = Category::create([
        'name' => 'Notify Category',
        'slug' => 'notify-category',
        'status' => 'active',
    ]);

    $this->product = Product::create([
        'name' => 'Notify Product',
        'slug' => 'notify-product',
        'description' => 'Test',
        'price' => 500,
        'stock' => 50,
        'sku' => 'NOTIFY-001',
        'category_id' => $category->id,
        'status' => 'active',
    ]);

    $this->orderService = app(OrderService::class);
});

function makeNotifiableOrder(User $user, Product $product, string $status = 'pending'): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => 'ORD-NOTIFY-' . uniqid(),
        'status' => $status,
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 500,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 500,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 500,
        'subtotal' => 500,
        'total' => 500,
    ]);

    return $order;
}

test('updating order status dispatches the OrderStatusChanged event', function () {
    Event::fake([OrderStatusChanged::class]);

    $order = makeNotifiableOrder($this->user, $this->product);

    $this->orderService->updateOrderStatus($order, 'processing');

    Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
        return $event->order->id === $order->id && $event->newStatus === 'processing';
    });
});

test('shipping an order emails the customer a status update', function () {
    Mail::fake();

    $order = makeNotifiableOrder($this->user, $this->product, 'processing');

    $this->orderService->updateOrderStatus($order, 'shipped');

    Mail::assertSent(OrderStatusUpdateMail::class, function ($mail) {
        return $mail->status === 'shipped'
            && $mail->hasTo('notifycustomer@test.com');
    });
});

test('the listener queues a job for notifiable statuses', function () {
    Queue::fake();

    $order = makeNotifiableOrder($this->user, $this->product);
    $listener = new QueueOrderStatusNotification();

    $listener->handle(new OrderStatusChanged($order, 'shipped'));

    Queue::assertPushed(SendOrderStatusUpdate::class);
});

test('the listener ignores the initial pending status', function () {
    Queue::fake();

    $order = makeNotifiableOrder($this->user, $this->product);
    $listener = new QueueOrderStatusNotification();

    $listener->handle(new OrderStatusChanged($order, 'pending'));

    Queue::assertNotPushed(SendOrderStatusUpdate::class);
});

test('customer self-cancellation does not send a status update email', function () {
    Mail::fake();

    $order = makeNotifiableOrder($this->user, $this->product);

    $this->orderService->cancelOrder($order, $this->user->id);

    Mail::assertNothingSent();
});

test('an admin note flows through to the status update email', function () {
    Mail::fake();

    $order = makeNotifiableOrder($this->user, $this->product, 'processing');

    $this->orderService->updateOrderStatus($order, 'shipped', 'Tracking #BD11223');

    Mail::assertSent(OrderStatusUpdateMail::class, function ($mail) {
        return $mail->status === 'shipped' && $mail->note === 'Tracking #BD11223';
    });
});

test('the listener forwards the note to the queued job', function () {
    Queue::fake();

    $order = makeNotifiableOrder($this->user, $this->product);
    $listener = new QueueOrderStatusNotification();

    $listener->handle(new OrderStatusChanged($order, 'shipped', 'Handle with care'));

    Queue::assertPushed(SendOrderStatusUpdate::class, function ($job) {
        return (new ReflectionProperty($job, 'note'))->getValue($job) === 'Handle with care';
    });
});
