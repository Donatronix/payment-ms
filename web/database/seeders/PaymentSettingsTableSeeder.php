<?php

namespace Database\Seeders;

use App\Models\PaymentSetting as PaymentSettingsModel;
use Illuminate\Database\Seeder;
use App\Models\PaymentSetting;
use App\Models\PaymentSystem;
use BitPaySDK\Env;
use Illuminate\Support\Str;

class PaymentSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
   /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'bitpay' => [
                [
                    'key' => 'bitpay_environment',
                    'value' => Env::Test
                ],
                [
                    'key' => 'bitpay_api_token_merchant',
                    'value' => null
                ],
                [
                    'key' => 'bitpay_api_token_payroll',
                    'value' => null
                ],
                [
                    'key' => 'bitpay_key_path',
                    'value' => null
                ],
                [
                    'key' => 'bitpay_private_key_password',
                    'value' => null
                ],
                [
                    'key' => 'bitpay_payment_webhook_url',
                    'value' => 'https://sumra.net/payment/'
                ],
                [
                    'key' => 'bitpay_redirect_url',
                    'value' => 'https://sumra.net/'
                ],
            ],

            'coinbase' => [
                [
                    'key' => 'coinbase_api_key',
                    'value' => null
                ],
                [
                    'key' => 'coinbase_webhook_key',
                    'value' => null
                ],
                [
                    'key' => 'coinbase_redirect_url',
                    'value' => 'https://sumra.net/'
                ],
                [
                    'key' => 'coinbase_cancel_url',
                    'value' => 'https://sumra.net/'
                ],
            ],

            'openpayd' => [
                [
                    'key' => 'openpayd_username',
                    'value' => 'USERNAME'
                ],
                [
                    'key' => 'openpayd_password',
                    'value' => 'PASSWORD'
                ],
                [
                    'key' => 'openpayd_url',
                    'value' => 'https://sandbox.openpayd.com/api/'
                ],
                [
                    'key' => 'openpayd_public_key_path',
                    'value' => 'keys/openpayd.key'
                ],
            ],

            'paypal' => [
                [
                    'key' => 'paypal_mode',
                    'value' => 'sandbox'
                ],
                [
                    'key' => 'paypal_sandbox_client_id',
                    'value' => 'sandbox'
                ],
                [
                    'key' => 'paypal_sandbox_client_secret',
                    'value' => 'sandbox'
                ],
                [
                    'key' => 'paypal_live_client_id',
                    'value' => null
                ],
                [
                    'key' => 'paypal_live_client_secret',
                    'value' => null
                ],
                [
                    'key' => 'paypal_sandbox_api_url',
                    'value' => 'https://api-m.sandbox.paypal.com'
                ],
                [
                    'key' => 'paypal_live_api_url',
                    'value' => 'https://api-m.paypal.com'
                ],
                [
                    'key' => 'paypal_sandbox_app_id',
                    'value' => 'APP-80W284485P519543T'
                ],
                [
                    'key' => 'paypal_live_app_id',
                    'value' => ''
                ],
                [
                    'key' => 'paypal_payment_action',
                    'value' => 'Sale'
                ],
                [
                    'key' => 'paypal_currency',
                    'value' => 'USD'
                ],
                [
                    'key' => 'paypal_notify_url',
                    'value' => 'sandbox'
                ],
                [
                    'key' => 'paypal_local',
                    'value' => 'en_US'
                ],
                [
                    'key' => 'paypal_validate_ssl',
                    'value' => true
                ],
            ],

            'stripe' => [
                [
                    'key' => 'stripe_webhook_secret',
                    'value' => null
                ],
                [
                    'key' => 'stripe_public_key',
                    'value' => null
                ],
                [
                    'key' => 'stripe_secret_key',
                    'value' => null
                ],
                [
                    'key' => 'payments_redirect_url',
                    'value' => 'https://e0ee1c07505d.ngrok.io/'
                ],
                [
                    'key' => 'payments_webhook_url',
                    'value' => 'https://e0ee1c07505d.ngrok.io/v1/payments/webhooks'
                ],
            ]
        ];

        foreach ($data as $gateway => $values) {
            // Get provider by key
            $provider = PaymentSystem::where('gateway', $gateway)->first();

            // Loop all values
            foreach ($values as $key => $value){
                $setting = new PaymentSetting();
                $setting->fill($value);
                $setting->id = (string)Str::orderedUuid();
                $provider->payment_settings()->save($setting);
            }
        }
    }
}
