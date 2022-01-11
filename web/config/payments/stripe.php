<?php

return [
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', null),
    'public_key' => env('STRIPE_PUBLIC_KEY', null),
    'secret_key' => env('STRIPE_SECRET_KEY', null)
];
