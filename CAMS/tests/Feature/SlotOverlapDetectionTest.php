<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AppointmentSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class SlotOverlapDetectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that overlapping slots are not created.
     */
    public function test_overlapping_slots_are_not_created(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create first slot
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse("$futureDate 09:00"),
            'end_time' => Carbon::parse("$futureDate 09:30"),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // Try to create overlapping slots (9:15 to 9:45 overlaps with 9:00-9:30)
        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:15',
                'end_time' => '09:45',
                'duration' => 30,
            ]);

        // Should return error because slot overlaps
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Should still only have 1 slot
        $this->assertDatabaseCount('appointment_slots', 1);
    }

    /**
     * Test that adjacent slots are created correctly.
     */
    public function test_adjacent_slots_are_created_correctly(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create first slot
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse("$futureDate 09:00"),
            'end_time' => Carbon::parse("$futureDate 09:30"),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // Create adjacent slot (9:30 - 10:00)
        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:30',
                'end_time' => '10:00',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Should now have 2 slots
        $this->assertDatabaseCount('appointment_slots', 2);
    }

    /**
     * Test creating multiple slots in a time range.
     */
    public function test_creating_multiple_slots_in_time_range(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'duration' => 30,
            ]);

        // 3 hours / 30 minutes = 6 slots
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('appointment_slots', 6);
    }

    /**
     * Test creating slots with 20-minute duration.
     */
    public function test_creating_slots_with_20_minute_duration(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '10:00',
                'duration' => 20,
            ]);

        // 60 minutes / 20 minutes = 3 slots
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('appointment_slots', 3);
    }

    /**
     * Test creating slots with 45-minute duration.
     */
    public function test_creating_slots_with_45_minute_duration(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '11:15',
                'duration' => 45,
            ]);

        // 135 minutes / 45 minutes = 3 slots
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('appointment_slots', 3);
    }

    /**
     * Test creating slots with 60-minute duration.
     */
    public function test_creating_slots_with_60_minute_duration(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'duration' => 60,
            ]);

        // 3 hours / 60 minutes = 3 slots
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('appointment_slots', 3);
    }

    /**
     * Test partial overlap at the start is handled.
     */
    public function test_partial_overlap_at_start_is_handled(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create slot from 9:30 to 10:00
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse("$futureDate 09:30"),
            'end_time' => Carbon::parse("$futureDate 10:00"),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // Try to create slots from 9:00 to 10:00 with 30-min duration
        // First slot (9:00-9:30) should be created, second (9:30-10:00) should be skipped
        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '10:00',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Should have 2 slots total (1 existing + 1 new)
        $this->assertDatabaseCount('appointment_slots', 2);
    }

    /**
     * Test partial overlap at the end is handled.
     */
    public function test_partial_overlap_at_end_is_handled(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create slot from 9:00 to 9:30
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse("$futureDate 09:00"),
            'end_time' => Carbon::parse("$futureDate 09:30"),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // Try to create slots from 9:00 to 10:00 with 30-min duration
        // First slot (9:00-9:30) should be skipped, second (9:30-10:00) should be created
        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '10:00',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Should have 2 slots total (1 existing + 1 new)
        $this->assertDatabaseCount('appointment_slots', 2);
    }

    /**
     * Test slots for different advisors don't affect each other.
     */
    public function test_slots_for_different_advisors_dont_affect_each_other(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create slot for advisor1
        AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::parse("$futureDate 09:00"),
            'end_time' => Carbon::parse("$futureDate 09:30"),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // advisor2 should be able to create slot at the same time
        $response = $this
            ->actingAs($advisor2)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '09:30',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Should have 2 slots total (1 for each advisor)
        $this->assertDatabaseCount('appointment_slots', 2);
    }

    /**
     * Test creating slots for today works.
     */
    public function test_creating_slots_for_today_works(): void
    {
        $advisor = User::factory()->advisor()->create();
        $today = Carbon::now()->format('Y-m-d');
        
        // Use a time that's definitely in the future
        $futureHour = Carbon::now()->addHours(2)->format('H:00');
        $futureHourEnd = Carbon::now()->addHours(3)->format('H:00');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $today,
                'start_time' => $futureHour,
                'end_time' => $futureHourEnd,
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test that blocked slots are not considered for overlap detection.
     */
    public function test_blocked_slots_are_not_considered_for_overlap(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        // Create a blocked slot
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse("$futureDate 09:00"),
            'end_time' => Carbon::parse("$futureDate 09:30"),
            'status' => 'blocked', // Not active
            'is_recurring' => false,
        ]);

        // Try to create slot at the same time - blocked slots shouldn't block new ones
        // according to the controller logic which only checks for 'active' status
        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '09:00',
                'end_time' => '09:30',
                'duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Should have 2 slots total
        $this->assertDatabaseCount('appointment_slots', 2);
    }

    /**
     * Test slot times are correctly stored.
     */
    public function test_slot_times_are_correctly_stored(): void
    {
        $advisor = User::factory()->advisor()->create();
        $futureDate = Carbon::tomorrow()->format('Y-m-d');

        $response = $this
            ->actingAs($advisor)
            ->post('/advisor/slots', [
                'date' => $futureDate,
                'start_time' => '14:30',
                'end_time' => '15:00',
                'duration' => 30,
            ]);

        $slot = AppointmentSlot::first();
        
        $this->assertEquals('14', $slot->start_time->format('H'));
        $this->assertEquals('30', $slot->start_time->format('i'));
        $this->assertEquals('15', $slot->end_time->format('H'));
        $this->assertEquals('00', $slot->end_time->format('i'));
    }
}
