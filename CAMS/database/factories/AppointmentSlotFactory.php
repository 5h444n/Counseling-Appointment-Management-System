<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppointmentSlot>
 */
class AppointmentSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::tomorrow()->addHours(fake()->numberBetween(8, 16));
        
        return [
            'advisor_id' => User::factory()->advisor(),
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes(30),
            'is_recurring' => false,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the slot is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
        ]);
    }

    /**
     * Indicate that the slot is in the past.
     */
    public function past(): static
    {
        $startTime = Carbon::yesterday()->addHours(fake()->numberBetween(8, 16));
        
        return $this->state(fn (array $attributes) => [
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes(30),
        ]);
    }
}
