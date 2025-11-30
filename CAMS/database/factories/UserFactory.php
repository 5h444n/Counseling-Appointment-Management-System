<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Department;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Default to student, but we can override this
            'role' => 'student',
            'university_id' => fake()->unique()->numerify('011######'), // Generates like 011238491
            'department_id' => fn () => Department::inRandomOrder()->first()?->id,
        ];
    }

    /**
     * State to create an Advisor specifically
     */
    public function advisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'advisor',
            'university_id' => fake()->unique()->numerify('T-####'), // Generates like T-4092
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
