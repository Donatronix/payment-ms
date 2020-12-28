<?php

use App\Models\Balance;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Currency;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sumra User
        $sumraUser = factory(User::class)->create([
            'first_name' => 'Sumra',
            'last_name' => 'Net'
        ]);

        // Other users
        $users = factory(User::class, 10)->create();
    }
}
