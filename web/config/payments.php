<?php

use BitPaySDK\Env;

return [
    'bitpay' => [
        'environment' => env('BITPAY_ENVIRONMENT', Env::Test),
        'api_tokens' => [
            'merchant' => env('BITPAY_API_TOKEN_MERCHANT', null),
            'payroll' => env('BITPAY_API_TOKEN_PAYROLL', null)
        ],
        'private_key' => [
            'path' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bitpay-test.key',
            'password' => env('BITPAY_MASTER_PASSWORD', null),
        ],
        'webhook_url' => env('PAYMENTS_WEBHOOK_URL','https://sumra.net/payment/'),
        'redirect_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
    ],

    'coinbase' => [
        'api_key' => env('COINBASE_API_KEY', null),
        'webhook_key' => env('COINBASE_WEBHOOK_KEY', null),
        'redirect_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
        'cancel_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
            'api_url' => env('PAYPAL_SANDBOX_API_URL', 'https://api-m.sandbox.paypal.com'),
            'app_id' => 'APP-80W284485P519543T',
        ],
        'live' => [
            'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
            'api_url' => env('PAYPAL_LIVE_API_URL', 'https://api-m.paypal.com'),
            'app_id' => '',
        ],

        'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
        'notify_url' => env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
        'locale' => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
        'validate_ssl' => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
    ]
];
