<?php

namespace Database\Factories;

use App\Models\PaymentService;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentSystemFactory extends Factory
{
    protected $model = PaymentService::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->name,
    	    'gateway' => $this->faker->name,
    	    'description' => $this->faker->paragraph,
    	];
    }
}
