<?php

namespace Database\Seeders;

use App\Models\PaymentService;
use Illuminate\Database\Seeder;

class PaymentServicesTableSeeder extends Seeder
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
                'status' => true,
            ],
            'coinbase' => [
                'status' => false,
            ],
            'openpayd' => [
                'status' => false,
            ],
            'paypal' => [
                'status' => false,
            ],
            'stripe' => [
                'status' => true,
                'amount_min' => 250,
                'amount_max' => 1000
            ],
            'nuvei' => [
                'status' => false,
            ],
            'network-bitcoin' => [
                'status' => true,
            ],
            'network-bnb-beacon-chain' => [
                'status' => true,
            ],
            'network-bnb-smart-chain' => [
                'status' => true,
            ],
            'network-cardano' => [
                'status' => true,
            ],
            'network-ethereum' => [
                'status' => true,
            ],
            'network-solana' => [
                'status' => true,
            ]
        ];

        $services = PaymentService::catalog();

        foreach ($services as $key => $value) {
            PaymentService::create(array_merge($value, $data[$key]));
        }
    }
}
