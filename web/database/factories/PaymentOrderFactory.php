<?php

namespace Database\Factories;

use App\Models\PaymentOrder;
use App\Models\PaymentService;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentOrderFactory extends Factory
{
    protected $model = PaymentOrder::class;

    public function definition(): array
    {
        return [
            'type' => rand(1, 3),
            'gateway' => PaymentService::all()->random()->key,
            'amount' => rand(20, 1200),
            'currency' => $this->faker->randomElement(['usd', 'eur', 'gpd']),
            'user_id' => $this->faker->randomElement(config('settings.default_users_ids')),
            'check_code' => $this->faker->word,
        ];
    }
}
