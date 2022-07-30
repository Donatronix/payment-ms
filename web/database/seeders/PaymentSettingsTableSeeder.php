<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use Illuminate\Database\Seeder;
use App\Models\PaymentService;
use BitPaySDK\Env;
use Illuminate\Support\Str;

class PaymentSettingsTableSeeder extends Seeder
{
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
                    'value' => 'FDpsaV6E9BXq4UXTsEnNBA3GF8hPNoh5eguuGGfDymSd'
                ],
                [
                    'key' => 'bitpay_api_token_payroll',
                    'value' => '2c4LJEPemnFpPSRHiYKHh3qrJA9tV3HPvTjqeyW29pwo'
                ],
                [
                    'key' => 'bitpay_key_path',
                    'value' => 'keys/bitpay.key',
                ],
                [
                    'key' => 'bitpay_private_key_password',
                    'value' => 'A6Zaq4nVkH1iVgcR4pNA94rFTwraRQu9YqcnY7pHHNhS'
                ],
                [
                    'key' => 'bitpay_payment_webhook_url',
                    'value' => 'https://api-dev.ultainfinity.com/v1/payments'
                ],
                [
                    'key' => 'bitpay_redirect_url',
                    'value' => 'https://ultainfinity.com'
                ],
            ],

            'coinbase' => [
                [
                    'key' => 'coinbase_api_key',
                    'value' => 'c4733e03-d198-4437-ab57-daa8deecc08e'
                ],
                [
                    'key' => 'coinbase_webhook_key',
                    'value' => '4c644339-329f-43b1-9c33-4bc7173e9e94'
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
                    'value' => 'ATnX0XE4H-N5QapZkdhDVKHY6xdyITz_N9RPvQkcmDQx1kToXj-30hFWpIJ9VyiHPjf7wVI89YDkZaTK'
                ],
                [
                    'key' => 'paypal_sandbox_client_secret',
                    'value' => 'EMuQ9MOnfOW4k1nhJ63bISM2M9hUK1in5E_VuZDKU2osI54qGd9vkkPvi5YXefV1NkWC9nNbAOue0vvm'
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
                    'value' => ''
                ],
                [
                    'key' => 'paypal_locale',
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
                    'value' => 'whsec_c08967af37c995990d48319f310cfcc1c559f7b7032605119d10a23fae73037f'
                ],
                [
                    'key' => 'stripe_public_key',
                    'value' => 'pk_test_51HcoG6KkrmrXUD8muOcKZTxu2vN6tHSaJrYwFtEcPYFPO7FoJFPPc1by3Uma118tNcCC0SvM8bdWF4b0DEknu3sK00FOYUsbxD'
                ],
                [
                    'key' => 'stripe_secret_key',
                    'value' => 'sk_test_51HcoG6KkrmrXUD8mrFuqkBKnPfFlsfh51HpDQ6gR3eI0uhQfxU24ayd1TqP47UiMGEDRRCB7mC6P6UPsvFcaSMX600IMaVUrZb'
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
            $provider = PaymentService::where('gateway', $gateway)->first();

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
