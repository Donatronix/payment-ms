<?php
use App\Helpers\PaymentGatewaySettings as PaymentSetting;
/**
 * Helper function settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key type) e.g gateway_name, webhook_secret, public_key, secret_key
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */
return [
    'webhook_secret'    => PaymentSetting::settings('stripe_webhook_secret'), //env('STRIPE_WEBHOOK_SECRET', null),
    'public_key'        => PaymentSetting::settings('stripe_public_key'), //env('STRIPE_PUBLIC_KEY', null),
    'secret_key'        => PaymentSetting::settings('stripe_secret_key'), //env('STRIPE_SECRET_KEY', null),
];
