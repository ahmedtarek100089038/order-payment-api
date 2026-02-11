<?php

namespace App\PaymentGateways;

use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\PaymentGateways\Gateways\CreditCardGateway;
use App\PaymentGateways\Gateways\PayPalGateway;
use App\PaymentGateways\Gateways\StripeGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    private array $gateways = [];

    public function __construct()
    {
        $this->registerDefaultGateways();
    }

    private function registerDefaultGateways(): void
    {
        $this->register('credit_card', CreditCardGateway::class);
        $this->register('paypal', PayPalGateway::class);
        $this->register('stripe', StripeGateway::class);
    }

    public function register(string $name, string $gatewayClass): void
    {
        $this->gateways[$name] = $gatewayClass;
    }

    public function gateway(string $paymentMethod): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$paymentMethod])) {
            throw new InvalidArgumentException("Payment gateway [{$paymentMethod}] not supported.");
        }

        $config = $this->getConfig($paymentMethod);
        $gatewayClass = $this->gateways[$paymentMethod];

        return new $gatewayClass($config);
    }

    private function getConfig(string $gatewayName): array
    {
        return match($gatewayName) {
            'credit_card' => [
                'merchant_id' => config('payment.credit_card.merchant_id'),
                'api_key' => config('payment.credit_card.api_key'),
            ],
            'paypal' => [
                'client_id' => config('payment.paypal.client_id'),
                'client_secret' => config('payment.paypal.client_secret'),
            ],
            'stripe' => [
                'secret_key' => config('payment.stripe.secret_key'),
                'publishable_key' => config('payment.stripe.publishable_key'),
            ],
            default => [],
        };
    }

    public function availableGateways(): array
    {
        return array_keys($this->gateways);
    }
}