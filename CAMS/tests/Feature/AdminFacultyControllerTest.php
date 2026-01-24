<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AdminFacultyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_admin_can_access_faculty_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->create(['name' => 'Dr. Test Advisor']);

        $response = $this->actingAs($admin)->get('/admin/faculty');

        $response->assertOk();
        $response->assertViewIs('admin.faculty.index');
        $response->assertViewHas('faculty');
        $response->assertViewHas('departments');
        $response->assertSee('Dr. Test Advisor');
    }

    public function test_non_admin_cannot_access_faculty_index(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/faculty');

        $response->assertForbidden();
    }

    public function test_faculty_index_can_be_searched_by_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->create(['name' => 'Dr. John Smith']);
        User::factory()->advisor()->create(['name' => 'Dr. Jane Doe']);

        $response = $this->actingAs($admin)->get('/admin/faculty?search=John');

        $response->assertOk();
        $response->assertSee('Dr. John Smith');
        $response->assertDontSee('Dr. Jane Doe');
    }

    public function test_faculty_index_can_be_searched_by_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->create(['email' => 'john@example.com']);
        User::factory()->advisor()->create(['email' => 'jane@example.com']);

        $response = $this->actingAs($admin)->get('/admin/faculty?search=john@');

        $response->assertOk();
        $response->assertSee('john@example.com');
        $response->assertDontSee('jane@example.com');
    }

    public function test_faculty_index_can_be_filtered_by_department(): void
    {
        $dept2 = Department::create(['name' => 'Mathematics', 'code' => 'MATH']);
        
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->create([
            'name' => 'Dr. CSE Advisor',
            'department_id' => $this->department->id,
        ]);
        User::factory()->advisor()->create([
            'name' => 'Dr. Math Advisor',
            'department_id' => $dept2->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/faculty?department_id=' . $this->department->id);

        $response->assertOk();
        $response->assertSee('Dr. CSE Advisor');
        $response->assertDontSee('Dr. Math Advisor');
    }

    public function test_admin_can_access_create_faculty_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/faculty/create');

        $response->assertOk();
        $response->assertViewIs('admin.faculty.create');
        $response->assertViewHas('departments');
    }

    public function test_non_admin_cannot_access_create_faculty_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get('/admin/faculty/create');

        $response->assertForbidden();
    }

    public function test_admin_can_create_faculty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/faculty', [
            'name' => 'Dr. New Advisor',
            'email' => 'newadvisor@example.com',
            'department_id' => $this->department->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'name' => 'Dr. New Advisor',
            'email' => 'newadvisor@example.com',
            'department_id' => $this->department->id,
            'role' => 'advisor',
        ]);
        
        $user = User::where('email', 'newadvisor@example.com')->first();
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_create_faculty_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/faculty', []);

        $response->assertSessionHasErrors(['name', 'email', 'department_id', 'password']);
    }

    public function test_create_faculty_validates_unique_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->advisor()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/admin/faculty', [
            'name' => 'Dr. Test',
            'email' => 'existing@example.com',
            'department_id' => $this->department->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_create_faculty_validates_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/faculty', [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'department_id' => $this->department->id,
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_create_faculty_validates_department_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/faculty', [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'department_id' => 99999,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['department_id']);
    }

    public function test_non_admin_cannot_create_faculty(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post('/admin/faculty', [
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'department_id' => $this->department->id,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_access_edit_faculty_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create();

        $response = $this->actingAs($admin)->get('/admin/faculty/' . $faculty->id . '/edit');

        $response->assertOk();
        $response->assertViewIs('admin.faculty.edit');
        $response->assertViewHas('faculty');
        $response->assertViewHas('departments');
    }

    public function test_edit_faculty_page_only_shows_advisors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->get('/admin/faculty/' . $student->id . '/edit');

        $response->assertNotFound();
    }

    public function test_admin_can_update_faculty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($admin)->put('/admin/faculty/' . $faculty->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'department_id' => $this->department->id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'id' => $faculty->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_update_faculty_can_change_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create();

        $response = $this->actingAs($admin)->put('/admin/faculty/' . $faculty->id, [
            'name' => $faculty->name,
            'email' => $faculty->email,
            'department_id' => $faculty->department_id,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        
        $faculty->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $faculty->password));
    }

    public function test_update_faculty_does_not_require_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create();
        $oldPassword = $faculty->password;

        $response = $this->actingAs($admin)->put('/admin/faculty/' . $faculty->id, [
            'name' => 'Updated Name',
            'email' => $faculty->email,
            'department_id' => $faculty->department_id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        
        $faculty->refresh();
        $this->assertEquals($oldPassword, $faculty->password);
    }

    public function test_update_faculty_validates_unique_email_except_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty1 = User::factory()->advisor()->create(['email' => 'faculty1@example.com']);
        $faculty2 = User::factory()->advisor()->create(['email' => 'faculty2@example.com']);

        $response = $this->actingAs($admin)->put('/admin/faculty/' . $faculty1->id, [
            'name' => $faculty1->name,
            'email' => 'faculty2@example.com',
            'department_id' => $faculty1->department_id,
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_faculty_allows_keeping_same_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($admin)->put('/admin/faculty/' . $faculty->id, [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'department_id' => $faculty->department_id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_delete_faculty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create();

        $response = $this->actingAs($admin)->delete('/admin/faculty/' . $faculty->id);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('users', ['id' => $faculty->id]);
    }

    public function test_delete_faculty_prevents_deletion_if_has_slots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faculty = User::factory()->advisor()->create();
        
        \App\Models\AppointmentSlot::create([
            'advisor_id' => $faculty->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete('/admin/faculty/' . $faculty->id);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('users', ['id' => $faculty->id]);
    }

    public function test_delete_faculty_only_deletes_advisors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->delete('/admin/faculty/' . $student->id);

        $response->assertNotFound();
    }

    public function test_non_admin_cannot_update_faculty(): void
    {
        $advisor = User::factory()->advisor()->create();
        $faculty = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->put('/admin/faculty/' . $faculty->id, [
            'name' => 'Updated Name',
            'email' => $faculty->email,
            'department_id' => $faculty->department_id,
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_faculty(): void
    {
        $advisor = User::factory()->advisor()->create();
        $faculty = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->delete('/admin/faculty/' . $faculty->id);

        $response->assertForbidden();
    }
}
