<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function stripe_settings()
 * It  takes two Parameters:
 *  - parameter 1: string (key type) e.g gateway_name, webhook_secret, public_key, secret_key
 *  - parameter 2: integer (status) e.g 0-active, 1-inactive
 *
 */
return [
    'webhook_secret'    => PaymentGatewaySettings::stripe_settings('WEBHOOK_SECRET', 1), //env('STRIPE_WEBHOOK_SECRET', null),
    'public_key'        => PaymentGatewaySettings::stripe_settings('PUBLIC_KEY', 1), //env('STRIPE_PUBLIC_KEY', null),
    'secret_key'        => PaymentGatewaySettings::stripe_settings('SECRET_KEY', 1), //env('STRIPE_SECRET_KEY', null)
];
