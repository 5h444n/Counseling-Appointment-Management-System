<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentDocument;
use App\Models\Waitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a department for tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    // ========================================
    // User Model Tests
    // ========================================

    /**
     * Test User belongs to Department relationship.
     */
    public function test_user_belongs_to_department(): void
    {
        $department = Department::first();
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $user->department);
        $this->assertEquals($department->id, $user->department->id);
    }

    /**
     * Test User can have null department.
     */
    public function test_user_can_have_null_department(): void
    {
        $user = User::factory()->create(['department_id' => null]);

        $this->assertNull($user->department);
    }

    /**
     * Test advisor has many slots relationship.
     */
    public function test_advisor_has_many_slots(): void
    {
        $advisor = User::factory()->advisor()->create();

        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $this->assertCount(2, $advisor->slots);
        $this->assertTrue($advisor->slots->contains($slot1));
        $this->assertTrue($advisor->slots->contains($slot2));
    }

    /**
     * Test student has many appointments relationship.
     */
    public function test_student_has_many_appointments(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot1 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $slot2 = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(11, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment1 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot1->id,
            'purpose' => 'Test 1',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $appointment2 = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot2->id,
            'purpose' => 'Test 2',
            'status' => 'approved',
            'token' => 'CSE-1-B',
        ]);

        $this->assertCount(2, $student->appointments);
        $this->assertTrue($student->appointments->contains($appointment1));
        $this->assertTrue($student->appointments->contains($appointment2));
    }

    // ========================================
    // Department Model Tests
    // ========================================

    /**
     * Test Department has many users relationship.
     */
    public function test_department_has_many_users(): void
    {
        $department = Department::first();

        $user1 = User::factory()->create(['department_id' => $department->id]);
        $user2 = User::factory()->create(['department_id' => $department->id]);

        $this->assertCount(2, $department->users);
        $this->assertTrue($department->users->contains($user1));
        $this->assertTrue($department->users->contains($user2));
    }

    // ========================================
    // AppointmentSlot Model Tests
    // ========================================

    /**
     * Test AppointmentSlot belongs to advisor relationship.
     */
    public function test_appointment_slot_belongs_to_advisor(): void
    {
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $this->assertInstanceOf(User::class, $slot->advisor);
        $this->assertEquals($advisor->id, $slot->advisor->id);
    }

    /**
     * Test AppointmentSlot has one appointment relationship.
     */
    public function test_appointment_slot_has_one_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $this->assertInstanceOf(Appointment::class, $slot->appointment);
        $this->assertEquals($appointment->id, $slot->appointment->id);
    }

    /**
     * Test AppointmentSlot datetime casting.
     */
    public function test_appointment_slot_datetime_casting(): void
    {
        $advisor = User::factory()->advisor()->create();
        $startTime = Carbon::tomorrow()->setTime(10, 0);
        $endTime = Carbon::tomorrow()->setTime(10, 30);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'active',
            'is_recurring' => false,
        ]);

        $slot->refresh();

        $this->assertInstanceOf(Carbon::class, $slot->start_time);
        $this->assertInstanceOf(Carbon::class, $slot->end_time);
    }

    /**
     * Test AppointmentSlot boolean casting for is_recurring.
     */
    public function test_appointment_slot_boolean_casting(): void
    {
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'active',
            'is_recurring' => 1, // Pass as integer
        ]);

        $slot->refresh();

        $this->assertIsBool($slot->is_recurring);
        $this->assertTrue($slot->is_recurring);
    }

    // ========================================
    // Appointment Model Tests
    // ========================================

    /**
     * Test Appointment belongs to student relationship.
     */
    public function test_appointment_belongs_to_student(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $this->assertInstanceOf(User::class, $appointment->student);
        $this->assertEquals($student->id, $appointment->student->id);
    }

    /**
     * Test Appointment belongs to slot relationship.
     */
    public function test_appointment_belongs_to_slot(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $this->assertInstanceOf(AppointmentSlot::class, $appointment->slot);
        $this->assertEquals($slot->id, $appointment->slot->id);
    }

    /**
     * Test Appointment has many documents relationship.
     */
    public function test_appointment_has_many_documents(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $doc1 = AppointmentDocument::create([
            'appointment_id' => $appointment->id,
            'file_path' => '/storage/docs/doc1.pdf',
            'original_name' => 'document1.pdf',
        ]);

        $doc2 = AppointmentDocument::create([
            'appointment_id' => $appointment->id,
            'file_path' => '/storage/docs/doc2.pdf',
            'original_name' => 'document2.pdf',
        ]);

        $this->assertCount(2, $appointment->documents);
        $this->assertTrue($appointment->documents->contains($doc1));
        $this->assertTrue($appointment->documents->contains($doc2));
    }

    // ========================================
    // AppointmentDocument Model Tests
    // ========================================

    /**
     * Test AppointmentDocument belongs to appointment relationship.
     */
    public function test_appointment_document_belongs_to_appointment(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $appointment = Appointment::create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'purpose' => 'Test',
            'status' => 'pending',
            'token' => 'CSE-1-A',
        ]);

        $doc = AppointmentDocument::create([
            'appointment_id' => $appointment->id,
            'file_path' => '/storage/docs/doc1.pdf',
            'original_name' => 'document1.pdf',
        ]);

        $this->assertInstanceOf(Appointment::class, $doc->appointment);
        $this->assertEquals($appointment->id, $doc->appointment->id);
    }

    // ========================================
    // Waitlist Model Tests
    // ========================================

    /**
     * Test Waitlist belongs to slot relationship.
     */
    public function test_waitlist_belongs_to_slot(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $waitlist = Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student->id,
            'is_notified' => false,
        ]);

        $this->assertInstanceOf(AppointmentSlot::class, $waitlist->slot);
        $this->assertEquals($slot->id, $waitlist->slot->id);
    }

    /**
     * Test Waitlist belongs to student relationship.
     */
    public function test_waitlist_belongs_to_student(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(10, 30),
            'status' => 'blocked',
            'is_recurring' => false,
        ]);

        $waitlist = Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student->id,
            'is_notified' => false,
        ]);

        $this->assertInstanceOf(User::class, $waitlist->student);
        $this->assertEquals($student->id, $waitlist->student->id);
    }
}
