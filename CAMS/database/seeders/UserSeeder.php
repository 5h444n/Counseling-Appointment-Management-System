<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Specific Users for Login Testing (Keep these!)
        // Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'university_id' => 'ADMIN-01',
            'department_id' => 1,
        ]);

        // Specific Student
        User::create([
            'name' => 'Shaan Student',
            'email' => 'shaan@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'student',
            'university_id' => '011223001',
            'department_id' => 1,
        ]);

        // Specific Advisor
        User::create([
            'name' => 'Dr. Nabila Advisor',
            'email' => 'nabila@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'advisor',
            'university_id' => 'T-9090',
            'department_id' => 1,
        ]);

        // 2. Generate Bulk Fake Data (The "Factory" Magic)

        // Create 10 Random Advisors
        User::factory()->count(10)->advisor()->create();

        // Create 20 Random Students
        User::factory()->count(20)->create();
    }
}
