<?php

use BitPaySDK\Env;

return [
    'environment' => env('BITPAY_ENVIRONMENT', Env::Test),
    'api_tokens' => [
        'merchant' => env('BITPAY_API_TOKEN_MERCHANT', null),
        'payroll' => env('BITPAY_API_TOKEN_PAYROLL', null)
    ],
    'private_key' => [
        'path' => storage_path('keys/bitpay.key'),
        'password' => env('BITPAY_MASTER_PASSWORD', null),
    ],
    'webhook_url' => env('PAYMENTS_WEBHOOK_URL', 'https://sumra.net/payment/'),
    'redirect_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
];
