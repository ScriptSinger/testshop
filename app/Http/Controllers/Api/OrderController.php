<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function catalog()
    {
        return Product::all();
    }

    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return response()->json([
            'message' => 'Order created and items reserved',
            'order_id' => $order->id,
        ]);
    }

    public function approveOrder(ApproveOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->approveOrder($request->input('order_id'));

        return response()->json([
            'message' => 'Order approved',
            'order_id' => $order->id,
            'status' => $order->status,
        ]);
    }
}
