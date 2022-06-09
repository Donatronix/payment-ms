<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentSettings;
use App\Models\PaymentSystem;
use BitPaySDK\Env;

class PaymentSettingsSeeder extends Seeder
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
        $bitpay     = PaymentSystem::where('gateway', 'bitpay')->value('id');
        $coinbase   = PaymentSystem::where('gateway', 'coinbase')->value('id');
        $openpayd   = PaymentSystem::where('gateway', 'openpayd')->value('id');
        $paypal     = PaymentSystem::where('gateway', 'paypal')->value('id');
        $stripe     = PaymentSystem::where('gateway', 'stripe')->value('id');


        $data = [
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_environment', 'setting_value' => Env::Test],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_api_token_merchant', 'setting_value' => null],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_api_token_payroll', 'setting_value' => null],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_key_path', 'setting_value' => null],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_private_key_password', 'setting_value' => null],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_payment_webhook_url', 'setting_value' => 'https://sumra.net/payment/'],
            ['payment_system_id' =>  $bitpay, 'setting_key' => 'bitpay_redirect_url', 'setting_value' => 'https://sumra.net/'],

            ['payment_system_id' => $coinbase, 'setting_key' => 'coinbase_api_key', 'setting_value' => null],
            ['payment_system_id' => $coinbase, 'setting_key' => 'coinbase_webhook_key', 'setting_value' => null],
            ['payment_system_id' => $coinbase, 'setting_key' => 'coinbase_redirect_url', 'setting_value' => 'https://sumra.net/'],
            ['payment_system_id' => $coinbase, 'setting_key' => 'coinbase_cancel_url', 'setting_value' => 'https://sumra.net/'],

            ['payment_system_id' => $openpayd, 'setting_key' => 'openpayd_username', 'setting_value' => 'USERNAME'],
            ['payment_system_id' => $openpayd, 'setting_key' => 'openpayd_password', 'setting_value' => 'PASSWORD'],
            ['payment_system_id' => $openpayd, 'setting_key' => 'openpayd_url', 'setting_value' => 'https://sandbox.openpayd.com/api/'],
            ['payment_system_id' => $openpayd, 'setting_key' => 'openpayd_public_key_path', 'setting_value' => 'keys/openpayd.key'],

            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_mode', 'setting_value' => 'sandbox'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_sandbox_client_id', 'setting_value' => 'sandbox'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_sandbox_client_secret', 'setting_value' => 'sandbox'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_live_client_id', 'setting_value' => null],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_live_client_secret', 'setting_value' => null],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_api_url', 'setting_value' => 'https://api-m.sandbox.paypal.com'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_app_id', 'setting_value' => 'APP-80W284485P519543T'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_payment_action', 'setting_value' => 'Sale'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_currency', 'setting_value' => 'USD'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_notify_url', 'setting_value' => 'sandbox'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_local', 'setting_value' => 'en_US'],
            ['payment_system_id' => $paypal, 'setting_key' => 'paypal_validate_ssl', 'setting_value' => true],

            ['payment_system_id' => $stripe, 'setting_key' => 'stripe_webhook_secret', 'setting_value' => true],
            ['payment_system_id' => $stripe, 'setting_key' => 'stripe_public_key', 'setting_value' => true],
            ['payment_system_id' => $stripe, 'setting_key' => 'stripe_secret_key', 'setting_value' => true],

        ];
        foreach ($data as $key => $value) {
            PaymentSettings::create($value);
        }
    }
}
