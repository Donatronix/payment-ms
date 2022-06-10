<?php

use BitPaySDK\Env;
use App\Helpers\PaymentGatewaySettings as PaymentSetting;
/**
 * Helper function settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g api_key, webhook_key, redirect_url, cancel_url
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [
    'environment'       => PaymentSetting::settings('bitpay_environment', Env::Test),
    'api_tokens' => [
        'merchant'      => PaymentSetting::settings('bitpay_api_token_merchant'), //env('BITPAY_API_TOKEN_MERCHANT', null)
        'payroll'       => PaymentSetting::settings('bitpay_api_token_payroll'), //env('BITPAY_API_TOKEN_PAYROLL', null)
    ],
    'private_key' => [
        'path'          => storage_path(PaymentSetting::settings('bitpay_key_path')), //keys/bitpay.key
        'password'      => PaymentSetting::settings('bitpay_private_key_password'), //env('BITPAY_MASTER_PASSWORD', null),
    ],
    'webhook_url'       => PaymentSetting::settings('bitpay_payment_webhook_url'), //env('PAYMENTS_WEBHOOK_URL', 'https://sumra.net/payment/'),
    'redirect_url'      => PaymentSetting::settings('bitpay_redirect_url'), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
];
