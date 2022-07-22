<?php

namespace App\Helpers;

use App\Models\PaymentSetting;

class PaymentServiceSettings
{
    //Get Payment Gateway settings manager
    public static function settings($getKey = null, $default = null): string
    {
        try {
            $getKey = strtolower($getKey);

            if ($getKey) {
                $getValue = PaymentSetting::where('key', $getKey)->value('value');

                return ($getValue ? $getValue : $default);
            } else {
                return $default;
            }
        } catch (\Exception $e) {
            return $default;
        }
    }
}
