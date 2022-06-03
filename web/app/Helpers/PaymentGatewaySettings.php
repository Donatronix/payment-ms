<?php

namespace App\Helpers;

use App\Models\StripePaymentGatewaySetup;
use App\Models\PaypalPaymentGatewaySetup;
use App\Models\OpenpaydPaymentGatewaySetup;



class PaymentGatewaySettings
{

    //Get Stripe Gateway settings
    public static function stripe_settings($getSetting = null, $default = null, $status = 1)
    {
            try {
                $status     = (is_int($status) ? $status : 1);
                $getSetting = strtolower($getSetting);
                if($getSetting)
                {
                    $getValue = StripePaymentGatewaySetup::where('status', $status)->value($getSetting);
                    return ($getValue ? $getValue : $default);
                }else{
                    return $default;
                }
            } catch (\Throwable $e) {
                return null;
            }
    }

    //Get Paypal Gateway settings
    public static function paypal_settings($getSetting = null, $default = null, $status = 1)
    {
            try {
                $status     = (is_int($status) ? $status : 1);
                $getSetting = strtolower($getSetting);
                if($getSetting)
                {
                    $getValue = PaypalPaymentGatewaySetup::where('status', $status)->value($getSetting);
                    return ($getValue ? $getValue : $default);
                }else{
                    return $default;
                }
            } catch (\Throwable $e) {
                return null;
            }
    }

    //Get Openpayd Gateway settings
    public static function openpayd_settings($getSetting = null, $default = null, $status = 1)
    {
        try {
            $status     = (is_int($status) ? $status : 1);
            $getSetting = strtolower($getSetting);
            if($getSetting)
            {
                $getValue = OpenpaydPaymentGatewaySetup::where('status', $status)->value($getSetting);
                return ($getValue ? $getValue : $default);
            }else{
                return $default;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }



}

