<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AppointmentSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->create(['role' => 'student']),
            'slot_id' => AppointmentSlot::factory(),
            'token' => $this->generateUniqueToken(),
            'purpose' => fake()->sentence(10),
            'status' => 'pending',
            'meeting_notes' => null,
        ];
    }

    /**
     * Generate a unique appointment token.
     */
    private function generateUniqueToken(): string
    {
        $serial = chr(random_int(65, 90));
        return 'TEST-' . fake()->unique()->numberBetween(1000, 9999) . '-' . $serial;
    }

    /**
     * Indicate that the appointment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the appointment is declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'declined',
        ]);
    }
}
