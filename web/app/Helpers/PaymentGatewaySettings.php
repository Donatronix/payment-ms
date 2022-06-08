<?php

namespace App\Helpers;

use App\Models\PaymentSystem as PaymentSystemModel;
use App\Models\PaymentSettings as PaymentSettingsModel;



class PaymentGatewaySettings
{

    //Get Payment Gateway settings manager
    public static function manage_settings($getKey = null, $default = null, $status = 1)
    {
        try {
            $status     = (is_int($status) ? $status : 1);
            $getKey = strtolower($getKey);
            if($getKey)
            {
                $getValue = PaymentSettingsModel::where('setting_key', $getKey)->value('setting_value');
                return ($getValue ? $getValue : $default);
            }else{
                return $default;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }


}

