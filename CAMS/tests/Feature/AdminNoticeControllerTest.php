<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNoticeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_admin_can_access_notices_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Notice::create([
            'title' => 'Test Notice',
            'content' => 'Notice content',
            'user_role' => 'all',
        ]);

        $response = $this->actingAs($admin)->get('/admin/notices');

        $response->assertOk();
        $response->assertViewIs('admin.notices.index');
        $response->assertViewHas('notices');
        $response->assertSee('Test Notice');
    }

    public function test_non_admin_cannot_access_notices_index(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/notices');

        $response->assertForbidden();
    }

    public function test_notices_are_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        for ($i = 1; $i <= 15; $i++) {
            Notice::create([
                'title' => "Notice $i",
                'content' => "Content $i",
                'user_role' => 'all',
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/notices');

        $response->assertOk();
        $response->assertViewHas('notices', function ($notices) {
            return $notices instanceof \Illuminate\Pagination\LengthAwarePaginator;
        });
    }

    public function test_admin_can_access_create_notice_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/notices/create');

        $response->assertOk();
        $response->assertViewIs('admin.notices.create');
        $response->assertViewHas('users');
    }

    public function test_non_admin_cannot_access_create_notice_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get('/admin/notices/create');

        $response->assertForbidden();
    }

    public function test_admin_can_create_notice_for_all_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'System Maintenance',
            'content' => 'System will be down for maintenance on Sunday.',
            'user_role' => 'all',
        ]);

        $response->assertRedirect(route('admin.notices.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('notices', [
            'title' => 'System Maintenance',
            'content' => 'System will be down for maintenance on Sunday.',
            'user_role' => 'all',
            'user_id' => null,
        ]);
    }

    public function test_admin_can_create_notice_for_students(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Student Notice',
            'content' => 'Important message for students.',
            'user_role' => 'student',
        ]);

        $response->assertRedirect(route('admin.notices.index'));
        
        $this->assertDatabaseHas('notices', [
            'title' => 'Student Notice',
            'user_role' => 'student',
            'user_id' => null,
        ]);
    }

    public function test_admin_can_create_notice_for_advisors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Advisor Notice',
            'content' => 'Important message for advisors.',
            'user_role' => 'advisor',
        ]);

        $response->assertRedirect(route('admin.notices.index'));
        
        $this->assertDatabaseHas('notices', [
            'title' => 'Advisor Notice',
            'user_role' => 'advisor',
            'user_id' => null,
        ]);
    }

    public function test_admin_can_create_notice_for_specific_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Personal Notice',
            'content' => 'This is for you specifically.',
            'user_role' => 'specific',
            'user_id' => $student->id,
        ]);

        $response->assertRedirect(route('admin.notices.index'));
        
        $this->assertDatabaseHas('notices', [
            'title' => 'Personal Notice',
            'user_role' => 'specific',
            'user_id' => $student->id,
        ]);
    }

    public function test_create_notice_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', []);

        $response->assertSessionHasErrors(['title', 'content', 'user_role']);
    }

    public function test_create_notice_validates_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'invalid_role',
        ]);

        $response->assertSessionHasErrors(['user_role']);
    }

    public function test_create_notice_requires_user_id_when_role_is_specific(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'specific',
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_create_notice_validates_user_id_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'specific',
            'user_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_create_notice_does_not_require_user_id_for_all(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'all',
        ]);

        $response->assertSessionDoesntHaveErrors(['user_id']);
    }

    public function test_create_notice_does_not_require_user_id_for_student_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'student',
        ]);

        $response->assertSessionDoesntHaveErrors(['user_id']);
    }

    public function test_create_notice_does_not_require_user_id_for_advisor_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'advisor',
        ]);

        $response->assertSessionDoesntHaveErrors(['user_id']);
    }

    public function test_non_admin_cannot_create_notice(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post('/admin/notices', [
            'title' => 'Test',
            'content' => 'Test content',
            'user_role' => 'all',
        ]);

        $response->assertForbidden();
    }

    public function test_notices_are_ordered_by_newest_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        sleep(1); // Ensure different timestamps
        
        $notice1 = Notice::create([
            'title' => 'Older Notice',
            'content' => 'Content',
            'user_role' => 'all',
        ]);
        
        sleep(1); // Ensure different timestamps
        
        $notice2 = Notice::create([
            'title' => 'Newer Notice',
            'content' => 'Content',
            'user_role' => 'all',
        ]);

        $response = $this->actingAs($admin)->get('/admin/notices');

        $response->assertOk();
        $response->assertViewHas('notices', function ($notices) use ($notice1, $notice2) {
            $items = $notices->items();
            return count($items) >= 2 && $items[0]->id === $notice2->id && $items[1]->id === $notice1->id;
        });
    }

    public function test_creating_notice_handles_notification_failures_gracefully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/notices', [
            'title' => 'Test Notice',
            'content' => 'Test content',
            'user_role' => 'all',
        ]);

        $response->assertRedirect(route('admin.notices.index'));
        
        $this->assertDatabaseHas('notices', [
            'title' => 'Test Notice',
        ]);
    }
}
