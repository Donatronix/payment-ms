<?php

return [
    'api_key' => env('COINBASE_API_KEY', null),
    'webhook_key' => env('COINBASE_WEBHOOK_KEY', null),
    'redirect_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
    'cancel_url' => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
];
