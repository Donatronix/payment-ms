<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\PaymentSettingsSeeder;
use Database\Seeders\PaymentSystemSeeder;
use App\Models\PaymentSystem as PaymentSystemModel;
use App\Models\PaymentSettings;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Run Seeder Class
        $this->call([
            PaymentSystemSeeder::class,
            PaymentSettingsSeeder::class
        ]);

    }
}
