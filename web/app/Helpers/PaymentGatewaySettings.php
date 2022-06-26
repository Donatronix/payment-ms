<?php

namespace App\Helpers;

use App\Models\PaymentSetting as PaymentSettingsModel;

class PaymentGatewaySettings
{
    //Get Payment Gateway settings manager
    public static function settings($getKey = null, $default = null, $status = 1): string
    {
        try {
            $status = (is_int($status) ? $status : 1);
            $getKey = strtolower($getKey);

            if ($getKey) {
                $getValue = PaymentSettingsModel::where('key', $getKey)->value('value');

                return ($getValue ? $getValue : $default);
            } else {
                return $default;
            }
        } catch (\Throwable $e) {
            return '';
        }
    }
}
