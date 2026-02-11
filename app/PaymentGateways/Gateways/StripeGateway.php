<?php

namespace App\PaymentGateways\Gateways;

use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\PaymentGateways\DTOs\PaymentResult;
use Illuminate\Support\Str;

class StripeGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function charge(float $amount, array $paymentDetails): PaymentResult
    {
        if (!isset($paymentDetails['stripe_token'])) {
            return PaymentResult::failed('Stripe token required', 'MISSING_TOKEN');
        }

        // Simulate 92% success rate
        $isSuccessful = rand(1, 100) <= 92;

        if ($isSuccessful) {
            $transactionId = 'STRIPE_' . Str::uuid();
            return PaymentResult::success($transactionId, [
                'stripe_charge_id' => 'ch_' . Str::random(24),
                'processed_at' => now()->toIso8601String(),
            ]);
        }

        return PaymentResult::failed('Stripe payment failed', 'CHARGE_FAILED');
    }

    public function getName(): string
    {
        return 'stripe';
    }
}