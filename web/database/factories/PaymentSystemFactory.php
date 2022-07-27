<?php

namespace Database\Factories;

use App\Models\PaymentSystem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentSystemFactory extends Factory
{
    protected $model = PaymentSystem::class;

    public function definition(): array
    {
    	return [
    	    'name' => $this->faker->name,
    	    'gateway' => $this->faker->name,
    	    'description' => $this->faker->paragraph,
    	];
    }
}
