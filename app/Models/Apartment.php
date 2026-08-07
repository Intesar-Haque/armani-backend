<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_SOLD = 'sold';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_BOOKED,
        self::STATUS_SOLD,
    ];

    protected $fillable = [
        'project_id',
        'apartment_number',
        'floor_label',
        'floor_number',
        'size_sqft',
        'facing',
        'per_sqft_rate',
        'apartment_price',
        'car_parking_cost',
        'bike_parking_cost',
        'utility_connection_cost',
        'gross_total',
        'status',
    ];

    protected $casts = [
        'per_sqft_rate' => 'decimal:2',
        'apartment_price' => 'decimal:2',
        'car_parking_cost' => 'decimal:2',
        'bike_parking_cost' => 'decimal:2',
        'utility_connection_cost' => 'decimal:2',
        'gross_total' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking(): ?Booking
    {
        return $this->bookings()->whereIn('status', ['active', 'completed'])->latest()->first();
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
}
