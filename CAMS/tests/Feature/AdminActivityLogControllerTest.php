<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can access activity logs page.
     */
    public function test_admin_can_access_activity_logs_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs');

        $response->assertOk();
        $response->assertViewIs('admin.activity-logs.index');
        $response->assertViewHas('logs');
    }

    /**
     * Test that activity logs are displayed correctly.
     */
    public function test_activity_logs_are_displayed_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Test User']);

        // Create some activity logs
        $log1 = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Test User logged into the system',
            'ip_address' => '127.0.0.1',
        ]);

        $log2 = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'book_appointment',
            'description' => 'Test User booked appointment with Dr. Advisor',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs');

        $response->assertOk();
        $response->assertSee('Test User logged into the system');
        $response->assertSee('Test User booked appointment with Dr. Advisor');
    }

    /**
     * Test that pagination works correctly.
     */
    public function test_pagination_works_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Test User']);

        // Create 30 activity logs (more than the default 25 per page)
        for ($i = 1; $i <= 30; $i++) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => "Login #{$i}",
                'ip_address' => '127.0.0.1',
            ]);
        }

        // Test first page
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertEquals(25, $logs->count());
        $this->assertEquals(30, $logs->total());

        // Test second page
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?page=2');

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertEquals(5, $logs->count());
    }

    /**
     * Test that search filtering works.
     */
    public function test_search_filtering_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        ActivityLog::create([
            'user_id' => $user1->id,
            'action' => 'login',
            'description' => 'John Doe logged in',
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $user2->id,
            'action' => 'book_appointment',
            'description' => 'Jane Smith booked appointment',
            'ip_address' => '127.0.0.1',
        ]);

        // Search by user name
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?search=John');

        $response->assertOk();
        $response->assertSee('John Doe logged in');
        $response->assertDontSee('Jane Smith booked appointment');

        // Search by action
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?search=book');

        $response->assertOk();
        $response->assertSee('Jane Smith booked appointment');
        $response->assertDontSee('John Doe logged in');
    }

    /**
     * Test that date range filtering works.
     */
    public function test_date_range_filtering_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Test User']);

        // Create old log
        $oldLog = new ActivityLog([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Old login',
            'ip_address' => '127.0.0.1',
        ]);
        $oldLog->created_at = Carbon::now()->subDays(10);
        $oldLog->updated_at = Carbon::now()->subDays(10);
        $oldLog->save();

        // Create recent log
        $recentLog = new ActivityLog([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Recent login',
            'ip_address' => '127.0.0.1',
        ]);
        $recentLog->created_at = Carbon::now()->subDays(2);
        $recentLog->updated_at = Carbon::now()->subDays(2);
        $recentLog->save();

        // Filter by start date (should only show recent login)
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?start_date=' . Carbon::now()->subDays(3)->format('Y-m-d'));

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertEquals(1, $logs->count());
        $this->assertEquals('Recent login', $logs->first()->description);

        // Filter by end date (should only show old login)
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?end_date=' . Carbon::now()->subDays(5)->format('Y-m-d'));

        $response->assertOk();
        $logs = $response->viewData('logs');
        $this->assertEquals(1, $logs->count());
        $this->assertEquals('Old login', $logs->first()->description);
    }

    /**
     * Test that action type filtering works.
     */
    public function test_action_type_filtering_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Test User']);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'User logged in',
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'book_appointment',
            'description' => 'User booked appointment',
            'ip_address' => '127.0.0.1',
        ]);

        // Filter by login action
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?action_type=login');

        $response->assertOk();
        $response->assertSee('User logged in');
        $response->assertDontSee('User booked appointment');

        // Filter by book_appointment action
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?action_type=book_appointment');

        $response->assertOk();
        $response->assertSee('User booked appointment');
        $response->assertDontSee('User logged in');
    }

    /**
     * Test that role filtering works.
     */
    public function test_role_filtering_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Student User']);
        $advisor = User::factory()->advisor()->create(['name' => 'Advisor User']);

        ActivityLog::create([
            'user_id' => $student->id,
            'action' => 'login',
            'description' => 'Student logged in',
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $advisor->id,
            'action' => 'login',
            'description' => 'Advisor logged in',
            'ip_address' => '127.0.0.1',
        ]);

        // Filter by student role
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?role=student');

        $response->assertOk();
        $response->assertSee('Student logged in');
        $response->assertDontSee('Advisor logged in');

        // Filter by advisor role
        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs?role=advisor');

        $response->assertOk();
        $response->assertSee('Advisor logged in');
        $response->assertDontSee('Student logged in');
    }

    /**
     * Test that non-admin users cannot access activity logs.
     */
    public function test_non_admin_users_cannot_access_logs(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        // Test student access
        $response = $this
            ->actingAs($student)
            ->get('/admin/activity-logs');

        $response->assertStatus(403);

        // Test advisor access
        $response = $this
            ->actingAs($advisor)
            ->get('/admin/activity-logs');

        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access activity logs.
     */
    public function test_unauthenticated_users_cannot_access_logs(): void
    {
        $response = $this->get('/admin/activity-logs');

        $response->assertRedirect('/login');
    }

    /**
     * Test that logs are ordered by most recent first.
     */
    public function test_logs_are_ordered_by_most_recent_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Test User']);

        // Create first login (older)
        $log1 = new ActivityLog([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'First login',
            'ip_address' => '127.0.0.1',
        ]);
        $log1->created_at = Carbon::now()->subHours(2);
        $log1->updated_at = Carbon::now()->subHours(2);
        $log1->save();

        // Create second login (newer)
        $log2 = new ActivityLog([
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Second login',
            'ip_address' => '127.0.0.1',
        ]);
        $log2->created_at = Carbon::now()->subHours(1);
        $log2->updated_at = Carbon::now()->subHours(1);
        $log2->save();

        $response = $this
            ->actingAs($admin)
            ->get('/admin/activity-logs');

        $response->assertOk();
        $logs = $response->viewData('logs');
        
        // Most recent should be first
        $logsArray = $logs->items();
        $this->assertEquals('Second login', $logsArray[0]->description);
        $this->assertEquals('First login', $logsArray[1]->description);
    }
}
