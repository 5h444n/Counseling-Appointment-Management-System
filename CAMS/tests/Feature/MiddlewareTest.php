<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // IsStudent Middleware Tests
    // ========================================

    /**
     * Test that students can access student-only routes.
     */
    public function test_students_can_access_student_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // The student booking routes are protected by the student middleware
        $response = $this
            ->actingAs($student)
            ->get('/student/advisors');

        $response->assertOk();
    }

    /**
     * Test that advisors cannot access student-only routes.
     */
    public function test_advisors_cannot_access_student_routes(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/student/advisors');

        // Should get 403 because advisors are blocked by student middleware
        $response->assertStatus(403);
    }

    /**
     * Test that admins cannot access student-only routes.
     */
    public function test_admins_cannot_access_student_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->get('/student/advisors');

        $response->assertStatus(403);
    }

    // ========================================
    // IsAdvisor Middleware Tests
    // ========================================

    /**
     * Test that advisors can access advisor-only routes.
     */
    public function test_advisors_can_access_advisor_routes(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/advisor/slots');

        $response->assertOk();
    }

    /**
     * Test that students cannot access advisor-only routes.
     */
    public function test_students_cannot_access_advisor_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/advisor/slots');

        $response->assertStatus(403);
    }

    /**
     * Test that admins cannot access advisor-only routes.
     */
    public function test_admins_cannot_access_advisor_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->get('/advisor/slots');

        $response->assertStatus(403);
    }

    // ========================================
    // IsAdmin Middleware Tests
    // ========================================

    /**
     * Test that admins can access admin-only routes.
     */
    public function test_admins_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertOk();
    }

    /**
     * Test that students cannot access admin-only routes.
     */
    public function test_students_cannot_access_admin_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test that advisors cannot access admin-only routes.
     */
    public function test_advisors_cannot_access_admin_routes(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    // ========================================
    // Unauthenticated Access Tests
    // ========================================

    /**
     * Test that unauthenticated users are redirected from student routes.
     */
    public function test_unauthenticated_users_redirected_from_student_routes(): void
    {
        $response = $this->get('/student/advisors');

        $response->assertRedirect('/login');
    }

    /**
     * Test that unauthenticated users are redirected from advisor routes.
     */
    public function test_unauthenticated_users_redirected_from_advisor_routes(): void
    {
        $response = $this->get('/advisor/slots');

        $response->assertRedirect('/login');
    }

    /**
     * Test that unauthenticated users are redirected from admin routes.
     */
    public function test_unauthenticated_users_redirected_from_admin_routes(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    // ========================================
    // POST Request Middleware Tests
    // ========================================

    /**
     * Test that student middleware blocks advisor POST requests.
     */
    public function test_student_middleware_blocks_advisor_post_requests(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this
            ->actingAs($advisor)
            ->post('/student/book', [
                'slot_id' => 1,
                'purpose' => 'Test',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test that advisor middleware blocks student POST requests.
     */
    public function test_advisor_middleware_blocks_student_post_requests(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->post('/advisor/slots', [
                'date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'duration' => 30,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test that advisor middleware blocks student DELETE requests.
     */
    public function test_advisor_middleware_blocks_student_delete_requests(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->delete('/advisor/slots/1');

        $response->assertStatus(403);
    }

    /**
     * Test that advisor middleware blocks student PATCH requests.
     */
    public function test_advisor_middleware_blocks_student_patch_requests(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this
            ->actingAs($student)
            ->patch('/advisor/appointments/1', [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }
}
