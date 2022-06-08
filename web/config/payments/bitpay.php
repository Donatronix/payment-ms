<?php

use BitPaySDK\Env;
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
    'environment'       => PaymentGatewaySettings::manage_settings('BITPAY_ENVIRONMENT', Env::Test), //env('BITPAY_ENVIRONMENT', Env::Test),
    'api_tokens' => [
        'merchant'      => PaymentGatewaySettings::manage_settings('BITPAY_API_TOKEN_MERCHANT', null), //env('BITPAY_API_TOKEN_MERCHANT', null),
        'payroll'       => PaymentGatewaySettings::manage_settings('BITPAY_API_TOKEN_PAYROLL', null), //env('BITPAY_API_TOKEN_PAYROLL', null)
    ],
    'private_key' => [
        'path'          => storage_path(PaymentGatewaySettings::manage_settings('BITPAY_KEY_PATH', 'keys/bitpay.key')), //storage_path('keys/bitpay.key'),
        'password'      => PaymentGatewaySettings::manage_settings('BITPAY_PRIVATE_KEY_PASSWORD', null), //env('BITPAY_MASTER_PASSWORD', null),
    ],
    'webhook_url'       => PaymentGatewaySettings::manage_settings('BITPAY_PAYMENTS_WEBHOOK_URL', 'https://sumra.net/payment/'), //env('PAYMENTS_WEBHOOK_URL', 'https://sumra.net/payment/'),
    'redirect_url'      => PaymentGatewaySettings::manage_settings('BITPAY_REDIRECT_URL', 'https://sumra.net/'), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/')
];
