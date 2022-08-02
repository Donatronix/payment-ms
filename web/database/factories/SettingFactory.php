<?php

namespace Database\Factories;

use App\Models\PaymentService;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
    	return [
    	    'key' => $this->faker->slug(1),
            'value' => '',
            'payment_service_id' => PaymentService::all()->random()->id
    	];
    }
}
