<?php

namespace App\Console\Commands;

use App\Mail\InstallmentReminderMail;
use App\Models\Installment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInstallmentReminders extends Command
{
    protected $signature = 'installments:send-reminders';

    protected $description = 'Email clients about unpaid installments due soon (7, 3 and 1 days before the due date)';

    /** Days before the due date on which a reminder email is sent. */
    public const REMINDER_DAYS = [7, 3, 1];

    public function handle(): int
    {
        $dueDates = collect(self::REMINDER_DAYS)
            ->map(fn (int $days) => today()->addDays($days)->toDateString());

        $installments = Installment::query()
            ->whereNull('paid_at')
            ->where(function ($q) use ($dueDates) {
                foreach ($dueDates as $date) {
                    $q->orWhereDate('due_date', $date);
                }
            })
            ->where(function ($q) {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', today());
            })
            ->whereHas('booking', fn ($q) => $q->where('status', 'active'))
            ->with('booking.client', 'booking.project')
            ->get();

        foreach ($installments as $installment) {
            $client = $installment->booking->client;
            if (! $client?->email) {
                continue;
            }

            Mail::to($client->email)->send(new InstallmentReminderMail($installment));
            $installment->update(['last_reminder_sent_at' => today()]);

            $this->info("Reminder sent to {$client->email} for {$installment->label} (due {$installment->due_date->toDateString()}).");
        }

        $this->info("Done. {$installments->count()} reminder(s) processed.");

        return self::SUCCESS;
    }
}
