<?php

namespace Database\Seeders;

use App\Models\PaymentOrder;
use Illuminate\Database\Seeder;

class PaymentOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentOrder::factory()->count(10)->create();
    }
}
