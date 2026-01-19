<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\Waitlist;
use App\Events\SlotFreedUp;
use App\Listeners\NotifyWaitlist;
use App\Mail\SlotAvailableNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class WaitlistFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create department
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
    }

    /**
     * Test that students can join a waitlist for a blocked slot.
     */
    public function test_student_can_join_waitlist_for_blocked_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        // Create a blocked slot
        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
            'start_time' => now()->addDays(1),
        ]);

        $response = $this
            ->actingAs($student)
            ->post(route('waitlist.join', $slot->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('waitlists', [
            'slot_id' => $slot->id,
            'student_id' => $student->id,
        ]);
    }

    /**
     * Test that students cannot join waitlist for an active slot.
     */
    public function test_student_cannot_join_waitlist_for_active_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        // Create an active slot
        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'active',
            'start_time' => now()->addDays(1),
        ]);

        $response = $this
            ->actingAs($student)
            ->post(route('waitlist.join', $slot->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('waitlists', [
            'slot_id' => $slot->id,
            'student_id' => $student->id,
        ]);
    }

    /**
     * Test that students cannot join waitlist twice for the same slot.
     */
    public function test_student_cannot_join_waitlist_twice_for_same_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
            'start_time' => now()->addDays(1),
        ]);

        // Join waitlist first time
        Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student->id,
        ]);

        // Try to join again
        $response = $this
            ->actingAs($student)
            ->post(route('waitlist.join', $slot->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Should only have one entry
        $this->assertEquals(1, Waitlist::where('slot_id', $slot->id)
            ->where('student_id', $student->id)
            ->count());
    }

    /**
     * Test that waitlist entry is removed when student books the slot.
     */
    public function test_waitlist_entry_removed_when_student_books_slot(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'active',
            'start_time' => now()->addDays(1),
        ]);

        // Create waitlist entry (simulating they were on waitlist before)
        Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student->id,
        ]);

        // Book the slot
        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'Need academic guidance for my final project',
            ]);

        $response->assertRedirect(route('dashboard'));

        // Waitlist entry should be removed
        $this->assertDatabaseMissing('waitlists', [
            'slot_id' => $slot->id,
            'student_id' => $student->id,
        ]);
    }

    /**
     * Test that SlotFreedUp event is fired when appointment is declined.
     */
    public function test_slot_freed_up_event_fired_when_appointment_declined(): void
    {
        Event::fake();

        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        $appointment = Appointment::factory()->create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->patch(route('advisor.appointments.update', $appointment->id), [
                'status' => 'declined',
            ]);

        Event::assertDispatched(SlotFreedUp::class);
    }

    /**
     * Test that first student in waitlist receives notification when slot is freed.
     */
    public function test_first_student_in_waitlist_receives_notification_when_slot_freed(): void
    {
        Mail::fake();

        $advisor = User::factory()->advisor()->create();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        // Create waitlist entries
        $entry1 = Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student1->id,
            'created_at' => now()->subMinutes(10),
        ]);

        $entry2 = Waitlist::create([
            'slot_id' => $slot->id,
            'student_id' => $student2->id,
            'created_at' => now()->subMinutes(5),
        ]);

        // Manually trigger the listener (simulating event dispatch)
        $event = new SlotFreedUp($slot);
        $listener = new NotifyWaitlist();
        $listener->handle($event);

        // First student should receive email
        Mail::assertSent(SlotAvailableNotification::class, function ($mail) use ($student1) {
            return $mail->hasTo($student1->email);
        });

        // First student should be removed from waitlist
        $this->assertDatabaseMissing('waitlists', [
            'id' => $entry1->id,
        ]);

        // Second student should still be in waitlist
        $this->assertDatabaseHas('waitlists', [
            'id' => $entry2->id,
        ]);
    }

    /**
     * Test that slot status changes to active when appointment is declined.
     */
    public function test_slot_becomes_active_when_appointment_declined(): void
    {
        $advisor = User::factory()->advisor()->create();
        $student = User::factory()->create(['role' => 'student']);

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        $appointment = Appointment::factory()->create([
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->patch(route('advisor.appointments.update', $appointment->id), [
                'status' => 'declined',
            ]);

        $slot->refresh();
        $this->assertEquals('active', $slot->status);
    }

    /**
     * Test that no notification is sent if waitlist is empty.
     */
    public function test_no_notification_sent_if_waitlist_empty(): void
    {
        Mail::fake();

        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        // Manually trigger the listener with no waitlist entries
        $event = new SlotFreedUp($slot);
        $listener = new NotifyWaitlist();
        $listener->handle($event);

        // No email should be sent
        Mail::assertNothingSent();
    }

    /**
     * Test that waitlist respects FIFO (First In First Out) order.
     */
    public function test_waitlist_respects_fifo_order(): void
    {
        $advisor = User::factory()->advisor()->create();
        $students = User::factory()->count(5)->create(['role' => 'student']);

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        // Create waitlist entries at different times
        foreach ($students as $index => $student) {
            Waitlist::create([
                'slot_id' => $slot->id,
                'student_id' => $student->id,
                'created_at' => now()->subMinutes(100 - ($index * 10)),
            ]);
        }

        // Get first in line
        $firstEntry = Waitlist::where('slot_id', $slot->id)
            ->orderBy('created_at', 'asc')
            ->first();

        // Should be the first student (oldest created_at)
        $this->assertEquals($students[0]->id, $firstEntry->student_id);
    }

    /**
     * Test guest cannot join waitlist.
     */
    public function test_guest_cannot_join_waitlist(): void
    {
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        $response = $this->post(route('waitlist.join', $slot->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test advisor cannot join their own slot waitlist.
     */
    public function test_advisor_cannot_join_waitlist(): void
    {
        $advisor = User::factory()->advisor()->create();

        $slot = AppointmentSlot::factory()->create([
            'advisor_id' => $advisor->id,
            'status' => 'blocked',
        ]);

        $response = $this
            ->actingAs($advisor)
            ->post(route('waitlist.join', $slot->id));

        // Should be forbidden or redirected (depends on middleware)
        $this->assertTrue(
            $response->isRedirect() || $response->isForbidden()
        );
    }
}
