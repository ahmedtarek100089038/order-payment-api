<?php

namespace App\PaymentGateways\Gateways;

use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\PaymentGateways\DTOs\PaymentResult;
use Illuminate\Support\Str;

class PayPalGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function charge(float $amount, array $paymentDetails): PaymentResult
    {
        if (!isset($paymentDetails['paypal_email'])) {
            return PaymentResult::failed('PayPal email required', 'MISSING_EMAIL');
        }

        // Simulate 95% success rate
        $isSuccessful = rand(1, 100) <= 95;

        if ($isSuccessful) {
            $transactionId = 'PP_' . Str::uuid();
            return PaymentResult::success($transactionId, [
                'payer_email' => $paymentDetails['paypal_email'],
                'processed_at' => now()->toIso8601String(),
            ]);
        }

        return PaymentResult::failed('PayPal payment failed', 'PAYMENT_FAILED');
    }

    public function getName(): string
    {
        return 'paypal';
    }
}