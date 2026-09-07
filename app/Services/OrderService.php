<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrderService
{
    public function getAllOrders()
    {
        return Order::with('items.product')->get();
    }

    public function getOrderById(int $id)
    {
        return Order::with('items.product')->findOrFail($id);
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($data['items'] as $itemData) {
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->firstOrFail();

                if ($product->stock_quantity < $itemData['quantity']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Insufficient stock for product: {$product->name}"
                    ]);
                }

                $product->stock_quantity -= $itemData['quantity'];
                $product->save();

                $subtotal = $product->price * $itemData['quantity'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->price,
                ];
            }

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            $order->items()->createMany($itemsData);

            return $order->load('items.product');
        });
    }

    public function cancelOrder(Order $order)
    {
        if ($order->status === 'cancelled') {
            throw new ConflictHttpException('Order is already cancelled.');
        }

        return DB::transaction(function () use ($order) {
            $order->status = 'cancelled';
            $order->save();

            foreach ($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->stock_quantity += $item->quantity;
                    $product->save();
                }
            }

            return $order;
        });
    }
}
