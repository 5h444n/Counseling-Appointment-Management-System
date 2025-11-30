<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
        Department::create(['name' => 'Electrical Engineering', 'code' => 'EEE']);
    }

    /**
     * Test registration requires name.
     */
    public function test_registration_requires_name(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'email' => 'test@example.com',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /**
     * Test registration requires valid email.
     */
    public function test_registration_requires_valid_email(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test registration requires unique email.
     */
    public function test_registration_requires_unique_email(): void
    {
        $department = Department::first();
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test registration requires university_id.
     */
    public function test_registration_requires_university_id(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('university_id');
        $this->assertGuest();
    }

    /**
     * Test registration requires unique university_id.
     */
    public function test_registration_requires_unique_university_id(): void
    {
        $department = Department::first();
        User::factory()->create(['university_id' => '01112345']);

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('university_id');
        $this->assertGuest();
    }

    /**
     * Test registration requires valid role.
     */
    public function test_registration_requires_valid_role(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'university_id' => '01112345',
                'role' => 'invalid_role',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /**
     * Test registration allows student role.
     */
    public function test_registration_allows_student_role(): void
    {
        $department = Department::first();

        $response = $this->post('/register', [
            'name' => 'Student User',
            'email' => 'student@example.com',
            'university_id' => '01112345',
            'role' => 'student',
            'department_id' => $department->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'role' => 'student',
        ]);
    }

    /**
     * Test registration allows advisor role.
     */
    public function test_registration_allows_advisor_role(): void
    {
        $department = Department::first();

        $response = $this->post('/register', [
            'name' => 'Advisor User',
            'email' => 'advisor@example.com',
            'university_id' => 'T-1234',
            'role' => 'advisor',
            'department_id' => $department->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'advisor@example.com',
            'role' => 'advisor',
        ]);
    }

    /**
     * Test registration does not allow admin role.
     */
    public function test_registration_does_not_allow_admin_role(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'university_id' => 'A-1234',
                'role' => 'admin',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /**
     * Test registration requires valid department.
     */
    public function test_registration_requires_valid_department(): void
    {
        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => 99999,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertGuest();
    }

    /**
     * Test registration requires password confirmation.
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'university_id' => '01112345',
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'different_password',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test registration page displays departments in dropdown.
     */
    public function test_registration_page_displays_departments(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('Computer Science');
        $response->assertSee('Electrical Engineering');
    }

    /**
     * Test registration enforces lowercase email.
     * The 'lowercase' validation rule in RegisteredUserController rejects non-lowercase emails.
     */
    public function test_registration_enforces_lowercase_email(): void
    {
        $department = Department::first();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'TEST@EXAMPLE.COM',
            'university_id' => '01112345',
            'role' => 'student',
            'department_id' => $department->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // The email validation rule includes 'lowercase' which rejects uppercase emails
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test successful registration redirects to dashboard.
     */
    public function test_successful_registration_redirects_to_dashboard(): void
    {
        $department = Department::first();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'university_id' => '01112345',
            'role' => 'student',
            'department_id' => $department->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test university_id has max length validation.
     */
    public function test_university_id_has_max_length_validation(): void
    {
        $department = Department::first();

        $response = $this
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'university_id' => str_repeat('a', 21), // Exceeds 20 char limit
                'role' => 'student',
                'department_id' => $department->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSessionHasErrors('university_id');
        $this->assertGuest();
    }
}
