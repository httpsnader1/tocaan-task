<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {

            User::create([
                'name' => 'Tocaan Task ' . $i,
                'email' => 'tocaan.task' . $i . '@gmail.com',
                'password' => '12345678',
            ]);

        }
    }
}
