<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    public function test_admin_can_access_create_booking_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/bookings/create');

        $response->assertOk();
        $response->assertViewIs('admin.bookings.create');
        $response->assertViewHas('students');
        $response->assertViewHas('advisors');
    }

    public function test_non_admin_cannot_access_create_booking_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/bookings/create');

        $response->assertForbidden();
    }

    public function test_admin_can_get_available_slots_for_advisor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/bookings/slots?advisor_id=' . $advisor->id);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $slot->id]);
    }

    public function test_get_slots_requires_advisor_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/admin/bookings/slots');

        $response->assertStatus(422);
    }

    public function test_get_slots_validates_advisor_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/admin/bookings/slots?advisor_id=99999');

        $response->assertStatus(422);
    }

    public function test_get_slots_only_returns_active_future_slots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();
        
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subDays(1)->addMinutes(30),
            'status' => 'active',
        ]);
        
        AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $validSlot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/bookings/slots?advisor_id=' . $advisor->id);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $validSlot->id]);
    }

    public function test_admin_can_create_booking_for_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post('/admin/bookings', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic counseling',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic counseling',
            'status' => 'approved',
        ]);
        
        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'blocked',
        ]);
    }

    public function test_admin_booking_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/bookings', []);

        $response->assertSessionHasErrors(['student_id', 'slot_id', 'purpose']);
    }

    public function test_admin_booking_validates_student_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post('/admin/bookings', [
            'student_id' => 99999,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
        ]);

        $response->assertSessionHasErrors(['student_id']);
    }

    public function test_admin_booking_validates_slot_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->post('/admin/bookings', [
            'student_id' => $student->id,
            'slot_id' => 99999,
            'purpose' => 'Test',
        ]);

        $response->assertSessionHasErrors(['slot_id']);
    }

    public function test_admin_booking_prevents_booking_inactive_slot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);

        $response = $this->actingAs($admin)->post('/admin/bookings', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Academic counseling',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseMissing('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
        ]);
    }

    public function test_admin_booking_generates_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post('/admin/bookings', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
        ]);

        $appointment = Appointment::where('student_id', $student->id)->first();
        
        $this->assertNotNull($appointment->token);
        $this->assertStringStartsWith('GEN-' . $student->id . '-', $appointment->token);
    }

    public function test_admin_can_delete_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->delete('/admin/bookings/' . $appointment->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
        $this->assertDatabaseHas('appointment_slots', [
            'id' => $slot->id,
            'status' => 'active',
        ]);
    }

    public function test_delete_booking_requires_existing_appointment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete('/admin/bookings/99999');

        $response->assertNotFound();
    }

    public function test_non_admin_cannot_create_booking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->post('/admin/bookings', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
        ]);

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_booking(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'blocked',
        ]);
        
        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'token' => 'TEST-TOKEN',
            'purpose' => 'Test',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($student)->delete('/admin/bookings/' . $appointment->id);

        $response->assertForbidden();
    }
}
