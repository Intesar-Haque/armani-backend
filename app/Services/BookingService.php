<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Document;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private ClientAccountService $clients) {}

    /**
     * Book an apartment for a client. Returns [Booking, ?string $generatedPassword].
     * $generatedPassword is non-null when a new client user was created.
     *
     * Expected input:
     *  - apartment_id, booking_date, first_installment_date
     *  - client_id OR client_name/client_email/client_phone
     *  - booking_amount (booking money, standard 100,000 BDT)
     *  - down_payment
     *  - payment_plan: 'full' | '12' | '24' | '36'
     */
    public function createBooking(array $input): array
    {
        return DB::transaction(function () use ($input) {
            [$client, $generatedPassword] = $this->clients->findOrCreate($input);

            $apartment = Apartment::lockForUpdate()->findOrFail($input['apartment_id']);
            if (! $apartment->isAvailable()) {
                throw ValidationException::withMessages([
                    'apartment_id' => "Apartment {$apartment->apartment_number} is no longer available.",
                ]);
            }

            $bookingDate = Carbon::parse($input['booking_date']);
            $bookingAmount = round((float) $input['booking_amount'], 2);
            $downPayment = round((float) ($input['down_payment'] ?? 0), 2);
            $plan = (string) $input['payment_plan'];
            $gross = (float) $apartment->gross_total;
            $upfront = $bookingAmount + $downPayment;

            if ($plan === Booking::PAYMENT_TYPE_FULL) {
                $discount = round($gross * Booking::FULL_PAYMENT_DISCOUNT_PERCENT / 100, 2);
                $surchargeRate = 0.0;
                $surcharge = 0.0;
                $planMonths = null;
                $payable = round($gross - $discount, 2);
            } else {
                $planMonths = (int) $plan;
                if (! array_key_exists($planMonths, Booking::PLANS)) {
                    throw ValidationException::withMessages(['payment_plan' => 'Invalid installment plan.']);
                }
                $discount = 0.0;
                $surchargeRate = Booking::PLANS[$planMonths];
                $surcharge = round(max(0, $gross - $upfront) * $surchargeRate / 100, 2);
                $payable = round($gross + $surcharge, 2);
            }

            if ($upfront > $payable) {
                throw ValidationException::withMessages([
                    'down_payment' => 'Booking money plus down payment exceeds the total payable amount.',
                ]);
            }

            $booking = Booking::create([
                'project_id' => $apartment->project_id,
                'apartment_id' => $apartment->id,
                'client_id' => $client->id,
                'unit_number' => $apartment->apartment_number,
                'property_price' => $gross,
                'total_amount' => $payable,
                'booking_amount' => $bookingAmount,
                'down_payment' => $downPayment,
                'payment_type' => $plan === Booking::PAYMENT_TYPE_FULL
                    ? Booking::PAYMENT_TYPE_FULL
                    : Booking::PAYMENT_TYPE_INSTALLMENT,
                'plan_months' => $planMonths,
                'surcharge_rate' => $surchargeRate,
                'surcharge_amount' => $surcharge,
                'discount_amount' => $discount,
                'booking_date' => $bookingDate->toDateString(),
                'status' => 'active',
                'notes' => $input['notes'] ?? null,
            ]);

            $apartment->update(['status' => Apartment::STATUS_BOOKED]);

            $this->generateInstallmentSchedule(
                $booking,
                Carbon::parse($input['first_installment_date'] ?? $bookingDate->copy()->addMonth()),
                $bookingDate,
            );

            $this->createBookingReceipt($booking);

            return [$booking->fresh(['project', 'apartment', 'client', 'installments', 'documents']), $generatedPassword];
        });
    }

    private function generateInstallmentSchedule(
        Booking $booking,
        Carbon $firstInstallmentDate,
        Carbon $bookingDate,
    ): void {
        $sequence = 0;

        Installment::create([
            'booking_id' => $booking->id,
            'sequence' => $sequence++,
            'label' => 'Booking money',
            'amount_due' => $booking->booking_amount,
            'paid_amount' => $booking->booking_amount,
            'due_date' => $bookingDate->toDateString(),
            'paid_at' => $bookingDate,
            'payment_method' => 'received-at-booking',
        ]);

        if ((float) $booking->down_payment > 0) {
            Installment::create([
                'booking_id' => $booking->id,
                'sequence' => $sequence++,
                'label' => 'Down payment',
                'amount_due' => $booking->down_payment,
                'paid_amount' => $booking->down_payment,
                'due_date' => $bookingDate->toDateString(),
                'paid_at' => $bookingDate,
                'payment_method' => 'received-at-booking',
            ]);
        }

        $remaining = round((float) $booking->total_amount - (float) $booking->booking_amount - (float) $booking->down_payment, 2);
        if ($remaining <= 0) {
            return;
        }

        if ($booking->payment_type === Booking::PAYMENT_TYPE_FULL) {
            Installment::create([
                'booking_id' => $booking->id,
                'sequence' => $sequence,
                'label' => 'Full payment (5% discount applied)',
                'amount_due' => $remaining,
                'paid_amount' => 0,
                'due_date' => $firstInstallmentDate->toDateString(),
            ]);

            return;
        }

        $count = (int) $booking->plan_months;
        $perInstallment = round($remaining / $count, 2);
        $runningTotal = 0.0;

        for ($i = 1; $i <= $count; $i++) {
            $amount = ($i === $count)
                ? round($remaining - $runningTotal, 2)
                : $perInstallment;
            $runningTotal += $amount;

            Installment::create([
                'booking_id' => $booking->id,
                'sequence' => $sequence + $i - 1,
                'label' => "Installment {$i} of {$count}",
                'amount_due' => $amount,
                'paid_amount' => 0,
                'due_date' => $firstInstallmentDate->copy()->addMonthsNoOverflow($i - 1)->toDateString(),
            ]);
        }
    }

    private function createBookingReceipt(Booking $booking): void
    {
        $bookingInstallment = $booking->installments()->where('sequence', 0)->first();
        Document::create([
            'booking_id' => $booking->id,
            'installment_id' => $bookingInstallment?->id,
            'type' => Document::TYPE_RECEIPT,
            'title' => 'Booking Receipt · ' . $booking->reference,
            'amount' => $booking->booking_amount,
            'issued_at' => $booking->booking_date,
            'notes' => 'Auto-generated booking receipt',
        ]);
    }

    public function markInstallmentPaid(Installment $installment, array $input): Document
    {
        return DB::transaction(function () use ($installment, $input) {
            $amount = (float) $input['paid_amount'];
            $installment->update([
                'paid_amount' => $amount,
                'paid_at' => $input['paid_at'] ?? now(),
                'payment_method' => $input['payment_method'] ?? null,
                'payment_reference' => $input['payment_reference'] ?? null,
            ]);

            $booking = $installment->booking()->first();
            if ($booking && $booking->status === 'active' && $booking->balance() <= 0) {
                $booking->update(['status' => 'completed']);
                $booking->apartment?->update(['status' => Apartment::STATUS_SOLD]);
            }

            return Document::create([
                'booking_id' => $installment->booking_id,
                'installment_id' => $installment->id,
                'type' => Document::TYPE_RECEIPT,
                'title' => 'Receipt · ' . $installment->label,
                'amount' => $amount,
                'issued_at' => Carbon::parse($input['paid_at'] ?? now())->toDateString(),
                'notes' => $input['notes'] ?? null,
            ]);
        });
    }

    /**
     * Cancel a booking and release its apartment back to the market.
     */
    public function cancelBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);
            $booking->apartment?->update(['status' => Apartment::STATUS_AVAILABLE]);
        });
    }
}
