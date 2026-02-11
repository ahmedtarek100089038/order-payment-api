<?php

namespace App\Services;

use App\Exceptions\Custom\PaymentException;
use App\Models\Order;
use App\Models\Payment;
use App\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager
    ) {}

    public function processPayment(Order $order, array $data): Payment
    {
        if (!$order->isConfirmed()) {
            throw new PaymentException('Payment can only be processed for confirmed orders.');
        }

        return DB::transaction(function () use ($order, $data) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            try {
                $gateway = $this->gatewayManager->gateway($data['payment_method']);
                $result = $gateway->charge($order->total_amount, $data['payment_details'] ?? []);

                $payment->update([
                    'status' => $result->success ? 'successful' : 'failed',
                    'transaction_id' => $result->transactionId,
                    'gateway_response' => $result->gatewayResponse,
                ]);

                if (!$result->success) {
                    throw new PaymentException($result->message);
                }

                return $payment->fresh();

            } catch (\Exception $e) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => ['error' => $e->getMessage()],
                ]);

                throw new PaymentException('Payment processing failed: ' . $e->getMessage());
            }
        });
    }

    public function getPayments(array $filters = [], int $perPage = 15)
    {
        $query = Payment::with('order');

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }
}