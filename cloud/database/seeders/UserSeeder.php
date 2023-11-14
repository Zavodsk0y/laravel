<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'email' => 'user1@test.ru',
            'password' => bcrypt('Qa1'),
            'first_name' => 'name',
            'last_name' => 'last_name'
        ]);

        User::create([
            'email' => 'user2@test.ru',
            'password' => bcrypt('As2'),
            'first_name' => 'name',
            'last_name' => 'last_name'
        ]);
    }
}
