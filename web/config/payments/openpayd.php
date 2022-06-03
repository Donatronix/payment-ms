<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function paypal_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g username, password, url, public_key_path
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [

    'username'          => PaymentGatewaySettings::openpayd_settings('USERNAME', 'USERNAME', 1), //env("OPENPAYD_USERNAME","USERNAME"),
    "password"          => PaymentGatewaySettings::openpayd_settings('PASSWORD', 'PASSWORD', 1), //env("OPENPAYD_PASSWORD", "PASSWORD"),
    "url"               => PaymentGatewaySettings::openpayd_settings('URL', 'https://sandbox.openpayd.com/api/', 1), //env("OPENPAYD_URL", "https://sandbox.openpayd.com/api/"),
    "public_key_path"   => storage_path(PaymentGatewaySettings::openpayd_settings('PUBLIC_KEY_PATH', 'keys/openpayd.key', 1)), //storage_path('keys/openpayd.key')

];
