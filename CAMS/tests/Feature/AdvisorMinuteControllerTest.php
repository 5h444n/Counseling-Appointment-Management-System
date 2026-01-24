<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Minute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdvisorMinuteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_advisor_can_access_create_minute_page(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/appointments/' . $appointment->id . '/note');

        $response->assertOk();
        $response->assertViewIs('advisor.minutes.create');
        $response->assertViewHas('appointment');
        $response->assertViewHas('history');
    }

    public function test_advisor_cannot_access_create_minute_for_others_appointment(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor2)->get('/advisor/appointments/' . $appointment->id . '/note');

        $response->assertForbidden();
    }

    public function test_student_cannot_access_create_minute_page(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->get('/advisor/appointments/' . $appointment->id . '/note');

        $response->assertForbidden();
    }

    public function test_create_minute_page_shows_student_history(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $pastSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(5),
            'end_time' => Carbon::now()->subDays(5)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $pastAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $pastSlot->id,
            'token' => 'PAST-TOKEN',
            'purpose' => 'Past Session',
            'status' => 'completed',
        ]);
        
        Minute::create([
            'appointment_id' => $pastAppointment->id,
            'note' => 'Previous session notes',
        ]);
        
        $currentSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $currentAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $currentSlot->id,
            'token' => 'CURRENT-TOKEN',
            'purpose' => 'Current Session',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/appointments/' . $currentAppointment->id . '/note');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) use ($pastAppointment) {
            return $history->contains('id', $pastAppointment->id);
        });
    }

    public function test_create_minute_page_excludes_current_appointment_from_history(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/appointments/' . $appointment->id . '/note');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) use ($appointment) {
            return !$history->contains('id', $appointment->id);
        });
    }

    public function test_create_minute_page_only_shows_completed_appointments_in_history(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $pendingSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $pendingAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $pendingSlot->id,
            'token' => 'PENDING-TOKEN',
            'purpose' => 'Pending',
            'status' => 'pending',
        ]);
        
        Minute::create([
            'appointment_id' => $pendingAppointment->id,
            'note' => 'Should not show',
        ]);
        
        $currentSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $currentAppointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $currentSlot->id,
            'token' => 'CURRENT-TOKEN',
            'purpose' => 'Current',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/appointments/' . $currentAppointment->id . '/note');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) use ($pendingAppointment) {
            return !$history->contains('id', $pendingAppointment->id);
        });
    }

    public function test_advisor_can_save_session_note(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Academic Counseling',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'Discussed student progress and career goals. Student is doing well academically.',
        ]);

        $response->assertRedirect(route('advisor.schedule'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('minutes', [
            'appointment_id' => $appointment->id,
            'note' => 'Discussed student progress and career goals. Student is doing well academically.',
        ]);
    }

    public function test_saving_note_marks_appointment_as_completed(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'Session completed successfully.',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }

    public function test_advisor_can_update_existing_note(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);
        
        Minute::create([
            'appointment_id' => $appointment->id,
            'note' => 'Initial note',
        ]);

        $response = $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'Updated note with more details',
        ]);

        $response->assertRedirect(route('advisor.schedule'));
        
        $this->assertDatabaseHas('minutes', [
            'appointment_id' => $appointment->id,
            'note' => 'Updated note with more details',
        ]);
        
        $this->assertEquals(1, Minute::where('appointment_id', $appointment->id)->count());
    }

    public function test_save_note_validates_required_note(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', []);

        $response->assertSessionHasErrors(['note']);
    }

    public function test_save_note_validates_minimum_length(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'abc',
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    public function test_save_note_validates_maximum_length(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    public function test_advisor_cannot_save_note_for_others_appointment(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor2)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'Unauthorized note',
        ]);

        $response->assertForbidden();
    }

    public function test_student_cannot_save_note(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->post('/advisor/appointments/' . $appointment->id . '/note', [
            'note' => 'Student note',
        ]);

        $response->assertForbidden();
    }

    public function test_save_note_requires_existing_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->post('/advisor/appointments/99999/note', [
            'note' => 'Test note',
        ]);

        $response->assertNotFound();
    }
}
