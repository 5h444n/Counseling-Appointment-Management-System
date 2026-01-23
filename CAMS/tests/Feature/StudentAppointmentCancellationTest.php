<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Waitlist;
use App\Events\SlotFreedUp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class StudentAppointmentCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that a student can cancel their pending appointment.
     */
    public function test_student_can_cancel_pending_appointment(): void
    {
        Event::fake([SlotFreedUp::class]);

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
            'purpose' => 'Test cancellation',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'active',
        ]);

        Event::assertDispatched(SlotFreedUp::class);
    }

    /**
     * Test that a student can cancel their approved appointment.
     */
    public function test_student_can_cancel_approved_appointment(): void
    {
        Event::fake([SlotFreedUp::class]);

        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(14, 0),
            'end_time' => Carbon::tomorrow()->setTime(14, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Approved appointment cancellation test',
            'status' => 'approved',
            'token' => 'CSE-1-B',
        ]);

        $response = $this
            ->actingAs($student)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);

        Event::assertDispatched(SlotFreedUp::class);
    }

    /**
     * Test that a student cannot cancel a past appointment.
     */
    public function test_student_cannot_cancel_past_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::yesterday()->setTime(10, 0),
            'end_time' => Carbon::yesterday()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Past appointment test',
            'status' => 'approved',
            'token' => 'CSE-1-C',
        ]);

        $response = $this
            ->actingAs($student)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved', // Status unchanged
        ]);
    }

    /**
     * Test that a student cannot cancel an already declined appointment.
     */
    public function test_student_cannot_cancel_declined_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Declined appointment test',
            'status' => 'declined',
            'token' => 'CSE-1-D',
        ]);

        $response = $this
            ->actingAs($student)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test that a student cannot cancel another student's appointment.
     */
    public function test_student_cannot_cancel_other_students_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(15, 0),
            'end_time' => Carbon::tomorrow()->setTime(15, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student1->id,
            'slot_id' => $slot->id,
            'purpose' => 'Student 1 appointment',
            'status' => 'approved',
            'token' => 'CSE-1-E',
        ]);

        // Student 2 tries to cancel Student 1's appointment
        $response = $this
            ->actingAs($student2)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertStatus(404); // Not found due to where clause

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved', // Status unchanged
        ]);
    }

    /**
     * Test that slot becomes active and waitlist is notified when appointment is cancelled.
     */
    public function test_waitlist_notified_when_appointment_cancelled(): void
    {
        Event::fake([SlotFreedUp::class]);

        $advisor = User::factory()->advisor()->create();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(16, 0),
            'end_time' => Carbon::tomorrow()->setTime(16, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student1->id,
            'slot_id' => $slot->id,
            'purpose' => 'Waitlist test appointment',
            'status' => 'approved',
            'token' => 'CSE-1-F',
        ]);

        // Student 2 is on the waitlist
        Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student2->id,
        ]);

        $response = $this
            ->actingAs($student1)
            ->post("/student/appointments/{$appointment->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify SlotFreedUp event was dispatched (which triggers waitlist notification)
        Event::assertDispatched(SlotFreedUp::class, function ($event) use ($slot) {
            return $event->slot->id === $slot->id;
        });
    }

    /**
     * Test tabs work correctly - upcoming tab shows future pending/approved appointments.
     */
    public function test_upcoming_tab_shows_only_future_pending_and_approved_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        // Future pending slot
        $futureSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $futureAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $futureSlot->id,
            'purpose' => 'Future appointment',
            'status' => 'pending',
            'token' => 'CSE-1-G',
        ]);

        // Past slot
        $pastSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::yesterday()->setTime(10, 0),
            'end_time' => Carbon::yesterday()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $pastAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $pastSlot->id,
            'purpose' => 'Past appointment',
            'status' => 'approved',
            'token' => 'CSE-1-H',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments?tab=upcoming');

        $response->assertOk();
        $appointments = $response->viewData('appointments');

        // Should only contain future appointment
        $this->assertEquals(1, $appointments->count());
        $this->assertEquals($futureAppointment->id, $appointments->first()->id);
    }

    /**
     * Test tabs work correctly - past tab shows past or cancelled appointments.
     */
    public function test_past_tab_shows_past_and_cancelled_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        // Cancelled future appointment
        $futureSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $cancelledAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $futureSlot->id,
            'purpose' => 'Cancelled appointment',
            'status' => 'cancelled',
            'token' => 'CSE-1-I',
        ]);

        // Past slot
        $pastSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::yesterday()->setTime(10, 0),
            'end_time' => Carbon::yesterday()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $pastAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $pastSlot->id,
            'purpose' => 'Past appointment',
            'status' => 'completed',
            'token' => 'CSE-1-J',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments?tab=past');

        $response->assertOk();
        $appointments = $response->viewData('appointments');

        // Should contain both cancelled and past appointments
        $this->assertEquals(2, $appointments->count());
        $appointmentIds = $appointments->pluck('id')->toArray();
        $this->assertContains($cancelledAppointment->id, $appointmentIds);
        $this->assertContains($pastAppointment->id, $appointmentIds);
    }

    /**
     * Test that unauthenticated users cannot cancel appointments.
     */
    public function test_unauthenticated_users_cannot_cancel_appointments(): void
    {
        $response = $this->post('/student/appointments/1/cancel');
        $response->assertRedirect('/login');
    }

    /**
     * Test that advisors cannot access the student cancel route.
     */
    public function test_advisors_cannot_cancel_via_student_route(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->post('/student/appointments/1/cancel');

        $response->assertStatus(403);
    }
}
