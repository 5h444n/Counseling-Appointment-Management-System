<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class AutoCancelAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for the tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that stale pending appointments older than 24 hours are auto-cancelled.
     */
    public function test_stale_pending_appointments_are_cancelled(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create a slot
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create an appointment and manually update its created_at to be 25 hours ago
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test counseling session',
            'status' => 'pending',
            'token' => 'CSE-' . $student->id . '-A',
        ]);
        
        // Manually update created_at timestamp in the database
        $appointment->created_at = Carbon::now()->subHours(25);
        $appointment->save();

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify appointment is cancelled
        $appointment->refresh();
        $this->assertEquals('cancelled', $appointment->status);

        // Verify slot is freed up
        $slot->refresh();
        $this->assertEquals('active', $slot->status);
    }

    /**
     * Test that pending appointments newer than 24 hours are NOT cancelled.
     */
    public function test_recent_pending_appointments_are_not_cancelled(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create a slot
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create a recent appointment
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test counseling session',
            'status' => 'pending',
            'token' => 'CSE-' . $student->id . '-A',
        ]);
        
        // Manually set created_at to 2 hours ago
        $appointment->created_at = Carbon::now()->subHours(2);
        $appointment->save();

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify appointment is still pending
        $appointment->refresh();
        $this->assertEquals('pending', $appointment->status);

        // Verify slot is still blocked
        $slot->refresh();
        $this->assertEquals('blocked', $slot->status);
    }

    /**
     * Test that approved appointments are marked as no-show after start time + 10 minutes.
     */
    public function test_approved_appointments_marked_as_no_show(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create a slot that started 15 minutes ago
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subMinutes(15),
            'end_time' => Carbon::now()->subMinutes(15)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create an approved appointment
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test counseling session',
            'status' => 'approved',
            'token' => 'CSE-' . $student->id . '-A',
        ]);

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify appointment is marked as no-show
        $appointment->refresh();
        $this->assertEquals('no_show', $appointment->status);

        // Verify slot is freed up
        $slot->refresh();
        $this->assertEquals('active', $slot->status);
    }

    /**
     * Test that approved appointments within 10 minutes are NOT marked as no-show.
     */
    public function test_recent_approved_appointments_not_marked_as_no_show(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create a slot that started 5 minutes ago (within grace period)
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subMinutes(5),
            'end_time' => Carbon::now()->subMinutes(5)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create an approved appointment
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test counseling session',
            'status' => 'approved',
            'token' => 'CSE-' . $student->id . '-A',
        ]);

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify appointment is still approved
        $appointment->refresh();
        $this->assertEquals('approved', $appointment->status);

        // Verify slot is still blocked
        $slot->refresh();
        $this->assertEquals('blocked', $slot->status);
    }

    /**
     * Test that completed appointments are not affected by auto-cancel.
     */
    public function test_completed_appointments_are_not_affected(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create a slot
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create a completed appointment
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test counseling session',
            'status' => 'completed',
            'token' => 'CSE-' . $student->id . '-A',
        ]);
        
        // Set created_at to 2 days ago
        $appointment->created_at = Carbon::now()->subDays(2);
        $appointment->save();

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify appointment is still completed
        $appointment->refresh();
        $this->assertEquals('completed', $appointment->status);
    }

    /**
     * Test that multiple appointments are processed correctly.
     */
    public function test_multiple_appointments_processed_correctly(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        // Create multiple slots
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addMinutes(30),
            'status' => 'blocked',
        ]);

        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subMinutes(15),
            'end_time' => Carbon::now()->subMinutes(15)->addMinutes(30),
            'status' => 'blocked',
        ]);

        // Create old pending appointment
        $appointment1 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'purpose' => 'Test counseling session 1',
            'status' => 'pending',
            'token' => 'CSE-' . $student->id . '-A',
        ]);
        
        // Manually set created_at to 25 hours ago
        $appointment1->created_at = Carbon::now()->subHours(25);
        $appointment1->save();

        // Create old approved appointment
        $appointment2 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'purpose' => 'Test counseling session 2',
            'status' => 'approved',
            'token' => 'CSE-' . $student->id . '-B',
        ]);

        // Run the auto-cancel command
        Artisan::call('appointments:autocancel');

        // Verify first appointment is cancelled
        $appointment1->refresh();
        $this->assertEquals('cancelled', $appointment1->status);
        $slot1->refresh();
        $this->assertEquals('active', $slot1->status);

        // Verify second appointment is marked as no-show
        $appointment2->refresh();
        $this->assertEquals('no_show', $appointment2->status);
        $slot2->refresh();
        $this->assertEquals('active', $slot2->status);
    }
}
