<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index()
    {
        $orders = $this->orderService->getAllOrders();
        return OrderResource::collection($orders);
    }

    public function store(OrderStoreRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());
        return new OrderResource($order);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrderById($id);
        return new OrderResource($order);
    }

    public function cancel(int $id)
    {
        $order = $this->orderService->getOrderById($id);
        $order = $this->orderService->cancelOrder($order);
        return new OrderResource($order);
    }
}
