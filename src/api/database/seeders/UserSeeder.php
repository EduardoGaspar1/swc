<?php

namespace Database\Seeders;

use App\Models\Company;
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
        $company = Company::query()->firstOrCreate(
            ['codigo' => 'demo'],
            ['nombre' => 'Empresa Demo'],
        );

        User::factory()->create([
            'name' => 'Eduardo Rohden Silva',
            'email' => 'eduardorohdengaspar@gmail.com',
            'password' => Hash::make('secret'),
            'role' => User::ROLE_ADMIN,
            'company_id' => $company->id,
        ]);

        User::factory()->create([
            'name' => 'Alex Luiz',
            'email' => 'im2026237@imesmaresme.com',
            'password' => Hash::make('secret'),
            'role' => User::ROLE_USER,
            'company_id' => $company->id,
        ]);
    }
}
