<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Eduardo Rohden Silva',
            'email' => 'im2026238@imesmaresme.com',
        ]);

        User::factory()->create([
            'name' => 'Alex Luiz',
            'email' => 'im2026237@imesmaresme.com',
        ]);
    }
}
