<?php

namespace Database\Seeders;

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
        \App\Models\PaymentOrder::factory()->count(10)->create();
    }
}
