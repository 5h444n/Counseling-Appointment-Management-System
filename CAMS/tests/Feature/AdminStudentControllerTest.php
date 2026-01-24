<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AdminStudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->department = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_admin_can_access_students_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'name' => 'John Student']);

        $response = $this->actingAs($admin)->get('/admin/students');

        $response->assertOk();
        $response->assertViewIs('admin.students.index');
        $response->assertViewHas('students');
        $response->assertViewHas('departments');
        $response->assertSee('John Student');
    }

    public function test_non_admin_cannot_access_students_index(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/students');

        $response->assertForbidden();
    }

    public function test_students_index_can_be_searched_by_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'name' => 'Alice Smith']);
        User::factory()->create(['role' => 'student', 'name' => 'Bob Jones']);

        $response = $this->actingAs($admin)->get('/admin/students?search=Alice');

        $response->assertOk();
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    public function test_students_index_can_be_searched_by_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'email' => 'alice@example.com']);
        User::factory()->create(['role' => 'student', 'email' => 'bob@example.com']);

        $response = $this->actingAs($admin)->get('/admin/students?search=alice@');

        $response->assertOk();
        $response->assertSee('alice@example.com');
        $response->assertDontSee('bob@example.com');
    }

    public function test_students_index_can_be_searched_by_university_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'university_id' => '011123456']);
        User::factory()->create(['role' => 'student', 'university_id' => '011987654']);

        $response = $this->actingAs($admin)->get('/admin/students?search=011123456');

        $response->assertOk();
        $response->assertSee('011123456');
        $response->assertDontSee('011987654');
    }

    public function test_students_index_can_be_filtered_by_department(): void
    {
        $dept2 = Department::create(['name' => 'Mathematics', 'code' => 'MATH']);
        
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'role' => 'student',
            'name' => 'CSE Student',
            'department_id' => $this->department->id,
        ]);
        User::factory()->create([
            'role' => 'student',
            'name' => 'Math Student',
            'department_id' => $dept2->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/students?department_id=' . $this->department->id);

        $response->assertOk();
        $response->assertSee('CSE Student');
        $response->assertDontSee('Math Student');
    }

    public function test_students_index_is_paginated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(20)->create(['role' => 'student']);

        $response = $this->actingAs($admin)->get('/admin/students');

        $response->assertOk();
        $response->assertViewHas('students', function ($students) {
            return $students instanceof \Illuminate\Pagination\LengthAwarePaginator;
        });
    }

    public function test_admin_can_access_create_student_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/students/create');

        $response->assertOk();
        $response->assertViewIs('admin.students.create');
        $response->assertViewHas('departments');
    }

    public function test_non_admin_cannot_access_create_student_page(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->get('/admin/students/create');

        $response->assertForbidden();
    }

    public function test_admin_can_create_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/students', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'role' => 'student',
        ]);
        
        $user = User::where('email', 'newstudent@example.com')->first();
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_create_student_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/students', []);

        $response->assertSessionHasErrors(['name', 'email', 'department_id', 'university_id', 'password']);
    }

    public function test_create_student_validates_unique_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'existing@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_create_student_validates_unique_university_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'university_id' => '011123456']);

        $response = $this->actingAs($admin)->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['university_id']);
    }

    public function test_create_student_validates_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_create_student_validates_department_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'department_id' => 99999,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['department_id']);
    }

    public function test_non_admin_cannot_create_student(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->post('/admin/students', [
            'name' => 'Test Student',
            'email' => 'test@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011123456',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_access_edit_student_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->get('/admin/students/' . $student->id . '/edit');

        $response->assertOk();
        $response->assertViewIs('admin.students.edit');
        $response->assertViewHas('student');
        $response->assertViewHas('departments');
    }

    public function test_edit_student_page_only_shows_students(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($admin)->get('/admin/students/' . $advisor->id . '/edit');

        $response->assertNotFound();
    }

    public function test_admin_can_update_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'university_id' => '011111111',
        ]);

        $response = $this->actingAs($admin)->put('/admin/students/' . $student->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'department_id' => $this->department->id,
            'university_id' => '011222222',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'university_id' => '011222222',
        ]);
    }

    public function test_update_student_can_change_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->put('/admin/students/' . $student->id, [
            'name' => $student->name,
            'email' => $student->email,
            'department_id' => $student->department_id,
            'university_id' => $student->university_id,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        
        $student->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $student->password));
    }

    public function test_update_student_does_not_require_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $oldPassword = $student->password;

        $response = $this->actingAs($admin)->put('/admin/students/' . $student->id, [
            'name' => 'Updated Name',
            'email' => $student->email,
            'department_id' => $student->department_id,
            'university_id' => $student->university_id,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        
        $student->refresh();
        $this->assertEquals($oldPassword, $student->password);
    }

    public function test_update_student_validates_unique_email_except_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student1 = User::factory()->create(['role' => 'student', 'email' => 'student1@example.com']);
        $student2 = User::factory()->create(['role' => 'student', 'email' => 'student2@example.com']);

        $response = $this->actingAs($admin)->put('/admin/students/' . $student1->id, [
            'name' => $student1->name,
            'email' => 'student2@example.com',
            'department_id' => $student1->department_id,
            'university_id' => $student1->university_id,
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_student_validates_unique_university_id_except_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student1 = User::factory()->create(['role' => 'student', 'university_id' => '011111111']);
        $student2 = User::factory()->create(['role' => 'student', 'university_id' => '011222222']);

        $response = $this->actingAs($admin)->put('/admin/students/' . $student1->id, [
            'name' => $student1->name,
            'email' => $student1->email,
            'department_id' => $student1->department_id,
            'university_id' => '011222222',
        ]);

        $response->assertSessionHasErrors(['university_id']);
    }

    public function test_update_student_allows_keeping_same_email_and_university_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'email' => 'test@example.com',
            'university_id' => '011123456',
        ]);

        $response = $this->actingAs($admin)->put('/admin/students/' . $student->id, [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'department_id' => $student->department_id,
            'university_id' => '011123456',
        ]);

        $response->assertRedirect(route('admin.students.index'));
    }

    public function test_admin_can_delete_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->delete('/admin/students/' . $student->id);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_delete_student_only_deletes_students(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($admin)->delete('/admin/students/' . $advisor->id);

        $response->assertNotFound();
    }

    public function test_non_admin_cannot_update_student(): void
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student1)->put('/admin/students/' . $student2->id, [
            'name' => 'Updated Name',
            'email' => $student2->email,
            'department_id' => $student2->department_id,
            'university_id' => $student2->university_id,
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_student(): void
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student1)->delete('/admin/students/' . $student2->id);

        $response->assertForbidden();
    }
}
