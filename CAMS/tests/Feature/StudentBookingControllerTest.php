<?php

namespace Tests\Feature;

use App\Events\SlotFreedUp;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create a user via DB to avoid fillable/factory issues.
     */
    private function createUser(string $role, array $overrides = []): User
    {
        $data = array_merge([
            'name' => $overrides['name'] ?? ($role . ' User'),
            'email' => $overrides['email'] ?? (uniqid($role.'_') . '@example.com'),
            'password' => Hash::make('password'),
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        $id = DB::table('users')->insertGetId($data);

        return User::query()->findOrFail($id);
    }

    /**
     * Helper: create an appointment slot via DB and return model.
     */
    private function createSlot(int $advisorId, \Carbon\Carbon $startTime, \Carbon\Carbon $endTime, string $status = 'blocked'): AppointmentSlot
    {
        $slotId = DB::table('appointment_slots')->insertGetId([
            'advisor_id' => $advisorId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return AppointmentSlot::query()->findOrFail($slotId);
    }

    /**
     * Helper: create appointment via DB and return model.
     */
    private function createAppointment(int $studentId, int $advisorId, int $slotId, string $status = 'approved'): Appointment
    {
        $appointmentId = DB::table('appointments')->insertGetId([
            'student_id' => $studentId,
            'advisor_id' => $advisorId,
            'appointment_slot_id' => $slotId,
            'purpose' => 'Test purpose',
            'token' => 'TST-' . uniqid(),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Appointment::query()->findOrFail($appointmentId);
    }

    public function test_student_can_cancel_own_upcoming_appointment_and_slot_becomes_active_and_waitlist_event_fires(): void
    {
        Event::fake([SlotFreedUp::class]);

        $student = $this->createUser('student');
        $advisor = $this->createUser('advisor');

        $slot = $this->createSlot(
            advisorId: $advisor->id,
            startTime: now()->addDay(),
            endTime: now()->addDay()->addMinutes(30),
            status: 'blocked'
        );

        $appointment = $this->createAppointment(
            studentId: $student->id,
            advisorId: $advisor->id,
            slotId: $slot->id,
            status: 'approved'
        );

        $response = $this->actingAs($student)
            ->patch(route('student.appointments.cancel', $appointment->id));

        $response->assertRedirect();

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

    public function test_student_cannot_cancel_someone_elses_appointment(): void
    {
        $studentA = $this->createUser('student');
        $studentB = $this->createUser('student');
        $advisor = $this->createUser('advisor');

        $slot = $this->createSlot(
            advisorId: $advisor->id,
            startTime: now()->addDay(),
            endTime: now()->addDay()->addMinutes(30),
            status: 'blocked'
        );

        $appointment = $this->createAppointment(
            studentId: $studentA->id,
            advisorId: $advisor->id,
            slotId: $slot->id,
            status: 'approved'
        );

        $this->actingAs($studentB)
            ->patch(route('student.appointments.cancel', $appointment->id))
            ->assertStatus(403);

        // Ensure no changes happened
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'blocked',
        ]);
    }

    public function test_student_cannot_cancel_past_appointment(): void
    {
        $student = $this->createUser('student');
        $advisor = $this->createUser('advisor');

        $slot = $this->createSlot(
            advisorId: $advisor->id,
            startTime: now()->subDay(),
            endTime: now()->subDay()->addMinutes(30),
            status: 'blocked'
        );

        $appointment = $this->createAppointment(
            studentId: $student->id,
            advisorId: $advisor->id,
            slotId: $slot->id,
            status: 'approved'
        );

        $this->actingAs($student)
            ->patch(route('student.appointments.cancel', $appointment->id))
            ->assertStatus(422);

        // Ensure no changes happened
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'blocked',
        ]);
    }

    public function test_history_tab_includes_cancelled_appointment(): void
    {
        $student = $this->createUser('student');
        $advisor = $this->createUser('advisor');

        // Future slot but appointment is cancelled -> should be in history by status rule
        $slot = $this->createSlot(
            advisorId: $advisor->id,
            startTime: now()->addDay(),
            endTime: now()->addDay()->addMinutes(30),
            status: 'active'
        );

        $appointment = $this->createAppointment(
            studentId: $student->id,
            advisorId: $advisor->id,
            slotId: $slot->id,
            status: 'cancelled'
        );

        $response = $this->actingAs($student)
            ->get('/student/my-appointments?tab=history');

        $response->assertStatus(200);

        // Assert the view received the history collection and includes our appointment
        $response->assertViewHas('historyAppointments', function ($history) use ($appointment) {
            return $history->contains('id', $appointment->id);
        });
    }
}
