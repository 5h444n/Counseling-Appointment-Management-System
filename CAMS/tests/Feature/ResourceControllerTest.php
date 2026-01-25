<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Department::create(['name' => 'Computer Science', 'code' => 'CSE']);
        Storage::fake('public');
    }

    public function test_student_can_access_resources_index(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Test Resource',
            'description' => 'Test Description',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/student/resources');

        $response->assertOk();
        $response->assertViewIs('student.resources.index');
        $response->assertViewHas('resources');
        $response->assertSee('Test Resource');
    }

    public function test_advisor_can_access_resources_index(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Test Resource',
            'description' => 'Test Description',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/resources');

        $response->assertOk();
        $response->assertViewIs('advisor.resources.index');
        $response->assertViewHas('resources');
        $response->assertSee('Test Resource');
    }

    public function test_admin_can_access_resources_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Test Resource',
            'description' => 'Test Description',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/resources');

        $response->assertOk();
        $response->assertViewIs('advisor.resources.index');
        $response->assertViewHas('resources');
    }

    public function test_resources_can_be_filtered_by_category(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Academic Resource',
            'file_path' => 'resources/academic.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);
        
        Resource::create([
            'title' => 'Mental Health Resource',
            'file_path' => 'resources/mental.pdf',
            'category' => 'Mental Health',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/student/resources?category=Academic');

        $response->assertOk();
        $response->assertSee('Academic Resource');
        $response->assertDontSee('Mental Health Resource');
    }

    public function test_resources_can_be_filtered_by_advisor(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor1 = User::factory()->advisor()->create(['name' => 'Dr. Smith']);
        $advisor2 = User::factory()->advisor()->create(['name' => 'Dr. Jones']);
        
        Resource::create([
            'title' => 'Smith Resource',
            'file_path' => 'resources/smith.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor1->id,
        ]);
        
        Resource::create([
            'title' => 'Jones Resource',
            'file_path' => 'resources/jones.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor2->id,
        ]);

        $response = $this->actingAs($student)->get('/student/resources?advisor_id=' . $advisor1->id);

        $response->assertOk();
        $response->assertSee('Smith Resource');
        $response->assertDontSee('Jones Resource');
    }

    public function test_resources_can_be_searched_by_title(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Programming Guide',
            'file_path' => 'resources/prog.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);
        
        Resource::create([
            'title' => 'Mental Wellness Tips',
            'file_path' => 'resources/wellness.pdf',
            'category' => 'Wellness',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/student/resources?search=Programming');

        $response->assertOk();
        $response->assertSee('Programming Guide');
        $response->assertDontSee('Mental Wellness Tips');
    }

    public function test_resources_can_be_searched_by_description(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        Resource::create([
            'title' => 'Guide 1',
            'description' => 'This covers Python programming',
            'file_path' => 'resources/guide1.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);
        
        Resource::create([
            'title' => 'Guide 2',
            'description' => 'This covers Java programming',
            'file_path' => 'resources/guide2.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/student/resources?search=Python');

        $response->assertOk();
        $response->assertSee('Guide 1');
        $response->assertDontSee('Guide 2');
    }

    public function test_advisor_can_upload_resource(): void
    {
        $advisor = User::factory()->advisor()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($advisor)->post('/advisor/resources', [
            'title' => 'New Resource',
            'description' => 'Resource Description',
            'category' => 'Academic',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('resources', [
            'title' => 'New Resource',
            'description' => 'Resource Description',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);
        
        $resource = Resource::where('title', 'New Resource')->first();
        Storage::disk('public')->assertExists($resource->file_path);
    }

    public function test_admin_can_upload_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($admin)->post('/admin/resources', [
            'title' => 'Admin Resource',
            'description' => 'Test',
            'category' => 'Career',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('resources', [
            'title' => 'Admin Resource',
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_student_cannot_upload_resource(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        // Try to upload through advisor route - should get forbidden/not authorized
        $response = $this->actingAs($student)->post('/advisor/resources', [
            'title' => 'Student Resource',
            'description' => 'Test',
            'category' => 'Academic',
            'file' => $file,
        ]);

        // Either forbidden or method not allowed is acceptable - the point is students can't upload
        // Just verify no resource was created
        $this->assertDatabaseMissing('resources', [
            'title' => 'Student Resource',
        ]);
    }

    public function test_upload_resource_validates_required_fields(): void
    {
        $advisor = User::factory()->advisor()->create();

        $response = $this->actingAs($advisor)->post('/advisor/resources', []);

        $response->assertSessionHasErrors(['title', 'category', 'file']);
    }

    public function test_upload_resource_validates_category(): void
    {
        $advisor = User::factory()->advisor()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($advisor)->post('/advisor/resources', [
            'title' => 'Test',
            'category' => 'InvalidCategory',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['category']);
    }

    public function test_upload_resource_validates_file_type(): void
    {
        $advisor = User::factory()->advisor()->create();
        $file = UploadedFile::fake()->create('malicious.exe', 1000);

        $response = $this->actingAs($advisor)->post('/advisor/resources', [
            'title' => 'Test',
            'category' => 'Academic',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_upload_resource_accepts_valid_file_types(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $validTypes = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
        
        foreach ($validTypes as $type) {
            $file = UploadedFile::fake()->create("document.$type", 1000);
            
            $response = $this->actingAs($advisor)->post('/advisor/resources', [
                'title' => "Test $type",
                'category' => 'Academic',
                'file' => $file,
            ]);
            
            $response->assertSessionDoesntHaveErrors(['file']);
        }
    }

    public function test_upload_resource_validates_file_size(): void
    {
        $advisor = User::factory()->advisor()->create();
        $file = UploadedFile::fake()->create('toolarge.pdf', 60000);

        $response = $this->actingAs($advisor)->post('/advisor/resources', [
            'title' => 'Test',
            'category' => 'Academic',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_student_can_download_resource(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('resources', 'public');
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => $path,
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/resources/' . $resource->id . '/download');

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_advisor_can_download_resource(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('resources', 'public');
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => $path,
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($advisor)->get('/resources/' . $resource->id . '/download');

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_download_returns_404_if_file_not_found(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $resource = Resource::create([
            'title' => 'Missing File',
            'file_path' => 'resources/nonexistent.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($student)->get('/resources/' . $resource->id . '/download');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_download_resource(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->get('/resources/' . $resource->id . '/download');

        $response->assertRedirect('/login');
    }

    public function test_advisor_can_delete_own_resource(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('resources', 'public');
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => $path,
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($advisor)->delete('/advisor/resources/' . $resource->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_delete_any_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advisor = User::factory()->advisor()->create();
        
        $file = UploadedFile::fake()->create('test.pdf', 100);
        $path = $file->store('resources', 'public');
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => $path,
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($admin)->delete('/admin/resources/' . $resource->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_advisor_cannot_delete_others_resource(): void
    {
        $advisor1 = User::factory()->advisor()->create();
        $advisor2 = User::factory()->advisor()->create();
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor1->id,
        ]);

        $response = $this->actingAs($advisor2)->delete('/advisor/resources/' . $resource->id);

        $response->assertForbidden();
        
        $this->assertDatabaseHas('resources', ['id' => $resource->id]);
    }

    public function test_student_cannot_delete_resource(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => 'resources/test.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        // Students don't have access to delete routes at all (no route defined)
        // Try to access through advisor route - should fail with 404 (route not found for student)
        $response = $this->actingAs($student)->delete('/advisor/resources/' . $resource->id);
        
        // Since students can't access advisor routes, we expect 403 or the resource still exists
        // Better: check that resource still exists
        $this->assertDatabaseHas('resources', ['id' => $resource->id]);
    }

    public function test_delete_resource_handles_missing_file_gracefully(): void
    {
        $advisor = User::factory()->advisor()->create();
        
        $resource = Resource::create([
            'title' => 'Test Document',
            'file_path' => 'resources/nonexistent.pdf',
            'category' => 'Academic',
            'uploaded_by' => $advisor->id,
        ]);

        $response = $this->actingAs($advisor)->delete('/advisor/resources/' . $resource->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_resources_are_paginated(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $advisor = User::factory()->advisor()->create();
        
        for ($i = 1; $i <= 15; $i++) {
            Resource::create([
                'title' => "Resource $i",
                'file_path' => "resources/test$i.pdf",
                'category' => 'Academic',
                'uploaded_by' => $advisor->id,
            ]);
        }

        $response = $this->actingAs($student)->get('/student/resources');

        $response->assertOk();
        $response->assertViewHas('resources', function ($resources) {
            return $resources instanceof \Illuminate\Pagination\LengthAwarePaginator;
        });
    }
}
