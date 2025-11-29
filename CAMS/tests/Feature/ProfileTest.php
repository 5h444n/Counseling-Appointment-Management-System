<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $department = Department::create(['name' => 'Computer Science', 'code' => 'CS']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $department = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $department = Department::create(['name' => 'Science', 'code' => 'SCI']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $department = Department::create(['name' => 'Arts', 'code' => 'ART']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $department = Department::create(['name' => 'Business', 'code' => 'BUS']);
        $user = User::factory()->create(['department_id' => $department->id]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_sidebar_displays_user_information(): void
    {
        $department = Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'university_id' => '011123456',
            'department_id' => $department->id,
            'role' => 'student',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee('Computer Science');
        $response->assertSee('011123456');
        $response->assertSee('student');
    }

    public function test_profile_sidebar_displays_na_fallback_for_missing_university_id(): void
    {
        $department = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $user = User::factory()->create([
            'university_id' => null,
            'department_id' => $department->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('N/A');
    }

    public function test_profile_sidebar_displays_general_fallback_for_missing_department(): void
    {
        $user = User::factory()->create([
            'department_id' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('General');
    }
}
