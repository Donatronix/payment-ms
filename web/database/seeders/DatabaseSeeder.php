<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Common seeds
        $this->call([
            PaymentServicesTableSeeder::class,
            SettingsTableSeeder::class
        ]);

        // Seeds for local and staging
        if (App::environment(['local', 'staging'])) {
            $this->call([
                PaymentOrderSeeder::class
            ]);
        }

        // Seeds for production
        if (App::environment('production')) {
            //
        }
    }
}
