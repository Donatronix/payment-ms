<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sumra User
        factory(User::class)->create([
            'id' => 1,
            'first_name' => 'Sumra',
            'last_name' => 'Net'
        ]);

        // Other users
        for($a = 2; $a < 10; $a++){
            factory(User::class)->create(['id' => $a]);
        }
    }
}
