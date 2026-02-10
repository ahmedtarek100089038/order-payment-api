<?php

return [
    'credit_card' => [
        'merchant_id' => env('CREDIT_CARD_MERCHANT_ID', 'merchant_123'),
        'api_key' => env('CREDIT_CARD_API_KEY', 'key_test_123'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', 'paypal_client_id'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', 'paypal_client_secret'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY', 'sk_test_123'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', 'pk_test_123'),
    ],
];