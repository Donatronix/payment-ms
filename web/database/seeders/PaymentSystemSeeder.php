<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentSystem;

class PaymentSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'Bitpay', 'gateway' => 'bitpay', 'description' => 'Bitpay payment gateway provider', 'new_status' => 1],
            ['name' => 'Coinbase', 'gateway' => 'coinbase', 'description' => 'Coinbase payment gateway provider', 'new_status' => 1],
            ['name' => 'Openpayd', 'gateway' => 'openpayd', 'description' => 'Openpayd payment gateway provider', 'new_status' => 1],
            ['name' => 'Paypal', 'gateway' => 'paypal', 'description' => 'Paypal payment gateway provider', 'new_status' => 1],
            ['name' => 'Stripe', 'gateway' => 'stripe', 'description' => 'Stripe payment gateway provider', 'new_status' => 1],

        ];
        foreach ($data as $key => $value) {
            PaymentSystem::create($value);
        }
    }
}
