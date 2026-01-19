<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create departments for the tests
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
        
        // Fake the storage
        Storage::fake('public');
    }

    /**
     * Test that a student can book an appointment with a PDF document.
     */
    public function test_student_can_book_appointment_with_pdf_document(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1024); // 1MB PDF

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Verify appointment was created
        $this->assertDatabaseHas('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
            'status' => 'pending',
        ]);

        // Verify document was saved
        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertNotNull($appointment);
        
        $this->assertDatabaseHas('appointment_documents', [
            'appointment_id' => $appointment->id,
            'original_name' => 'document.pdf',
        ]);

        // Verify file was stored
        $document = AppointmentDocument::where('appointment_id', $appointment->id)->first();
        Storage::disk('public')->assertExists($document->file_path);
    }

    /**
     * Test that a student can book an appointment with a DOCX document.
     */
    public function test_student_can_book_appointment_with_docx_document(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('advising-sheet.docx', 500);

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        // Verify document was saved with correct original name
        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertDatabaseHas('appointment_documents', [
            'appointment_id' => $appointment->id,
            'original_name' => 'advising-sheet.docx',
        ]);
    }

    /**
     * Test that a student can book an appointment with a PPTX presentation.
     */
    public function test_student_can_book_appointment_with_pptx_presentation(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('presentation.pptx', 2048); // 2MB

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        // Verify document was saved with correct original name
        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertDatabaseHas('appointment_documents', [
            'appointment_id' => $appointment->id,
            'original_name' => 'presentation.pptx',
        ]);
    }

    /**
     * Test that a student can book an appointment with an XLSX spreadsheet.
     */
    public function test_student_can_book_appointment_with_xlsx_spreadsheet(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('grades.xlsx', 1024); // 1MB

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        // Verify document was saved with correct original name
        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertDatabaseHas('appointment_documents', [
            'appointment_id' => $appointment->id,
            'original_name' => 'grades.xlsx',
        ]);
    }

    /**
     * Test that a student can book an appointment with an image (JPG).
     */
    public function test_student_can_book_appointment_with_jpg_image(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('scan.jpg', 1200, 1600)->size(2048); // 2MB

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertDatabaseHas('appointment_documents', [
            'appointment_id' => $appointment->id,
            'original_name' => 'scan.jpg',
        ]);
    }

    /**
     * Test that a student can book an appointment without a document.
     */
    public function test_student_can_book_appointment_without_document(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
            ]);

        $response->assertRedirect(route('dashboard'));

        // Verify appointment was created
        $appointment = Appointment::where('student_id', $student->id)->first();
        $this->assertNotNull($appointment);

        // Verify no document was saved
        $this->assertDatabaseMissing('appointment_documents', [
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Test that invalid file types are rejected.
     */
    public function test_invalid_file_types_are_rejected(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        // Try to upload an executable file
        $file = UploadedFile::fake()->create('malware.exe', 100);

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertSessionHasErrors('document');

        // Verify appointment was NOT created
        $this->assertDatabaseMissing('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
        ]);
    }

    /**
     * Test that files larger than 100MB are rejected.
     */
    public function test_files_larger_than_100mb_are_rejected(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        // Try to upload a file larger than 100MB
        $file = UploadedFile::fake()->create('large-file.pdf', 102500); // 100.09MB (slightly over limit)

        $response = $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $response->assertSessionHasErrors('document');

        // Verify appointment was NOT created
        $this->assertDatabaseMissing('appointments', [
            'student_id' => $student->id,
            'slot_id' => $slot->id,
        ]);
    }

    /**
     * Test that document relationship works correctly.
     */
    public function test_appointment_document_relationship(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
        $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

        $slot = AppointmentSlot::create([
            'advisor_id' => $advisor->id,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addMinutes(30),
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->create('test-doc.pdf', 500);

        $this
            ->actingAs($student)
            ->post(route('student.book.store'), [
                'slot_id' => $slot->id,
                'purpose' => 'I need academic counseling regarding my course selection.',
                'document' => $file,
            ]);

        $appointment = Appointment::where('student_id', $student->id)->first();
        
        // Test relationship
        $this->assertCount(1, $appointment->documents);
        $this->assertEquals('test-doc.pdf', $appointment->documents->first()->original_name);
        
        // Test reverse relationship
        $document = $appointment->documents->first();
        $this->assertEquals($appointment->id, $document->appointment->id);
    }

    /**
     * Test that multiple file formats are accepted.
     */
    public function test_multiple_accepted_formats(): void
    {
        $cseDept = Department::where('code', 'CSE')->first();
        $acceptedFormats = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
        
        foreach ($acceptedFormats as $index => $format) {
            // Create a new student for each test to avoid rate limiting
            $student = User::factory()->create(['role' => 'student', 'department_id' => $cseDept->id]);
            $advisor = User::factory()->advisor()->create(['department_id' => $cseDept->id]);

            $slot = AppointmentSlot::create([
                'advisor_id' => $advisor->id,
                'start_time' => Carbon::now()->addDays($index + 1),
                'end_time' => Carbon::now()->addDays($index + 1)->addMinutes(30),
                'status' => 'active',
            ]);

            $file = UploadedFile::fake()->create("document.{$format}", 500);

            $response = $this
                ->actingAs($student)
                ->post(route('student.book.store'), [
                    'slot_id' => $slot->id,
                    'purpose' => 'I need academic counseling regarding my course selection.',
                    'document' => $file,
                ]);

            $response->assertRedirect(route('dashboard'));
            $response->assertSessionHasNoErrors();
        }

        // Verify all documents were saved
        $this->assertEquals(count($acceptedFormats), AppointmentDocument::count());
    }
}
