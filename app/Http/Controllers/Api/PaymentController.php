<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Custom\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentCollection;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $filters = [
            'order_id' => $request->input('order_id'),
            'status' => $request->input('status'),
        ];

        $payments = $this->paymentService->getPayments($filters, $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => new PaymentCollection($payments),
        ]);
    }

    /**
     * Process a payment
     */
    public function store(ProcessPaymentRequest $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);

            // Authorization: User can only pay for their own orders
            if ($order->user_id !== auth('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $payment = $this->paymentService->processPayment($order, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => new PaymentResource($payment),
            ], 201);
        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display payments for a specific order
     */
    public function orderPayments(Order $order)
    {
        // Authorization: User can only view payments for their own orders
        if ($order->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $payments = $order->payments()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
        ]);
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        $payment = \App\Models\Payment::with('order')->findOrFail($id);

        // Authorization: User can only view their own payments
        if ($payment->order->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentResource($payment),
        ]);
    }
}