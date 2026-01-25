<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_student_can_submit_feedback(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Academic',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
            'comment' => 'Excellent counseling session. Very helpful!',
            'is_anonymous' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('feedback', [
            'appointment_id' => $appointment->id,
            'student_id' => $student->id,
            'advisor_id' => $advisor->id,
            'rating' => 5,
            'comment' => 'Excellent counseling session. Very helpful!',
            'is_anonymous' => false,
        ]);
    }

    public function test_student_can_submit_anonymous_feedback(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Academic',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 4,
            'comment' => 'Good session',
            'is_anonymous' => true,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('feedback', [
            'appointment_id' => $appointment->id,
            'is_anonymous' => true,
        ]);
    }

    public function test_feedback_validates_required_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post('/feedback', []);

        $response->assertSessionHasErrors(['appointment_id', 'rating']);
    }

    public function test_feedback_validates_appointment_exists(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => 99999,
            'rating' => 5,
        ]);

        $response->assertSessionHasErrors(['appointment_id']);
    }

    public function test_feedback_validates_rating_range(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 6,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_feedback_validates_minimum_rating(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 0,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_feedback_accepts_all_valid_ratings(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        for ($rating = 1; $rating <= 5; $rating++) {
            $slot = AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => Carbon::now()->subDays($rating),
                'end_time' => Carbon::now()->subDays($rating)->addMinutes(30),
                'status' => 'blocked',
            ]);
            
            $appointment = Appointment::create([
                'student_id' => $student->id,
                'slot_id' => $slot->id,
                'token' => "TOKEN-$rating",
                'purpose' => 'Test',
                'status' => 'completed',
            ]);

            $response = $this->actingAs($student)->post('/feedback', [
                'appointment_id' => $appointment->id,
                'rating' => $rating,
            ]);

            $response->assertSessionDoesntHaveErrors(['rating']);
        }
    }

    public function test_feedback_comment_is_optional(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_feedback_validates_comment_max_length(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
            'comment' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors(['comment']);
    }

    public function test_student_can_only_rate_own_appointments(): void
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student1->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($student2)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_student_cannot_rate_appointment_twice(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);
        
        Feedback::create([
            'appointment_id' => $appointment->id,
            'student_id' => $student->id,
            'advisor_id' => $advisor->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_advisor_cannot_submit_feedback(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($advisor)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_cannot_submit_feedback(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_submit_feedback(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $response = $this->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_feedback_stores_correct_advisor_id(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('feedback', [
            'appointment_id' => $appointment->id,
            'advisor_id' => $advisor->id,
        ]);
    }

    public function test_is_anonymous_defaults_to_false(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'completed',
        ]);

        $this->actingAs($student)->post('/feedback', [
            'appointment_id' => $appointment->id,
            'rating' => 5,
        ]);

        $this->assertDatabaseHas('feedback', [
            'appointment_id' => $appointment->id,
            'is_anonymous' => false,
        ]);
    }
}
