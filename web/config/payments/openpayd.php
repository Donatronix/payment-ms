<?php
use App\Helpers\PaymentGatewaySettings;
/**
 * Helper function manage_settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g username, password, url, public_key_path
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [

    'username'          => PaymentGatewaySettings::manage_settings('OPENPAYD_USERNAME', 'USERNAME', 1), //env("OPENPAYD_USERNAME","USERNAME"),
    "password"          => PaymentGatewaySettings::manage_settings('OPENPAYD_PASSWORD', 'PASSWORD', 1), //env("OPENPAYD_PASSWORD", "PASSWORD"),
    "url"               => PaymentGatewaySettings::manage_settings('OPENPAYD_URL', 'https://sandbox.openpayd.com/api/', 1), //env("OPENPAYD_URL", "https://sandbox.openpayd.com/api/"),
    "public_key_path"   => storage_path(PaymentGatewaySettings::manage_settings('OPENPAYD_PUBLIC_KEY_PATH', 'keys/openpayd.key', 1)), //storage_path('keys/openpayd.key')

];
