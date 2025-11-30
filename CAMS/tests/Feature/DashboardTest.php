<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a department for tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that authenticated users can access the dashboard.
     */
    public function test_authenticated_users_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
    }

    /**
     * Test that unauthenticated users are redirected from dashboard.
     */
    public function test_unauthenticated_users_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test that root route redirects to login.
     */
    public function test_root_route_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test dashboard displays next approved appointment.
     */
    public function test_dashboard_displays_next_approved_appointment(): void
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
            'purpose' => 'Test appointment',
            'status' => 'approved',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/dashboard');

        $response->assertOk();
        
        // The dashboard should have the next appointment data
        $nextAppointment = $response->viewData('nextAppointment');
        $this->assertNotNull($nextAppointment);
        $this->assertEquals($appointment->id, $nextAppointment->id);
    }

    /**
     * Test dashboard shows null when no approved appointments.
     */
    public function test_dashboard_shows_null_when_no_approved_appointments(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/dashboard');

        $response->assertOk();
        $this->assertNull($response->viewData('nextAppointment'));
    }

    /**
     * Test dashboard only shows user's own appointments.
     */
    public function test_dashboard_only_shows_users_own_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        // Create appointment for student1
        $appointment = Appointment::create([
            'student_id' => $student1->id,
            'slot_id' => $slot->id,
            'purpose' => 'Student 1 appointment',
            'status' => 'approved',
            'token' => 'CSE-1-A',
        ]);

        // student2 should not see student1's appointment
        $response = $this
            ->actingAs($student2)
            ->get('/dashboard');

        $response->assertOk();
        $this->assertNull($response->viewData('nextAppointment'));
    }

    /**
     * Test dashboard does not show pending appointments.
     */
    public function test_dashboard_does_not_show_pending_appointments(): void
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
            'purpose' => 'Pending appointment',
            'status' => 'pending', // Not approved
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/dashboard');

        $response->assertOk();
        // Pending appointments should not be shown as "next appointment"
        $this->assertNull($response->viewData('nextAppointment'));
    }

    /**
     * Test dashboard does not show declined appointments.
     */
    public function test_dashboard_does_not_show_declined_appointments(): void
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
            'purpose' => 'Declined appointment',
            'status' => 'declined',
            'token' => 'CSE-1-A',
        ]);

        $response = $this
            ->actingAs($student)
            ->get('/dashboard');

        $response->assertOk();
        $this->assertNull($response->viewData('nextAppointment'));
    }

    /**
     * Test students can access dashboard.
     */
    public function test_students_can_access_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/dashboard');

        $response->assertOk();
    }

    /**
     * Test advisors can access dashboard.
     */
    public function test_advisors_can_access_dashboard(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/dashboard');

        $response->assertOk();
    }

    /**
     * Test admins can access dashboard.
     */
    public function test_admins_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->get('/dashboard');

        $response->assertOk();
    }

    /**
     * Test unverified users can access dashboard (the verified middleware doesn't block access,
     * it only shows a verification notice).
     */
    public function test_unverified_users_can_access_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        // The 'verified' middleware allows access but may show a notification
        // The route allows access to unverified users in the current configuration
        $response->assertOk();
    }
}
