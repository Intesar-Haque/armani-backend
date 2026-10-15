<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    protected $fillable = [
        'booking_id',
        'sequence',
        'label',
        'amount_due',
        'paid_amount',
        'due_date',
        'paid_at',
        'last_reminder_sent_at',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $hidden = ['notes', 'last_reminder_sent_at'];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'last_reminder_sent_at' => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Document::class)->where('type', Document::TYPE_RECEIPT);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null && $this->paid_amount >= $this->amount_due;
    }

    public function isOverdue(): bool
    {
        return ! $this->isPaid() && $this->due_date instanceof CarbonInterface && $this->due_date->isPast();
    }

    public function isUpcoming(): bool
    {
        return ! $this->isPaid() && $this->due_date instanceof CarbonInterface && $this->due_date->isFuture();
    }

    public function statusLabel(): string
    {
        if ($this->isPaid()) {
            return 'paid';
        }
        if ($this->isOverdue()) {
            return 'overdue';
        }
        return 'upcoming';
    }
}
