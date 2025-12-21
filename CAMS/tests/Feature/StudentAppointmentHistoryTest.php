<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StudentAppointmentHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for the tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that students can access their appointments page.
     */
    public function test_students_can_access_appointments_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments');

        $response->assertOk();
    }

    /**
     * Test that unauthenticated users cannot access appointments page.
     */
    public function test_unauthenticated_users_cannot_access_appointments_page(): void
    {
        $response = $this->get('/student/my-appointments');

        $response->assertRedirect('/login');
    }

    /**
     * Test that student appointments are displayed.
     */
    public function test_student_appointments_are_displayed(): void
    {
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Smith']);
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
            'purpose' => 'Career counseling session',
            'status' => 'approved',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments');

        $response->assertOk();
        // The view displays token and advisor name, not the purpose
        $response->assertSee('CSE-1-A');
        $response->assertSee('Dr. Smith');
    }

    /**
     * Test that students only see their own appointments.
     */
    public function test_students_only_see_their_own_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment1 = Appointment::create([
            'student_id' => $student1->id,
            'slot_id' => $slot1->id,
            'purpose' => 'Student 1 appointment',
            'status' => 'approved',
            'token' => 'CSE-1-A',
        ]);

        $appointment2 = Appointment::create([
            'student_id' => $student2->id,
            'slot_id' => $slot2->id,
            'purpose' => 'Student 2 appointment',
            'status' => 'approved',
            'token' => 'CSE-2-B',
        ]);

        $response = $this
            ->actingAs($student1)
            ->get('/student/my-appointments');

        $response->assertOk();
        // The view displays token, so we check for token instead of purpose
        $response->assertSee('CSE-1-A');
        $response->assertDontSee('CSE-2-B');
    }

    /**
     * Test that appointments are ordered by creation date descending.
     */
    public function test_appointments_are_ordered_by_creation_date(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        // Create older appointment first - use DB::table for precise timestamp control
        $oldCreatedAt = Carbon::now()->subDays(2);
        \Illuminate\Support\Facades\DB::table('appointments')->insert([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'purpose' => 'First appointment',
            'status' => 'approved',
            'token' => 'CSE-1-A',
            'created_at' => $oldCreatedAt,
            'updated_at' => $oldCreatedAt,
        ]);
        $appointment1 = Appointment::where('token', 'CSE-1-A')->first();

        // Create newer appointment
        $appointment2 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'purpose' => 'Second appointment',
            'status' => 'pending',
            'token' => 'CSE-1-B',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments');

        $response->assertOk();
        
        // Get appointments from the view data
        $appointments = $response->viewData('appointments');
        // The newer appointment should be first (ordered by created_at desc)
        $this->assertEquals($appointment2->id, $appointments->first()->id);
        $this->assertEquals($appointment1->id, $appointments->last()->id);
    }

    /**
     * Test appointments with different statuses are displayed.
     */
    public function test_appointments_with_different_statuses_are_displayed(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $statuses = ['pending', 'approved', 'declined'];
        $tokens = [];

        foreach ($statuses as $index => $status) {
            $slot = AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => Carbon::tomorrow()->addDays($index)->setTime(10, 0),
                'end_time' => Carbon::tomorrow()->addDays($index)->setTime(10, 30),
                'status' => 'blocked',
                'is_recurring' => false,
            ]);

            $token = "CSE-1-" . chr(65 + $index);
            $tokens[$status] = $token;

            Appointment::create([
                'student_id' => $student->id,
                'slot_id' => $slot->id,
                'purpose' => "Appointment with status: {$status}",
                'status' => $status,
                'token' => $token,
            ]);
        }

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments');

        $response->assertOk();

        // Check for tokens of pending and approved appointments
        // The view shows tokens only for pending and approved status
        $response->assertSee($tokens['pending']);
        $response->assertSee($tokens['approved']);
        
        // Check for different status text
        $response->assertSee('Pending');
        $response->assertSee('Confirmed');
        $response->assertSee('Declined');
    }

    /**
     * Test empty appointments page displays correctly.
     */
    public function test_empty_appointments_page_displays_correctly(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/student/my-appointments');

        $response->assertOk();
        // Should display with zero appointments
        $this->assertEquals(0, $response->viewData('appointments')->count());
    }

    /**
     * Test that advisors cannot access student appointments page.
     * Note: The '/student/my-appointments' route is protected by 'student' middleware,
     * so only students can access it.
     */
    public function test_advisors_can_access_my_appointments_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/student/my-appointments');

        // The route is under 'student' middleware, so advisors get 403
        $response->assertStatus(403);
    }
}
