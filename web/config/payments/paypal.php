<?php
use App\Helpers\PaymentGatewaySettings as PaymentSetting;
/**
 * Helper function settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g sandboc_client_id, sandboc_client_secret, api_url, etc
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [
    'mode' => PaymentSetting::settings('paypal_mode'), //env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id'     => PaymentSetting::settings('paypal_sandbox_client_id'), //env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => PaymentSetting::settings('paypal_sandbox_client_secret'), //env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'api_url'       => PaymentSetting::settings('paypal_sandbox_api_url'), //env('PAYPAL_SANDBOX_API_URL', 'https://api-m.sandbox.paypal.com'),
        'app_id'        => PaymentSetting::settings('paypal_sandbox_app_id'), //'APP-80W284485P519543T',
    ],
    'live' => [
        'client_id'     => PaymentSetting::settings('paypal_live_client_id'), //env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => PaymentSetting::settings('paypal_live_client_secret'), //env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'api_url'       => PaymentSetting::settings('paypal_live_api_url'), //env('PAYPAL_LIVE_API_URL', 'https://api-m.paypal.com'),
        'app_id'        => PaymentSetting::settings('paypal_live_app_id'), //'',
    ],

    'payment_action'    => PaymentSetting::settings('paypal_payment_action'), //env('PAYPAL_PAYMENT_ACTION', 'Sale'),
    'currency'          => PaymentSetting::settings('paypal_currency'), //env('PAYPAL_CURRENCY', 'USD'),
    'notify_url'        => PaymentSetting::settings('paypal_notify_url'), //env('PAYPAL_NOTIFY_URL', ''),
    'locale'            => PaymentSetting::settings('paypal_local'), //env('PAYPAL_LOCALE', 'en_US'),
    'validate_ssl'      => PaymentSetting::settings('paypal_validate_ssl'), //env('PAYPAL_VALIDATE_SSL', true),
];
