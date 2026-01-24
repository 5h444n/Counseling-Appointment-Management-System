<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdvisorScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_advisor_can_access_schedule_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewIs('advisor.schedule');
        $response->assertViewHas('upcoming');
        $response->assertViewHas('history');
    }

    public function test_non_advisor_cannot_access_schedule_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/advisor/schedule');

        $response->assertForbidden();
    }

    public function test_schedule_shows_upcoming_approved_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'UPCOMING-TOKEN',
            'purpose' => 'Academic Counseling',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            return $upcoming->count() === 1;
        });
    }

    public function test_schedule_does_not_show_pending_in_upcoming(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'PENDING-TOKEN',
            'purpose' => 'Test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            return $upcoming->count() === 0;
        });
    }

    public function test_schedule_does_not_show_past_appointments_in_upcoming(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'PAST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            return $upcoming->count() === 0;
        });
    }

    public function test_schedule_shows_completed_appointments_in_history(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'COMPLETED-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) {
            return $history->count() === 1;
        });
    }

    public function test_schedule_shows_past_approved_appointments_in_history(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'PAST-APPROVED',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) {
            return $history->count() === 1;
        });
    }

    public function test_schedule_only_shows_own_appointments(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor2->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'TOKEN-1',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'TOKEN-2',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor1)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            return $upcoming->count() === 1;
        });
    }

    public function test_upcoming_appointments_are_sorted_by_time(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $app1 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'LATER',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);
        
        $app2 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'SOONER',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) use ($app2, $app1) {
            $items = $upcoming->values();
            return $items[0]->id === $app2->id && $items[1]->id === $app1->id;
        });
    }

    public function test_history_appointments_are_sorted_by_time_descending(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(5),
            'end_time' => Carbon::now()->subDays(5)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $app1 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'OLDER',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);
        
        $app2 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'NEWER',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('history', function ($history) use ($app2, $app1) {
            $items = $history->values();
            return $items[0]->id === $app2->id && $items[1]->id === $app1->id;
        });
    }

    public function test_schedule_eager_loads_relationships(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student', 'name' => 'Test Student']);
        
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
            'status' => 'approved',
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            $first = $upcoming->first();
            return $first->relationLoaded('student') && $first->relationLoaded('slot');
        });
    }

    public function test_schedule_works_with_no_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get('/advisor/schedule');

        $response->assertOk();
        $response->assertViewHas('upcoming', function ($upcoming) {
            return $upcoming->count() === 0;
        });
        $response->assertViewHas('history', function ($history) {
            return $history->count() === 0;
        });
    }

    public function test_admin_cannot_access_advisor_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/advisor/schedule');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_schedule(): void
    {
        $response = $this->get('/advisor/schedule');

        $response->assertRedirect('/login');
    }
}
