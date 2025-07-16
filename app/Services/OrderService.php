<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data['customer_id']);

            $order = $customer->orders()->create([
                'status' => 'pending',
                'ordered_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);



                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'stock_quantity' => "Not enough stock for product {$product->name}",
                    ]);
                }

                $product->decrement('stock_quantity', $item['quantity']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_order_time' => $product->price,  // цена на момент заказа
                ]);
            }

            return $order;
        });
    }

    public function approveOrder(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::with('customer', 'items')->findOrFail($orderId);

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Order already processed',
                ]);
            }

            $total = $order->items->sum(fn($item) => $item->price * $item->quantity);

            if ($order->customer->balance < $total) {
                throw ValidationException::withMessages([
                    'balance' => 'Insufficient balance',
                ]);
            }

            $order->customer->decrement('balance', $total);
            $order->update(['status' => 'approved']);

            return $order;
        });
    }
}
