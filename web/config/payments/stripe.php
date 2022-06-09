<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function manage_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key type) e.g gateway_name, webhook_secret, public_key, secret_key
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */
return [
    'webhook_secret'    => env('STRIPE_WEBHOOK_SECRET', null), //PaymentGatewaySettings::manage_settings('STRIPE_WEBHOOK_SECRET', null),
    'public_key'        => env('STRIPE_PUBLIC_KEY', null), //PaymentGatewaySettings::manage_settings('STRIPE_PUBLIC_KEY', null),
    'secret_key'        => env('STRIPE_SECRET_KEY', null), //PaymentGatewaySettings::manage_settings('STRIPE_SECRET_KEY', null),
];
