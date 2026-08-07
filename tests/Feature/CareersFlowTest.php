<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareersFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_job_posting(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/jobs', [
            'title' => 'Site Engineer',
            'department' => 'Construction',
            'location' => 'Dhaka',
            'employment_type' => 'Full-time',
            'description' => 'Oversee site operations.',
            'requirements' => '5+ years experience.',
            'status' => 'open',
        ]);

        $job = JobPosting::first();
        $response->assertRedirect(route('admin.jobs.show', $job));
        $this->assertDatabaseHas('job_postings', ['title' => 'Site Engineer', 'status' => 'open']);
        $this->assertNotEmpty($job->slug);
    }

    public function test_public_can_apply_to_a_specific_open_job(): void
    {
        Storage::fake('local');
        $job = JobPosting::create([
            'title' => 'Sales Executive',
            'description' => 'Sell apartments.',
            'status' => 'open',
        ]);

        $response = $this->postJson("/api/jobs/{$job->slug}/apply", [
            'name' => 'Applicant One',
            'email' => 'applicant@example.com',
            'phone' => '01700000001',
            'cover_letter' => 'I am interested.',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('job_applications', [
            'job_posting_id' => $job->id,
            'email' => 'applicant@example.com',
        ]);

        $application = JobApplication::first();
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_public_can_apply_generically_without_a_job(): void
    {
        Storage::fake('local');

        $response = $this->postJson('/api/jobs/apply', [
            'name' => 'Advance Applicant',
            'email' => 'advance@example.com',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('job_applications', [
            'job_posting_id' => null,
            'email' => 'advance@example.com',
        ]);
    }

    public function test_closed_jobs_are_not_listed_or_applicable(): void
    {
        $open = JobPosting::create(['title' => 'Open Role', 'description' => 'd', 'status' => 'open']);
        $closed = JobPosting::create(['title' => 'Closed Role', 'description' => 'd', 'status' => 'closed']);

        $response = $this->getJson('/api/jobs');
        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertTrue($titles->contains('Open Role'));
        $this->assertFalse($titles->contains('Closed Role'));

        $this->getJson("/api/jobs/{$closed->slug}")->assertNotFound();

        Storage::fake('local');
        $this->postJson("/api/jobs/{$closed->slug}/apply", [
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ])->assertNotFound();
    }
}
