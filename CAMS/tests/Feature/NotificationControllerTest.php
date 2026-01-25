<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_user_can_fetch_notifications(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk();
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
        ]);
        $response->assertJsonCount(1, 'notifications');
    }

    public function test_user_only_sees_own_notifications(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => ['message' => 'User 1 notification'],
            'read_at' => null,
        ]);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user2->id,
            'data' => ['message' => 'User 2 notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user1)->getJson('/notifications');

        $response->assertOk();
        $response->assertJsonCount(1, 'notifications');
    }

    public function test_notification_response_includes_unread_count(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Unread notification'],
            'read_at' => null,
        ]);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Read notification'],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk();
        $response->assertJson(['unread_count' => 1]);
    }

    public function test_notifications_are_limited_to_10(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        for ($i = 1; $i <= 15; $i++) {
            DatabaseNotification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => ['message' => "Notification $i"],
                'read_at' => null,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk();
        $response->assertJsonCount(10, 'notifications');
    }

    public function test_notifications_are_ordered_by_latest_first(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $old = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Old notification'],
            'read_at' => null,
            'created_at' => now()->subHours(2),
        ]);
        
        $new = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'New notification'],
            'read_at' => null,
            'created_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk();
        $notifications = $response->json('notifications');
        $this->assertEquals($new->id, $notifications[0]['id']);
        $this->assertEquals($old->id, $notifications[1]['id']);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $notification = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->postJson('/notifications/' . $notification->id . '/read');

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
        
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_as_read_handles_already_read_notification(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        $notification = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/notifications/' . $notification->id . '/read');

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        $notification = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user2)->postJson('/notifications/' . $notification->id . '/read');

        $response->assertOk();
        
        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    public function test_mark_as_read_handles_nonexistent_notification(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->postJson('/notifications/nonexistent-id/read');

        $response->assertOk();
        $response->assertJson(['status' => 'success']);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Notification 1'],
            'read_at' => null,
        ]);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Notification 2'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/notifications/mark-all');

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_mark_all_as_read_only_affects_own_notifications(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user1->id,
            'data' => ['message' => 'User 1 notification'],
            'read_at' => null,
        ]);
        
        DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user2->id,
            'data' => ['message' => 'User 2 notification'],
            'read_at' => null,
        ]);

        $this->actingAs($user1)->post('/notifications/mark-all');

        $this->assertEquals(0, $user1->unreadNotifications()->count());
        $this->assertEquals(1, $user2->unreadNotifications()->count());
    }

    public function test_unauthenticated_user_cannot_fetch_notifications(): void
    {
        $response = $this->getJson('/notifications');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_mark_notification_as_read(): void
    {
        $response = $this->postJson('/notifications/some-id/read');

        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_mark_all_as_read(): void
    {
        $response = $this->post('/notifications/mark-all');

        $response->assertRedirect('/login');
    }

    public function test_notifications_endpoint_works_with_no_notifications(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->getJson('/notifications');

        $response->assertOk();
        $response->assertJson([
            'notifications' => [],
            'unread_count' => 0,
        ]);
    }
}
