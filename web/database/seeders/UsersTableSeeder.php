<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        // Sumra User
        User::factory()->create([
            'id' => 1,
            'first_name' => 'Sumra',
            'last_name' => 'Net'
        ]);

        // Other users
        for($a = 2; $a < 10; $a++){
            User::factory()->create(['id' => $a]);
        }
    }
}
