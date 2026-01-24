<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\CalendarEvent;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CalendarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_student_can_fetch_personal_calendar_events(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        
        CalendarEvent::create([
            'user_id' => $student->id,
            'title' => 'Study Session',
            'description' => 'Prepare for exam',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
            'type' => 'note',
        ]);

        $response = $this->actingAs($student)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Study Session']);
    }

    public function test_student_can_fetch_their_appointments(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Smith']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Academic',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Meeting: Dr. Smith']);
    }

    public function test_advisor_can_fetch_personal_calendar_events(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        CalendarEvent::create([
            'user_id' => $advisor->id,
            'title' => 'Department Meeting',
            'start_time' => Carbon::now()->addDays(2),
            'type' => 'note',
        ]);

        $response = $this->actingAs($advisor)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Department Meeting']);
    }

    public function test_advisor_can_fetch_their_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student', 'name' => 'John Doe']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Academic',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Appt: John Doe']);
    }

    public function test_user_only_sees_own_calendar_events(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        CalendarEvent::create([
            'user_id' => $user1->id,
            'title' => 'User 1 Event',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'note',
        ]);
        
        CalendarEvent::create([
            'user_id' => $user2->id,
            'title' => 'User 2 Event',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'note',
        ]);

        $response = $this->actingAs($user1)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'User 1 Event']);
        $response->assertJsonMissing(['title' => 'User 2 Event']);
    }

    public function test_calendar_event_has_correct_color_for_type(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        
        CalendarEvent::create([
            'user_id' => $student->id,
            'title' => 'Reminder Event',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'reminder',
        ]);

        $response = $this->actingAs($student)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonPath('0.color', '#ef4444');
    }

    public function test_appointment_has_correct_color_based_on_status(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->getJson('/calendar/events');

        $response->assertOk();
        $events = $response->json();
        $appointmentEvent = collect($events)->firstWhere('extendedProps.type', 'appointment');
        $this->assertEquals('#22c55e', $appointmentEvent['color']);
    }

    public function test_user_can_create_calendar_event(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/calendar/events', [
            'title' => 'Study Group',
            'description' => 'Weekly study session',
            'start_time' => Carbon::now()->addDays(1)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(1)->addHours(2)->toDateTimeString(),
            'type' => 'note',
            'color' => '#3b82f6',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $student->id,
            'title' => 'Study Group',
            'description' => 'Weekly study session',
            'type' => 'note',
        ]);
    }

    public function test_create_calendar_event_validates_required_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/calendar/events', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'start_time', 'type']);
    }

    public function test_create_calendar_event_validates_type(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/calendar/events', [
            'title' => 'Test',
            'start_time' => Carbon::now()->addDays(1)->toDateTimeString(),
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_create_calendar_event_validates_date_format(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/calendar/events', [
            'title' => 'Test',
            'start_time' => 'invalid-date',
            'type' => 'note',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_time']);
    }

    public function test_user_can_create_reminder_event(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/calendar/events', [
            'title' => 'Submit Assignment',
            'start_time' => Carbon::now()->addDays(7)->toDateTimeString(),
            'type' => 'reminder',
        ]);

        $response->assertOk();
        
        $this->assertDatabaseHas('calendar_events', [
            'user_id' => $student->id,
            'title' => 'Submit Assignment',
            'type' => 'reminder',
        ]);
    }

    public function test_user_can_delete_own_calendar_event(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        
        $event = CalendarEvent::create([
            'user_id' => $student->id,
            'title' => 'Delete Me',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'note',
        ]);

        $response = $this->actingAs($student)->deleteJson('/calendar/events/' . $event->id);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_user_cannot_delete_others_calendar_event(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $event = CalendarEvent::create([
            'user_id' => $user1->id,
            'title' => 'User 1 Event',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'note',
        ]);

        $response = $this->actingAs($user2)->deleteJson('/calendar/events/' . $event->id);

        $response->assertNotFound();
        
        $this->assertDatabaseHas('calendar_events', ['id' => $event->id]);
    }

    public function test_delete_calendar_event_requires_existing_event(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->deleteJson('/calendar/events/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_fetch_events(): void
    {
        $response = $this->getJson('/calendar/events');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_event(): void
    {
        $response = $this->postJson('/calendar/events', [
            'title' => 'Test',
            'start_time' => Carbon::now()->addDays(1)->toDateTimeString(),
            'type' => 'note',
        ]);

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_delete_event(): void
    {
        $response = $this->deleteJson('/calendar/events/1');

        $response->assertUnauthorized();
    }

    public function test_calendar_events_include_extended_props(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        
        CalendarEvent::create([
            'user_id' => $student->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => Carbon::now()->addDays(1),
            'type' => 'note',
        ]);

        $response = $this->actingAs($student)->getJson('/calendar/events');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'start',
                'color',
                'extendedProps' => [
                    'type',
                    'description',
                    'db_id',
                ],
            ],
        ]);
    }
}
