<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Nagarro Manager',
            'email' => 'nagarro@example.com',
        ]);

        User::factory()->create([
            'name' => 'Arun Developer',
            'email' => 'arun@example.com',
        ]);

        User::factory()->create([
            'name' => 'Testing User',
            'email' => 'testing@example.com',
        ]);
    }
}
