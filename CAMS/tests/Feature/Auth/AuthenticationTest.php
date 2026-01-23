<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_activity_log_is_created_on_successful_login(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // Verify activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Test User logged into the system',
        ]);

        // Verify the activity log record
        $activityLog = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->first();
        
        $this->assertNotNull($activityLog);
        $this->assertEquals($user->id, $activityLog->user_id);
        $this->assertEquals('login', $activityLog->action);
        $this->assertStringContainsString('Test User logged into the system', $activityLog->description);
        $this->assertNotNull($activityLog->ip_address);
    }

    public function test_no_activity_log_is_created_on_failed_login(): void
    {
        $user = User::factory()->create();

        // Count activity logs before login attempt
        $initialCount = ActivityLog::count();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        // Verify no new activity log was created
        $this->assertEquals($initialCount, ActivityLog::count());
    }
}
