<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaConversionsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.meta.pixel_id' => '28609325158717517',
            'services.meta.capi_access_token' => 'test-token',
            'services.meta.graph_api_version' => 'v26.0',
            'services.meta.capi_test_event_code' => null,
        ]);
    }

    public function test_enquiry_sends_a_deduplicatable_lead_with_hashed_user_data(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $response = $this->postJson('/api/enquiries', [
            'name' => 'Test Person',
            'email' => '  Test.Person@Example.COM ',
            'phone' => '01712345678',
            'project_name' => 'Eden by Armani',
            'property_type' => 'Residential Apartments',
            'event_id' => '3f2b8c1e-5a47-4d0b-9c1e-7b6a2d9f0e11',
            'fbp' => 'fb.1.1700000000000.1234567890',
            'fbc' => 'fb.1.1700000000000.AbCdEf',
            'event_source_url' => 'https://www.armaniproperties.com/projects/eden_by_armani',
        ]);

        $response->assertCreated();

        // Tracking fields must not leak into the stored lead.
        $enquiry = Enquiry::sole();
        $this->assertArrayNotHasKey('fbp', $enquiry->getAttributes());
        // Laravel's TrimStrings middleware already trimmed it on the way in.
        $this->assertSame('Test.Person@Example.COM', $enquiry->email);
        $this->assertArrayNotHasKey('event_id', $enquiry->getAttributes());

        Http::assertSent(function ($request) {
            $event = $request->data()['data'][0];

            $this->assertSame('https://graph.facebook.com/v26.0/28609325158717517/events', $request->url());
            $this->assertSame('test-token', $request->data()['access_token']);
            $this->assertSame('Lead', $event['event_name']);
            $this->assertSame('website', $event['action_source']);
            // Same id the browser pushed to the dataLayer — this is what dedupes.
            $this->assertSame('3f2b8c1e-5a47-4d0b-9c1e-7b6a2d9f0e11', $event['event_id']);
            $this->assertSame('Eden by Armani', $event['custom_data']['content_name']);
            $this->assertSame('Residential Apartments', $event['custom_data']['content_category']);

            // email: trimmed + lowercased, then SHA-256.
            $this->assertSame(hash('sha256', 'test.person@example.com'), $event['user_data']['em']);
            // phone: digits only, leading 0 replaced by the 880 country code.
            $this->assertSame(hash('sha256', '8801712345678'), $event['user_data']['ph']);
            $this->assertSame('fb.1.1700000000000.1234567890', $event['user_data']['fbp']);
            // Never send anything in the clear.
            $this->assertStringNotContainsString('Test.Person', $request->body());
            $this->assertStringNotContainsString('01712345678', $request->body());

            return true;
        });
    }

    public function test_site_visit_sends_schedule_and_includes_the_test_event_code(): void
    {
        config(['services.meta.capi_test_event_code' => 'TEST12345']);
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->postJson('/api/site-visits', [
            'name' => 'Test Person',
            'email' => 'a@b.com',
            'phone' => '+880 1712-345678',
            'event_id' => 'abc',
        ])->assertCreated();

        Http::assertSent(function ($request) {
            $this->assertSame('Schedule', $request->data()['data'][0]['event_name']);
            $this->assertSame('TEST12345', $request->data()['test_event_code']);
            $this->assertSame(hash('sha256', '8801712345678'), $request->data()['data'][0]['user_data']['ph']);

            return true;
        });
    }

    public function test_a_duplicate_resubmission_is_reported_only_once(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $form = ['name' => 'Keen Buyer', 'email' => 'keen@buyer.com', 'event_id' => 'first'];

        $this->postJson('/api/enquiries', $form)->assertCreated();
        $this->postJson('/api/enquiries', $form + ['event_id' => 'second'])->assertCreated();

        // Both enquiries are still saved for the sales team — only Meta is spared the dupe.
        $this->assertSame(2, Enquiry::count());
        Http::assertSentCount(1);

        // A different event for the same person is still its own conversion.
        $this->postJson('/api/site-visits', $form)->assertCreated();
        Http::assertSentCount(2);
    }

    public function test_click_relay_reports_contact_without_any_user_supplied_identity(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->postJson('/api/track', [
            'event' => 'whatsapp_click',
            'project_name' => 'Eden by Armani',
            'click_location' => 'floating_button',
            'event_id' => 'click-1',
            'fbp' => 'fb.1.1700000000000.1234567890',
            // An attacker-supplied hash must never be trusted or forwarded.
            'em' => hash('sha256', 'victim@example.com'),
        ])->assertNoContent();

        Http::assertSent(function ($request) {
            $event = $request->data()['data'][0];

            $this->assertSame('Contact', $event['event_name']);
            $this->assertSame('click-1', $event['event_id']);
            $this->assertSame('Eden by Armani', $event['custom_data']['content_name']);
            // Clicks carry no identity: only the cookie / IP / user-agent signals.
            $this->assertArrayNotHasKey('em', $event['user_data']);
            $this->assertArrayNotHasKey('ph', $event['user_data']);
            $this->assertSame('fb.1.1700000000000.1234567890', $event['user_data']['fbp']);
            $this->assertStringNotContainsString('victim@example.com', $request->body());

            return true;
        });
    }

    public function test_click_relay_refuses_events_it_does_not_own(): void
    {
        Http::fake();

        // Only the three click events are relayable — not Purchase, not Lead.
        $this->postJson('/api/track', ['event' => 'Purchase'])->assertStatus(422);
        $this->postJson('/api/track', ['event' => 'generate_lead'])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_invalid_submission_is_not_reported_to_meta(): void
    {
        Http::fake();

        $this->postJson('/api/enquiries', ['name' => 'No Email'])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_malformed_tracking_data_never_costs_us_a_real_lead(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

        $this->postJson('/api/enquiries', [
            'name' => 'Real Buyer',
            'email' => 'real@buyer.com',
            'event_source_url' => 'not a url',
            'event_id' => ['an', 'array'],
            'fbp' => str_repeat('x', 5000),
        ])->assertCreated();

        $this->assertSame(1, Enquiry::count());

        Http::assertSent(function ($request) {
            $event = $request->data()['data'][0];

            // Junk is dropped or clamped, not forwarded, and never sent as an event_id.
            $this->assertArrayNotHasKey('event_id', $event);
            $this->assertNull($event['event_source_url']);
            $this->assertSame(255, mb_strlen($event['user_data']['fbp']));

            return true;
        });
    }

    public function test_a_meta_failure_does_not_affect_the_visitor(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => 'nope'], 500)]);

        $this->postJson('/api/enquiries', [
            'name' => 'Test Person',
            'email' => 'a@b.com',
            'event_id' => 'abc',
        ])->assertCreated();

        $this->assertSame(1, Enquiry::count());
    }
}
