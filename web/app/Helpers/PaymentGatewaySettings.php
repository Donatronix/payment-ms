<?php

namespace App\Helpers;

use App\Models\PaymentSetting as PaymentSettingsModel;

class PaymentGatewaySettings
{
    //Get Payment Gateway settings manager
    public static function settings($getKey = null, $default = null): string
    {
        try {
            $getKey = strtolower($getKey);

            if ($getKey) {
                $getValue = PaymentSettingsModel::where('key', $getKey)->value('value');

                return ($getValue ? $getValue : $default);
            } else {
                return $default;
            }
        } catch (\Exception $e) {
            return $default;
        }
    }
}
