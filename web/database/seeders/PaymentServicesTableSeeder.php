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
            'bitcoin-network' => [
                'status' => true,
            ],
            'bnb-beacon-chain-network' => [
                'status' => true,
            ],
            'bnb-smart-chain-network' => [
                'status' => true,
            ],
            'cardano-network' => [
                'status' => true,
            ],
            'ethereum-network' => [
                'status' => true,
            ],
            'solana-network' => [
                'status' => true,
            ]
        ];

        $services = PaymentService::catalog();

        foreach ($services as $key => $value) {
            PaymentService::create(array_merge($value, $data[$key]));
        }
    }
}
