<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StudentBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for the tests
        Department::insert([
            ['name' => 'Computer Science', 'code' => 'CSE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electrical Engineering', 'code' => 'EEE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Test that authenticated users can access advisors listing page.
     */
    public function test_authenticated_users_can_access_advisors_list(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/student/advisors');

        $response->assertOk();
    }

    /**
     * Test that advisors are listed on the index page.
     */
    public function test_advisors_are_listed_on_index_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Test Advisor']);

        $response = $this
            ->actingAs($student)
            ->get('/student/advisors');

        $response->assertOk();
        $response->assertSee('Dr. Test Advisor');
    }

    /**
     * Test that advisors can be searched by name.
     */
    public function test_advisors_can_be_searched_by_name(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor1 = User::factory()->advisor()->create(['name' => 'Dr. John Smith']);
        $advisor2 = User::factory()->advisor()->create(['name' => 'Dr. Jane Doe']);

        $response = $this
            ->actingAs($student)
            ->get('/student/advisors?search=John');

        $response->assertOk();
        $response->assertSee('Dr. John Smith');
        $response->assertDontSee('Dr. Jane Doe');
    }

    /**
     * Test that advisors can be filtered by department.
     */
    public function test_advisors_can_be_filtered_by_department(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $eeeDept = Department::where('code', 'EEE')->first();

        $student = User::factory()->create(['role' => 'student']);
        $advisor1 = User::factory()->advisor()->create([
            'name' => 'Dr. CSE Advisor',
            'department_id' => $cseDept->id,
        ]);
        $advisor2 = User::factory()->advisor()->create([
            'name' => 'Dr. EEE Advisor',
            'department_id' => $eeeDept->id,
        ]);

        $response = $this
            ->actingAs($student)
            ->get("/student/advisors?department_id={$cseDept->id}");

        $response->assertOk();
        $response->assertSee('Dr. CSE Advisor');
        $response->assertDontSee('Dr. EEE Advisor');
    }

    /**
     * Test that authenticated users can view advisor's available slots.
     */
    public function test_authenticated_users_can_view_advisor_slots(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Advisor']);

        // Create an active future slot
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->get("/student/advisors/{$advisor->id}");

        $response->assertOk();
        $response->assertSee('Dr. Advisor');
    }

    /**
     * Test that only active future slots are displayed.
     */
    public function test_only_active_future_slots_are_displayed(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        // Create an active future slot (should be visible)
        $activeSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        // Create a blocked slot (should not be visible)
        $blockedSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        // Create a past slot (should not be visible)
        $pastSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::yesterday()->setTime(10, 0),
            'end_time' => Carbon::yesterday()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->get("/student/advisors/{$advisor->id}");

        $response->assertOk();
        
        // The view should receive only the active future slot
        $this->assertEquals(1, $response->viewData('slots')->count());
        $this->assertEquals($activeSlot->id, $response->viewData('slots')->first()->id);
    }

    /**
     * Test that a student can book an available slot.
     */
    public function test_student_can_book_available_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->post('/student/book', [
                'slot_id' => $slot->id,
                'purpose' => 'Academic advising session',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Verify appointment was created
        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic advising session',
            'status' => 'pending',
        ]);

        // Verify slot was blocked
        $slot->refresh();
        $this->assertEquals('blocked', $slot->status);
    }

    /**
     * Test that appointment token is generated correctly.
     */
    public function test_appointment_token_is_generated(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => $cseDept->id,
        ]);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->post('/student/book', [
                'slot_id' => $slot->id,
                'purpose' => 'Test purpose',
            ]);

        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertNotNull($appointment->token);
        $this->assertStringStartsWith('CSE-', $appointment->token);
    }

    /**
     * Test booking fails when slot is already taken (race condition handling).
     */
    public function test_booking_fails_when_slot_is_already_taken(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked', // Already taken
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->from("/student/advisors/{$advisor->id}")
            ->post('/student/book', [
                'slot_id' => $slot->id,
                'purpose' => 'Test purpose',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Sorry, this slot was just taken.');

        // Verify no appointment was created
        $this->assertDatabaseMissing('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
        ]);
    }

    /**
     * Test validation requires slot_id.
     */
    public function test_validation_requires_slot_id(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->from('/student/advisors')
            ->post('/student/book', [
                'purpose' => 'Test purpose',
            ]);

        $response->assertSessionHasErrors('slot_id');
    }

    /**
     * Test validation requires purpose.
     */
    public function test_validation_requires_purpose(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->from("/student/advisors/{$advisor->id}")
            ->post('/student/book', [
                'slot_id' => $slot->id,
            ]);

        $response->assertSessionHasErrors('purpose');
    }

    /**
     * Test validation rejects non-existent slot.
     */
    public function test_validation_rejects_nonexistent_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->from('/student/advisors')
            ->post('/student/book', [
                'slot_id' => 99999, // Non-existent
                'purpose' => 'Test purpose',
            ]);

        $response->assertSessionHasErrors('slot_id');
    }

    /**
     * Test validation enforces max length on purpose.
     */
    public function test_validation_enforces_purpose_max_length(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $response = $this
            ->actingAs($student)
            ->from("/student/advisors/{$advisor->id}")
            ->post('/student/book', [
                'slot_id' => $slot->id,
                'purpose' => str_repeat('a', 256), // Exceeds 255 char limit
            ]);

        $response->assertSessionHasErrors('purpose');
    }

    /**
     * Test that viewing non-existent advisor returns 404.
     */
    public function test_viewing_nonexistent_advisor_returns_404(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/student/advisors/99999');

        $response->assertStatus(404);
    }
}
