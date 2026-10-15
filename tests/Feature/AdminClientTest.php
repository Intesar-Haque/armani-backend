<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_registers_client(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/clients')->assertOk();

        $this->actingAs($admin)->post('/admin/clients', [
            'client_name' => 'Jane', 'client_email' => 'jane@example.com',
            'nid' => '1234567890', 'father_name' => 'John', 'mother_name' => 'Mary', 'address' => 'Dhaka',
        ])->assertRedirect('/admin/clients')->assertSessionHas('status');

        $jane = User::where('email', 'jane@example.com')->first();
        $this->assertTrue($jane->isClient());
        $this->assertSame(['1234567890', 'John', 'Mary', 'Dhaka'], [$jane->nid, $jane->father_name, $jane->mother_name, $jane->address]);

        $this->actingAs($admin)->post('/admin/clients', [
            'client_name' => 'Dup', 'client_email' => $admin->email,
        ])->assertSessionHasErrors('client_email');
    }
}
