<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function paypal_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g api_key, webhook_key, redirect_url, cancel_url
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [
    'api_key'       => PaymentGatewaySettings::coinbase_settings('api_key', null, 1), //env('COINBASE_API_KEY', null),
    'webhook_key'   => PaymentGatewaySettings::coinbase_settings('webhook_key', null, 1), //env('COINBASE_WEBHOOK_KEY', null),
    'redirect_url'  => PaymentGatewaySettings::coinbase_settings('redirect_url', 'https://sumra.net/', 1), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
    'cancel_url'    => PaymentGatewaySettings::coinbase_settings('cancel_url', 'https://sumra.net/', 1), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
];
