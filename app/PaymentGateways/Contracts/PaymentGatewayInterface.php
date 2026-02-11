<?php

namespace App\PaymentGateways\Contracts;

use App\PaymentGateways\DTOs\PaymentResult;

interface PaymentGatewayInterface
{
    /**
     * Process a payment charge
     */
    public function charge(float $amount, array $paymentDetails): PaymentResult;

    /**
     * Get gateway name
     */
    public function getName(): string;
}