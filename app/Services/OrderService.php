<?php

namespace App\Services;

use App\Exceptions\Custom\OrderException;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $totalAmount += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['status'])) {
                $order->update(['status' => $data['status']]);
            }

            if (isset($data['items'])) {
                $order->items()->delete();

                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->update(['total_amount' => $totalAmount]);
            }

            return $order->fresh(['items']);
        });
    }

    public function deleteOrder(Order $order): bool
    {
        if (!$order->canBeDeleted()) {
            throw new OrderException('Cannot delete order with associated payments.');
        }

        return $order->delete();
    }

    public function getOrders(array $filters = [], int $perPage = 15)
    {
        $query = Order::with(['items', 'payments']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }
}