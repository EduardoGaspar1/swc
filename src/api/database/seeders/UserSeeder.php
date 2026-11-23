<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Eduardo Rohden Silva',
            'email' => 'eduardorohdengaspar@gmail.com',
            'password' => Hash::make('secret'),
        ]);

        User::factory()->create([
            'name' => 'Alex Luiz',
            'email' => 'im2026237@imesmaresme.com',
            'password' => Hash::make('secret'),
        ]);
    }
}
