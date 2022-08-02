<?php

namespace Database\Factories;

use App\Models\PaymentService;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentServiceFactory extends Factory
{
    protected $model = PaymentService::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'key' => $this->faker->slug(1),
            'description' => $this->faker->paragraph,
        ];
    }
}
