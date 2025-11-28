<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::insert([
            ['name' => 'Computer Science & Engineering', 'code' => 'CSE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electrical & Electronic Engineering', 'code' => 'EEE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bachelor of Business Administration', 'code' => 'BBA', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
