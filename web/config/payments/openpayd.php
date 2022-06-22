<?php
use App\Helpers\PaymentGatewaySettings as PaymentSetting;
/**
 * Helper function settings()
 * It  takes three (3) Parameters:
 *  - parameter 1: string (key/Field type) e.g username, password, url, public_key_path
 *  - parameter 2: string (Default value) e.g null
 *  - parameter 3: integer (status) e.g 0-active, 1-inactive
 *
 */

return [

    'username'          => PaymentSetting::settings('openpayd_username'), //env("OPENPAYD_USERNAME","USERNAME"),
    "password"          => PaymentSetting::settings('openpayd_password'), //env("OPENPAYD_PASSWORD", "PASSWORD"),
    "url"               => PaymentSetting::settings('openpayd_url'), //env("OPENPAYD_URL", "https://sandbox.openpayd.com/api/"),
    "public_key_path"   => storage_path(PaymentSetting::settings('openpayd_public_key_path')), //storage_path('keys/openpayd.key'),

];
