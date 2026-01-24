<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_dashboard_shows_total_students_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(5)->create(['role' => 'student']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalStudents', 5);
    }

    public function test_dashboard_shows_total_faculty_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalFaculty', 3);
    }

    public function test_dashboard_shows_total_notices_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Notice::create(['title' => 'Test Notice 1', 'content' => 'Content 1', 'user_role' => 'all']);
        Notice::create(['title' => 'Test Notice 2', 'content' => 'Content 2', 'user_role' => 'student']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalNotices', 2);
    }

    public function test_dashboard_shows_total_appointments_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
            'token' => 'TEST-001',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalAppointments', 1);
    }

    public function test_dashboard_shows_pending_requests_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'TEST-001',
            'purpose' => 'Test',
            'status' => 'pending',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'TEST-002',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('pendingRequests', 1);
    }

    public function test_dashboard_shows_top_advisor_with_most_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor1 = User::factory()->advisor()->create(['name' => 'Dr. Popular']);
        $advisor2 = User::factory()->advisor()->create(['name' => 'Dr. Less Popular']);
        
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor1->id,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(2)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $slot3 = AppointmentSlot::create([
            'advisor_id' => $advisor2->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'TEST-001',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'TEST-002',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot3->id,
            'token' => 'TEST-003',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('topAdvisorName', 'Dr. Popular');
        $response->assertViewHas('topAdvisorCount', 2);
    }

    public function test_dashboard_shows_na_when_no_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('topAdvisorName', 'N/A');
        $response->assertViewHas('topAdvisorCount', 0);
    }

    public function test_dashboard_calculates_total_counseling_hours(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $start1 = Carbon::now()->subDays(1);
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => $start1,
            'end_time' => $start1->copy()->addMinutes(60),
            'status' => 'blocked',
        ]);
        
        $start2 = Carbon::now()->subDays(2);
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => $start2,
            'end_time' => $start2->copy()->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'TEST-001',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'TEST-002',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalHours', 1.5);
    }

    public function test_dashboard_only_counts_completed_appointments_for_hours(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $start1 = Carbon::now()->subDays(1);
        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => $start1,
            'end_time' => $start1->copy()->addMinutes(60),
            'status' => 'blocked',
        ]);
        
        $start2 = Carbon::now()->subDays(2);
        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => $start2,
            'end_time' => $start2->copy()->addMinutes(60),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'token' => 'TEST-001',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'token' => 'TEST-002',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalHours', 1.0);
    }

    public function test_admin_can_export_appointments_as_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Test Student']);
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Advisor']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse('2025-01-30 10:00:00'),
            'end_time' => Carbon::parse('2025-01-30 10:30:00'),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN-001',
            'purpose' => 'Academic Counseling',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('ID', $content);
        $this->assertStringContainsString('Token', $content);
        $this->assertStringContainsString('TEST-TOKEN-001', $content);
        $this->assertStringContainsString('Test Student', $content);
        $this->assertStringContainsString('Dr. Advisor', $content);
        $this->assertStringContainsString('Academic Counseling', $content);
    }

    public function test_export_includes_all_appointment_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'John Doe']);
        $advisor = User::factory()->advisor()->create(['name' => 'Dr. Smith']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::parse('2025-02-15 14:00:00'),
            'end_time' => Carbon::parse('2025-02-15 14:30:00'),
            'status' => 'blocked',
        ]);
        
        Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'EXPORT-TEST',
            'purpose' => 'Career Guidance',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get('/admin/export');

        $content = $response->streamedContent();
        $this->assertStringContainsString('EXPORT-TEST', $content);
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('Dr. Smith', $content);
        $this->assertStringContainsString('2025-02-15', $content);
        $this->assertStringContainsString('Completed', $content);
        $this->assertStringContainsString('Career Guidance', $content);
    }

    public function test_export_filename_includes_timestamp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/export');

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('appointment_report_', $disposition);
        $this->assertStringContainsString('.csv', $disposition);
    }

    public function test_non_admin_cannot_export_appointments(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/export');

        $response->assertForbidden();
    }

    public function test_export_works_with_no_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/export');

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('ID', $content);
        $this->assertStringContainsString('Token', $content);
    }

    public function test_export_handles_multiple_appointments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        for ($i = 1; $i <= 3; $i++) {
            $slot = AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => Carbon::now()->addDays($i),
                'end_time' => Carbon::now()->addDays($i)->addMinutes(30),
                'status' => 'blocked',
            ]);
            
            Appointment::create([
                'student_id' => $student->id,
                'slot_id' => $slot->id,
                'token' => 'TOKEN-' . $i,
                'purpose' => 'Test ' . $i,
                'status' => 'approved',
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/export');

        $content = $response->streamedContent();
        $this->assertStringContainsString('TOKEN-1', $content);
        $this->assertStringContainsString('TOKEN-2', $content);
        $this->assertStringContainsString('TOKEN-3', $content);
    }
}
