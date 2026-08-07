<?php

namespace Tests\Feature;

use App\Console\Commands\SendInstallmentReminders;
use App\Mail\InstallmentReminderMail;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Project;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeApartment(float $gross = 10450000): Apartment
    {
        $project = Project::create(['name' => 'EDEN by Armani', 'slug' => 'eden-test']);

        return Apartment::create([
            'project_id' => $project->id,
            'apartment_number' => '4-E',
            'floor_label' => '4th Floor',
            'floor_number' => 4,
            'size_sqft' => 1250,
            'facing' => 'North',
            'per_sqft_rate' => 6800,
            'apartment_price' => 8500000,
            'car_parking_cost' => 800000,
            'bike_parking_cost' => 150000,
            'utility_connection_cost' => 1000000,
            'gross_total' => $gross,
        ]);
    }

    public function test_customer_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'phone' => '01700000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'customer@example.com', 'role' => User::ROLE_CLIENT]);
    }

    public function test_installment_plan_adds_surcharge_on_remaining_amount(): void
    {
        $apartment = $this->makeApartment();

        [$booking] = app(BookingService::class)->createBooking([
            'apartment_id' => $apartment->id,
            'client_name' => 'Plan Client',
            'client_email' => 'plan@example.com',
            'booking_amount' => 100000,
            'down_payment' => 2000000,
            'payment_plan' => '24',
            'booking_date' => '2026-08-06',
            'first_installment_date' => '2026-09-01',
        ]);

        // remaining = 10,450,000 - 2,100,000 = 8,350,000; 5% additional = 417,500
        $this->assertSame('417500.00', $booking->surcharge_amount);
        $this->assertSame('10867500.00', $booking->total_amount);
        $this->assertSame(Apartment::STATUS_BOOKED, $apartment->fresh()->status);

        // booking money + down payment recorded as paid, plus 24 monthly installments
        $this->assertCount(26, $booking->installments);
        $unpaid = $booking->installments->whereNull('paid_at');
        $this->assertCount(24, $unpaid);
        $this->assertEqualsWithDelta(8767500.0, (float) $unpaid->sum('amount_due'), 0.01);
    }

    public function test_full_payment_gets_five_percent_discount(): void
    {
        $apartment = $this->makeApartment();

        [$booking] = app(BookingService::class)->createBooking([
            'apartment_id' => $apartment->id,
            'client_name' => 'Full Payer',
            'client_email' => 'full@example.com',
            'booking_amount' => 100000,
            'down_payment' => 0,
            'payment_plan' => 'full',
            'booking_date' => '2026-08-06',
            'first_installment_date' => '2026-08-20',
        ]);

        // discount = 5% of 10,450,000 = 522,500; payable = 9,927,500
        $this->assertSame('522500.00', $booking->discount_amount);
        $this->assertSame('9927500.00', $booking->total_amount);

        $final = $booking->installments->whereNull('paid_at')->first();
        $this->assertSame('9827500.00', $final->amount_due);

        // confirming payment completes the booking and marks the apartment sold
        app(BookingService::class)->markInstallmentPaid($final, ['paid_amount' => (float) $final->amount_due]);
        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertSame(Apartment::STATUS_SOLD, $apartment->fresh()->status);
    }

    public function test_booked_apartment_cannot_be_booked_again(): void
    {
        $apartment = $this->makeApartment();
        $apartment->update(['status' => Apartment::STATUS_BOOKED]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(BookingService::class)->createBooking([
            'apartment_id' => $apartment->id,
            'client_name' => 'Second Client',
            'client_email' => 'second@example.com',
            'booking_amount' => 100000,
            'payment_plan' => '12',
            'booking_date' => '2026-08-06',
            'first_installment_date' => '2026-09-01',
        ]);
    }

    public function test_reminder_emails_are_sent_before_due_date_and_not_duplicated(): void
    {
        Mail::fake();
        $apartment = $this->makeApartment();

        [$booking] = app(BookingService::class)->createBooking([
            'apartment_id' => $apartment->id,
            'client_name' => 'Reminder Client',
            'client_email' => 'remind@example.com',
            'booking_amount' => 100000,
            'payment_plan' => '12',
            'booking_date' => today()->toDateString(),
            'first_installment_date' => today()->addDays(SendInstallmentReminders::REMINDER_DAYS[0])->toDateString(),
        ]);

        $this->artisan('installments:send-reminders')->assertSuccessful();
        Mail::assertSent(InstallmentReminderMail::class, 1);

        // running again the same day does not resend
        $this->artisan('installments:send-reminders')->assertSuccessful();
        Mail::assertSent(InstallmentReminderMail::class, 1);
    }
}
