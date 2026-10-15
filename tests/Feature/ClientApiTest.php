<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Project;
use App\Models\ProjectDirector;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ShareAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_api_is_scoped_to_logged_in_client(): void
    {
        $project = Project::create(['name' => 'EDEN', 'slug' => 'eden-api', 'share_value' => 5000, 'total_shares' => 100]);
        $apartment = Apartment::create([
            'project_id' => $project->id, 'apartment_number' => '1-A', 'floor_label' => '1st', 'floor_number' => 1,
            'size_sqft' => 1000, 'facing' => 'North', 'per_sqft_rate' => 5000, 'apartment_price' => 5000000,
            'car_parking_cost' => 0, 'bike_parking_cost' => 0, 'utility_connection_cost' => 0, 'gross_total' => 5000000,
        ]);
        [$booking] = app(BookingService::class)->createBooking([
            'apartment_id' => $apartment->id, 'client_name' => 'Api Client', 'client_email' => 'api@example.com',
            'booking_amount' => 100000, 'payment_plan' => '12', 'booking_date' => '2026-08-06',
            'first_installment_date' => '2026-09-01', 'notes' => 'internal',
        ]);
        $client = $booking->client;
        $client->update(['password' => 'secret-pass']);
        app(ShareAssignmentService::class)->assignShares([
            'project_id' => $project->id, 'client_email' => $client->email, 'client_name' => $client->name,
            'shares_count' => 3, 'assigned_date' => '2026-08-06',
        ]);
        ProjectDirector::create(['project_id' => $project->id, 'client_id' => $client->id, 'designation' => 'MD']);

        // admins can't log in via the client API; wrong password rejected
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'password' => 'secret-pass']);
        $this->postJson('/api/client/login', ['email' => $admin->email, 'password' => 'secret-pass'])->assertStatus(422);
        $this->postJson('/api/client/login', ['email' => $client->email, 'password' => 'nope'])->assertStatus(422);
        $this->getJson('/api/client/bookings')->assertStatus(401);

        $token = $this->postJson('/api/client/login', ['email' => $client->email, 'password' => 'secret-pass'])
            ->assertOk()->assertJsonMissingPath('user.password')->json('token');
        $auth = ['Authorization' => "Bearer {$token}"];

        $this->getJson('/api/client/me', $auth)->assertOk()->assertJsonPath('user.email', 'api@example.com');
        $this->getJson('/api/client/bookings', $auth)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/client/bookings/{$booking->id}", $auth)->assertOk()
            ->assertJsonPath('data.reference', $booking->reference)
            ->assertJsonMissingPath('data.notes')
            ->assertJsonCount(13, 'data.installments');
        $this->getJson('/api/client/installments?status=paid', $auth)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/client/shares', $auth)->assertOk()->assertJsonPath('data.0.shares_count', 3);
        $this->getJson('/api/client/directorships', $auth)->assertOk()->assertJsonPath('data.0.designation', 'MD');
        $this->getJson('/api/client/summary', $auth)->assertOk()
            ->assertJsonPath('bookings_count', 1)->assertJsonPath('shares_count', 3)->assertJsonPath('directorships_count', 1);

        // another client can't see this booking
        $other = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $otherToken = $other->createToken('t')->plainTextToken;
        $this->app['auth']->forgetGuards(); // tests reuse the resolved user between requests
        $this->getJson("/api/client/bookings/{$booking->id}", ['Authorization' => "Bearer {$otherToken}"])->assertStatus(403);
        $this->getJson('/api/client/bookings', ['Authorization' => "Bearer {$otherToken}"])->assertJsonCount(0, 'data');

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/client/logout', [], $auth)->assertOk();
        $this->assertSame(0, $client->tokens()->count());
    }
}
