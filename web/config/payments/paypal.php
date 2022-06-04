<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function paypal_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g sandboc_client_id, sandboc_client_secret, api_url, etc
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [
    'mode' => PaymentGatewaySettings::paypal_settings('MODE', 1), //env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
    'sandbox' => [
        'client_id'     => PaymentGatewaySettings::paypal_settings('SANDBOX_CLIENT_ID', null, 1), //env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => PaymentGatewaySettings::paypal_settings('SANDBOX_CLIENT_SECRET', null, 1), //env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'api_url'       => PaymentGatewaySettings::paypal_settings('API_URL', 'https://api-m.sandbox.paypal.com', 1), //env('PAYPAL_SANDBOX_API_URL', 'https://api-m.sandbox.paypal.com'),
        'app_id'        => PaymentGatewaySettings::paypal_settings('APP_ID', 'APP-80W284485P519543T', 1), //'APP-80W284485P519543T',
    ],
    'live' => [
        'client_id'     => PaymentGatewaySettings::paypal_settings('LIVE_CLIENT_ID', null, 1), //env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => PaymentGatewaySettings::paypal_settings('LIVE_CLIENT_SECRET', null, 1), //env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'api_url'       => PaymentGatewaySettings::paypal_settings('API_URL', 'https://api-m.paypal.com', 1), //env('PAYPAL_LIVE_API_URL', 'https://api-m.paypal.com'),
        'app_id'        => PaymentGatewaySettings::paypal_settings('APP_ID', null, 1), //'',
    ],

    'payment_action'    => PaymentGatewaySettings::paypal_settings('PAYMENT_ACTION', 'Sale', 1), //env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
    'currency'          => PaymentGatewaySettings::paypal_settings('CURRENCY', 'USD', 1), //env('PAYPAL_CURRENCY', 'USD'),
    'notify_url'        => PaymentGatewaySettings::paypal_settings('NOTIFY_URL', null, 1), //env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
    'locale'            => PaymentGatewaySettings::paypal_settings('LOCALE', 'en_US', 1), //env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'validate_ssl'      => PaymentGatewaySettings::paypal_settings('VALIDATE_SSL', true, 1), //env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
];
