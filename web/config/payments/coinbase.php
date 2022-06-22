<?php
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
    'api_key'       => PaymentSetting::settings('coinbase_api_key'), //env('COINBASE_API_KEY', null),
    'webhook_key'   => PaymentSetting::settings('coinbase_webhook_key'), //env('COINBASE_WEBHOOK_KEY', null),
    'redirect_url'  => PaymentSetting::settings('coinbase_webhook_key'), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
    'cancel_url'    => PaymentSetting::settings('coinbase_cancel_url'), //env('PAYMENTS_REDIRECT_URL', 'https://sumra.net/'),
];
