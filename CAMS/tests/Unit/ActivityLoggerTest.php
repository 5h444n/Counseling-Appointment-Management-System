<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLoggerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that logLogin creates an activity log with correct fields.
     */
    public function test_log_login_creates_activity_log_with_correct_fields(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $activityLog = ActivityLogger::logLogin($user->id, $user->name);

        $this->assertInstanceOf(ActivityLog::class, $activityLog);
        $this->assertEquals($user->id, $activityLog->user_id);
        $this->assertEquals('login', $activityLog->action);
        $this->assertEquals('John Doe logged into the system', $activityLog->description);
        $this->assertNotNull($activityLog->ip_address);

        // Verify it's persisted in database
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'John Doe logged into the system',
        ]);
    }

    /**
     * Test that logBooking creates an activity log with correct fields.
     */
    public function test_log_booking_creates_activity_log_with_correct_fields(): void
    {
        $user = User::factory()->create(['name' => 'Student Name']);
        $this->actingAs($user);

        $activityLog = ActivityLogger::logBooking('Student Name', 'Dr. Advisor', 'CSE-1-A');

        $this->assertInstanceOf(ActivityLog::class, $activityLog);
        $this->assertEquals($user->id, $activityLog->user_id);
        $this->assertEquals('book_appointment', $activityLog->action);
        $this->assertEquals('Student Name booked an appointment with Dr. Advisor (Token: CSE-1-A)', $activityLog->description);
        $this->assertNotNull($activityLog->ip_address);

        // Verify it's persisted in database
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'book_appointment',
            'description' => 'Student Name booked an appointment with Dr. Advisor (Token: CSE-1-A)',
        ]);
    }

    /**
     * Test that logCancellation creates an activity log with correct fields.
     */
    public function test_log_cancellation_creates_activity_log_with_correct_fields(): void
    {
        $user = User::factory()->create(['name' => 'Student Name']);
        $this->actingAs($user);

        $activityLog = ActivityLogger::logCancellation('Student Name', 'Dr. Advisor', 'CSE-1-A');

        $this->assertInstanceOf(ActivityLog::class, $activityLog);
        $this->assertEquals($user->id, $activityLog->user_id);
        $this->assertEquals('cancel_appointment', $activityLog->action);
        $this->assertEquals('Student Name cancelled appointment with Dr. Advisor (Token: CSE-1-A)', $activityLog->description);
        $this->assertNotNull($activityLog->ip_address);

        // Verify it's persisted in database
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'cancel_appointment',
            'description' => 'Student Name cancelled appointment with Dr. Advisor (Token: CSE-1-A)',
        ]);
    }

    /**
     * Test that log method can accept custom userId.
     */
    public function test_log_method_with_custom_user_id(): void
    {
        $user = User::factory()->create();

        $activityLog = ActivityLogger::log('custom_action', 'Custom description', $user->id);

        $this->assertEquals($user->id, $activityLog->user_id);
        $this->assertEquals('custom_action', $activityLog->action);
        $this->assertEquals('Custom description', $activityLog->description);
    }

    /**
     * Test that log method uses authenticated user when userId is not provided.
     */
    public function test_log_method_uses_authenticated_user_when_user_id_not_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $activityLog = ActivityLogger::log('custom_action', 'Custom description');

        $this->assertEquals($user->id, $activityLog->user_id);
    }

    /**
     * Test that log method captures IP address.
     */
    public function test_log_method_captures_ip_address(): void
    {
        $user = User::factory()->create();

        $activityLog = ActivityLogger::log('test_action', 'Test description', $user->id);

        $this->assertNotNull($activityLog->ip_address);
        // In testing environment, IP is typically 127.0.0.1
        $this->assertIsString($activityLog->ip_address);
    }
}
