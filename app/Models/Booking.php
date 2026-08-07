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
        'apartment_id',
        'client_id',
        'unit_number',
        'property_price',
        'total_amount',
        'booking_amount',
        'down_payment',
        'payment_type',
        'plan_months',
        'surcharge_rate',
        'surcharge_amount',
        'discount_amount',
        'booking_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'property_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'booking_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'surcharge_rate' => 'decimal:2',
        'surcharge_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'booking_date' => 'date',
    ];

    public const STATUSES = ['active', 'completed', 'cancelled'];

    public const PAYMENT_TYPE_INSTALLMENT = 'installment';
    public const PAYMENT_TYPE_FULL = 'full';

    /** Standard booking money required to reserve an apartment (BDT). */
    public const BOOKING_MONEY = 100000;

    /** Installment plans: months => additional surcharge % on the remaining amount. */
    public const PLANS = [
        12 => 0.0,
        24 => 5.0,
        36 => 10.0,
    ];

    /** Discount % when the full amount is paid without installments. */
    public const FULL_PAYMENT_DISCOUNT_PERCENT = 5.0;

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

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function planLabel(): string
    {
        if ($this->payment_type === self::PAYMENT_TYPE_FULL) {
            return 'Full payment (5% discount)';
        }

        $rate = rtrim(rtrim(number_format((float) $this->surcharge_rate, 2), '0'), '.');

        return "{$this->plan_months} months ({$rate}% additional)";
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
