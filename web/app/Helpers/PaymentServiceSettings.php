<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Str;

class PaymentServiceSettings
{
    // Get payment service settings
    public static function get($service = null): mixed
    {
        try {
            $settings = Setting::byService($service)->get();

            $list = [];
            foreach ($settings as $row) {
                $key = Str::replaceFirst("{$service}_", '', $row->key);

                $list[$key] = $row->value ?? null;
            }

            return (object) $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
