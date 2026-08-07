<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDirector;
use App\Models\User;
use App\Services\ShareAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SharesAndDirectorsTest extends TestCase
{
    use RefreshDatabase;

    private function makeSharedProject(int $totalShares = 100, float $shareValue = 5000): Project
    {
        return Project::create([
            'name' => 'EDEN by Armani',
            'slug' => 'eden-shares-test',
            'share_value' => $shareValue,
            'total_shares' => $totalShares,
        ]);
    }

    public function test_admin_can_configure_shares_on_a_project(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::create(['name' => 'Skyline Residences', 'base_price' => 1000000]);

        $response = $this->actingAs($admin)->put("/admin/projects/{$project->id}", [
            'name' => $project->name,
            'base_price' => 1000000,
            'status' => 'active',
            'share_value' => 2500,
            'total_shares' => 200,
        ]);

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertSame(200, $project->fresh()->total_shares);
        $this->assertSame('2500.00', $project->fresh()->share_value);
    }

    public function test_shares_can_be_assigned_to_a_new_client_and_deducted_from_remaining(): void
    {
        $project = $this->makeSharedProject();

        [$assignment, $password] = app(ShareAssignmentService::class)->assignShares([
            'project_id' => $project->id,
            'client_name' => 'Share Holder',
            'client_email' => 'holder@example.com',
            'shares_count' => 30,
            'assigned_date' => '2026-08-06',
        ]);

        $this->assertNotNull($password);
        $this->assertSame(30, $assignment->shares_count);
        $this->assertSame('150000.00', $assignment->total_amount);
        $this->assertDatabaseHas('users', ['email' => 'holder@example.com', 'role' => User::ROLE_CLIENT]);
        $this->assertSame(70, $project->fresh()->sharesRemaining());
    }

    public function test_share_assignment_rejects_allocation_beyond_remaining_shares(): void
    {
        $project = $this->makeSharedProject(totalShares: 50);

        app(ShareAssignmentService::class)->assignShares([
            'project_id' => $project->id,
            'client_name' => 'First Holder',
            'client_email' => 'first@example.com',
            'shares_count' => 40,
            'assigned_date' => '2026-08-06',
        ]);

        $this->expectException(ValidationException::class);

        app(ShareAssignmentService::class)->assignShares([
            'project_id' => $project->id,
            'client_name' => 'Second Holder',
            'client_email' => 'second@example.com',
            'shares_count' => 20,
            'assigned_date' => '2026-08-06',
        ]);
    }

    public function test_admin_can_assign_an_existing_client_as_director(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::create(['name' => 'Director Towers', 'base_price' => 5000000]);
        $client = User::factory()->create(['role' => User::ROLE_CLIENT, 'email' => 'director@example.com']);

        $response = $this->actingAs($admin)->post('/admin/project-directors', [
            'project_id' => $project->id,
            'client_id' => $client->id,
            'client_email' => $client->email,
            'designation' => 'Managing Director',
            'appointed_date' => '2026-08-06',
        ]);

        $response->assertRedirect(route('admin.project-directors.index'));
        $this->assertDatabaseHas('project_directors', [
            'project_id' => $project->id,
            'client_id' => $client->id,
            'designation' => 'Managing Director',
        ]);
    }

    public function test_admin_can_assign_a_new_client_as_director(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::create(['name' => 'New Director Towers', 'base_price' => 5000000]);

        $response = $this->actingAs($admin)->post('/admin/project-directors', [
            'project_id' => $project->id,
            'client_name' => 'Fresh Director',
            'client_email' => 'fresh-director@example.com',
            'designation' => 'Executive Director',
        ]);

        $response->assertRedirect(route('admin.project-directors.index'));
        $this->assertDatabaseHas('users', ['email' => 'fresh-director@example.com', 'role' => User::ROLE_CLIENT]);
        $this->assertSame(1, ProjectDirector::where('project_id', $project->id)->count());
    }
}
