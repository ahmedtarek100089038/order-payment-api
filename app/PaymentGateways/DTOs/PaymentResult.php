<?php

namespace App\PaymentGateways\DTOs;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?string $transactionId = null,
        public ?array $gatewayResponse = null,
        public ?string $errorCode = null
    ) {}

    public static function success(string $transactionId, array $gatewayResponse = []): self
    {
        return new self(
            success: true,
            message: 'Payment processed successfully',
            transactionId: $transactionId,
            gatewayResponse: $gatewayResponse
        );
    }

    public static function failed(string $message, ?string $errorCode = null, array $gatewayResponse = []): self
    {
        return new self(
            success: false,
            message: $message,
            gatewayResponse: $gatewayResponse,
            errorCode: $errorCode
        );
    }
}