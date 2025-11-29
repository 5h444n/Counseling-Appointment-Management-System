<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppointmentSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdvisorSlotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that advisors can access the slots page.
     */
    public function test_advisors_can_access_slots_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/advisor/slots');

        $response->assertOk();
    }

    /**
     * Test that non-advisors (students) cannot access advisor routes.
     */
    public function test_students_cannot_access_advisor_slots_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/advisor/slots');

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access advisor routes.
     */
    public function test_unauthenticated_users_cannot_access_advisor_slots(): void
    {
        $response = $this->get('/advisor/slots');

        $response->assertRedirect('/login');
    }

    /**
     * Test slot generation creates the correct number of slots.
     */
    public function test_slot_generation_creates_correct_number_of_slots(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 2 hours with 30-minute slots = 4 slots
        $this->assertDatabaseCount('appointment_slots', 4);
    }

    /**
     * Test validation rules - date must be today or in the future.
     */
    public function test_validation_rejects_past_dates(): void
    {
        $advisor = User::factory()->advisor()->create();
        $pastDate = Carbon::yesterday()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->from('/advisor/slots')
            ->post('/advisor/slots', [
                'date' => $pastDate,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'duration' => 30,
            ]);

        $response->assertSessionHasErrors('date');
    }

    /**
     * Test validation rules - end time must be after start time.
     */
    public function test_validation_rejects_end_time_before_start_time(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->from('/advisor/slots')
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '11:00',
                'end_time' => '09:00',
                'duration' => 30,
            ]);

        $response->assertSessionHasErrors('end_time');
    }

    /**
     * Test validation rules - duration must be a valid value.
     */
    public function test_validation_rejects_invalid_duration(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->from('/advisor/slots')
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'duration' => 15, // Invalid - must be 20, 30, 45, or 60
            ]);

        $response->assertSessionHasErrors('duration');
    }

    /**
     * Test that advisors can delete their own active slots.
     */
    public function test_advisors_can_delete_their_own_active_slots(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(9, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($advisor)
            ->delete("/advisor/slots/{$slot->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('appointment_slots', ['id' => $slot->id]);
    }

    /**
     * Test that advisors cannot delete other advisors' slots.
     */
    public function test_advisors_cannot_delete_other_advisors_slots(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(9, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($advisor2)
            ->delete("/advisor/slots/{$slot->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('appointment_slots', ['id' => $slot->id]);
    }

    /**
     * Test that advisors cannot delete booked slots.
     */
    public function test_advisors_cannot_delete_booked_slots(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(9, 30),
            'status' => 'blocked', // Represents a booked slot
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($advisor)
            ->delete("/advisor/slots/{$slot->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('appointment_slots', ['id' => $slot->id]);
    }

    /**
     * Test that students cannot create slots.
     */
    public function test_students_cannot_create_slots(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($student)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'duration' => 30,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test that students cannot delete slots.
     */
    public function test_students_cannot_delete_slots(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(9, 0),
            'end_time' => Carbon::tomorrow()->setTime(9, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->delete("/advisor/slots/{$slot->id}");

        $response->assertStatus(403);
    }

    /**
     * Test that no slots are created when time range is too short.
     */
    public function test_returns_error_when_time_range_too_short_for_slots(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->from('/advisor/slots')
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '09:29', // Too short for 30-minute slot
                'duration' => 30,
            ]);

        $response->assertRedirect('/advisor/slots');
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('appointment_slots', 0);
    }
}
