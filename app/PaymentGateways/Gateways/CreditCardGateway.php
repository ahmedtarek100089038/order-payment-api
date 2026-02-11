<?php

namespace App\PaymentGateways\Gateways;

use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\PaymentGateways\DTOs\PaymentResult;
use Illuminate\Support\Str;

class CreditCardGateway implements PaymentGatewayInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function charge(float $amount, array $paymentDetails): PaymentResult
    {
        if (!$this->validateCardDetails($paymentDetails)) {
            return PaymentResult::failed('Invalid card details', 'INVALID_CARD');
        }

        // Simulate 90% success rate
        $isSuccessful = rand(1, 100) <= 90;

        if ($isSuccessful) {
            $transactionId = 'CC_' . Str::uuid();
            return PaymentResult::success($transactionId, [
                'card_last_four' => substr($paymentDetails['card_number'] ?? '0000', -4),
                'processed_at' => now()->toIso8601String(),
            ]);
        }

        return PaymentResult::failed('Card declined', 'CARD_DECLINED');
    }

    public function getName(): string
    {
        return 'credit_card';
    }

    private function validateCardDetails(array $details): bool
    {
        return isset($details['card_number']) 
            && isset($details['expiry_month']) 
            && isset($details['expiry_year'])
            && isset($details['cvv']);
    }
}