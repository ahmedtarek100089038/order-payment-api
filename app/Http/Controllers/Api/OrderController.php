<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Custom\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'user_id' => auth('api')->id(),
        ];

        $orders = $this->orderService->getOrders($filters, $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => new OrderCollection($orders),
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                $request->validated(),
                auth('api')->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        // Authorization: User can only view their own orders
        if ($order->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load(['items', 'payments']);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        // Authorization: User can only update their own orders
        if ($order->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $order = $this->orderService->updateOrder($order, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order)
    {
        // Authorization: User can only delete their own orders
        if ($order->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->orderService->deleteOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully',
            ]);
        } catch (OrderException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage(),
            ], 500);
        }
    }
}