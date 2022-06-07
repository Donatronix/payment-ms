<?php

namespace App\Helpers;

use App\Models\AllPaymentGatewaySettings as ManageSettingsModel;



class PaymentGatewaySettings
{

    //Get Payment Gateway settings manager
    public static function manage_settings($getSetting = null, $default = null, $status = 1)
    {
        try {
            $status     = (is_int($status) ? $status : 1);
            $getSetting = strtolower($getSetting);
            if($getSetting)
            {
                $getValue = ManageSettingsModel::where('status', $status)->value($getSetting);
                return ($getValue ? $getValue : $default);
            }else{
                return $default;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }


}

