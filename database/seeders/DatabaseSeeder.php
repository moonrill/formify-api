<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name'     => 'user1',
            'email'    => 'user1@webtech.id',
            'password' => Hash::make('password1'),
        ]);
        \App\Models\User::factory()->create([
            'name'     => 'user2',
            'email'    => 'user2@webtech.id',
            'password' => Hash::make('password2'),
        ]);
        \App\Models\User::factory()->create([
            'name'     => 'user3',
            'email'    => 'user3@webtech.id',
            'password' => Hash::make('password3'),
        ]);
    }
}
