<?php

namespace App\Helpers;

use App\Models\StripePaymentGatewaySetup;
use App\Models\PaypalPaymentGatewaySetup;


class PaymentGatewaySettings
{

    //Get Stripe Gateway settings
    public static function stripe_settings($getSetting = 'gateway_name', $status = 1)
    {
            try {
                $status     = (is_int($status) ? $status : 1);
                $getSetting = strtolower($getSetting);
               return StripePaymentGatewaySetup::where('status', $status)->value($getSetting);
            } catch (\Throwable $e) {
                return false;
            }
    }

    //Get Stripe Gateway settings
    public static function paypal_settings($getSetting = 'gateway_name', $status = 1)
    {
            try {
                $status     = (is_int($status) ? $status : 1);
                $getSetting = strtolower($getSetting);
               return PaypalPaymentGatewaySetup::where('status', $status)->value($getSetting);
            } catch (\Throwable $e) {
                return false;
            }
    }



}

