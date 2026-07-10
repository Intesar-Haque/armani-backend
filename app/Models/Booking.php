<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'project_id',
        'client_id',
        'unit_number',
        'total_amount',
        'booking_amount',
        'booking_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'booking_date' => 'date',
    ];

    public const STATUSES = ['active', 'completed', 'cancelled'];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->reference)) {
                $booking->reference = 'ARM-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('sequence');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    public function totalPaid(): float
    {
        return (float) $this->installments()->sum('paid_amount');
    }

    public function balance(): float
    {
        return (float) $this->total_amount - $this->totalPaid();
    }
}
