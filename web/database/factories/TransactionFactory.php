<?php

namespace Database\Factories;

use App\Models\PaymentOrder;
use App\Models\PaymentService;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'gateway' => PaymentService::all()->random()->gateway,
            'trx_id' => uniqid('PAY_INT_ULTRA'),
            'payment_order_id' => PaymentOrder::all()->random()->id,
            'status' => $this->faker->boolean
        ];
    }
}
