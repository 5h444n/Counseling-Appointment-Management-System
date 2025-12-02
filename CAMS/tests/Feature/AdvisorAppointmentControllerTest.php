<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdvisorAppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for the tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that advisors can access their dashboard.
     */
    public function test_advisors_can_access_dashboard(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/advisor/dashboard');

        $response->assertOk();
    }

    /**
     * Test that students cannot access advisor dashboard.
     */
    public function test_students_cannot_access_advisor_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/advisor/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access advisor dashboard.
     */
    public function test_unauthenticated_users_cannot_access_advisor_dashboard(): void
    {
        $response = $this->get('/advisor/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test that pending appointments are displayed on dashboard.
     */
    public function test_pending_appointments_are_displayed_on_dashboard(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->get('/advisor/dashboard');

        $response->assertOk();
        $response->assertSee('Academic advising');
    }

    /**
     * Test that advisors can approve pending appointments.
     */
    public function test_advisors_can_approve_pending_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->patch("/advisor/appointments/{$appointment->id}", [
                'status' => 'approved',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Appointment Confirmed!');

        $appointment->refresh();
        $this->assertEquals('approved', $appointment->status);
    }

    /**
     * Test that advisors can decline pending appointments.
     */
    public function test_advisors_can_decline_pending_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->patch("/advisor/appointments/{$appointment->id}", [
                'status' => 'declined',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Request Declined.');

        $appointment->refresh();
        $this->assertEquals('declined', $appointment->status);
    }

    /**
     * Test that advisors cannot update other advisors' appointments.
     */
    public function test_advisors_cannot_update_other_advisors_appointments(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($advisor2)
            ->patch("/advisor/appointments/{$appointment->id}", [
                'status' => 'approved',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Unauthorized action.');

        $appointment->refresh();
        $this->assertEquals('pending', $appointment->status);
    }

    /**
     * Test validation requires valid status.
     */
    public function test_validation_requires_valid_status(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->from('/advisor/dashboard')
            ->patch("/advisor/appointments/{$appointment->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors('status');
    }

    /**
     * Test that non-existent appointment returns 404.
     */
    public function test_updating_nonexistent_appointment_returns_404(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->patch("/advisor/appointments/99999", [
                'status' => 'approved',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test that students cannot update appointment status.
     */
    public function test_students_cannot_update_appointment_status(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->patch("/advisor/appointments/{$appointment->id}", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }
}
