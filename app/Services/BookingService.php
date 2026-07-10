<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Document;
use App\Models\Installment;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create booking for a client. Returns [Booking, ?string $generatedPassword].
     * $generatedPassword is non-null when a new client user was created.
     */
    public function createBooking(array $input): array
    {
        return DB::transaction(function () use ($input) {
            $generatedPassword = null;

            $client = User::where('email', $input['client_email'])->first();
            if (! $client) {
                $generatedPassword = Str::random(10);
                $client = User::create([
                    'name' => $input['client_name'],
                    'email' => $input['client_email'],
                    'phone' => $input['client_phone'] ?? null,
                    'password' => Hash::make($generatedPassword),
                    'role' => User::ROLE_CLIENT,
                    'must_change_password' => true,
                ]);
            } elseif ($client->isAdmin()) {
                throw new \RuntimeException('An admin user already exists with that email.');
            }

            $project = Project::findOrFail($input['project_id']);
            $bookingDate = Carbon::parse($input['booking_date']);

            $booking = Booking::create([
                'project_id' => $project->id,
                'client_id' => $client->id,
                'unit_number' => $input['unit_number'] ?? null,
                'total_amount' => $input['total_amount'],
                'booking_amount' => $input['booking_amount'],
                'booking_date' => $bookingDate->toDateString(),
                'status' => 'active',
                'notes' => $input['notes'] ?? null,
            ]);

            $this->generateInstallmentSchedule(
                $booking,
                (float) $input['total_amount'],
                (float) $input['booking_amount'],
                (int) $input['installment_count'],
                Carbon::parse($input['first_installment_date']),
                $bookingDate,
            );

            $this->createBookingReceipt($booking);

            return [$booking->fresh(['project', 'client', 'installments', 'documents']), $generatedPassword];
        });
    }

    private function generateInstallmentSchedule(
        Booking $booking,
        float $total,
        float $bookingAmount,
        int $count,
        Carbon $firstInstallmentDate,
        Carbon $bookingDate,
    ): void {
        $bookingInstallment = Installment::create([
            'booking_id' => $booking->id,
            'sequence' => 0,
            'label' => 'Booking amount',
            'amount_due' => $bookingAmount,
            'paid_amount' => $bookingAmount,
            'due_date' => $bookingDate->toDateString(),
            'paid_at' => $bookingDate,
            'payment_method' => 'received-at-booking',
        ]);

        $remaining = round($total - $bookingAmount, 2);
        if ($count <= 0 || $remaining <= 0) {
            return;
        }

        $perInstallment = round($remaining / $count, 2);
        $runningTotal = 0;

        for ($i = 1; $i <= $count; $i++) {
            $amount = ($i === $count)
                ? round($remaining - $runningTotal, 2)
                : $perInstallment;
            $runningTotal += $amount;

            Installment::create([
                'booking_id' => $booking->id,
                'sequence' => $i,
                'label' => "Installment {$i}",
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
}
