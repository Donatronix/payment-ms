<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function manage_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g api_key, webhook_key, redirect_url, cancel_url
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [
    'api_key'       => env('COINBASE_API_KEY', null), //PaymentGatewaySettings::manage_settings('COINBASE_API_KEY', null),
    'webhook_key'   => env('COINBASE_WEBHOOK_KEY', null), //PaymentGatewaySettings::manage_settings('COINBASE_WEBHOOK_KEY', null),
    'redirect_url'  => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'), //PaymentGatewaySettings::manage_settings('COINBASE_REDIRECT_URL', 'https://sumra.net/'),
    'cancel_url'    => env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'), //PaymentGatewaySettings::manage_settings('COINBASE_CANCEL_url', 'https://sumra.net/'),
];
