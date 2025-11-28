<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create an Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create an Advisor (Teacher)
        User::create([
            'name' => 'Dr. Advisor',
            'email' => 'advisor@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'advisor',
            'university_id' => 'T-5050',
            'department_id' => 1, // CSE
        ]);

        // 3. Create a Student
        User::create([
            'name' => 'Student User',
            'email' => 'student@uiu.ac.bd',
            'password' => Hash::make('password'),
            'role' => 'student',
            'university_id' => '01123456',
            'department_id' => 1, // CSE
        ]);
    }
}
