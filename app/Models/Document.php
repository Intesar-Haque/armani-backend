<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_DEED = 'deed';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_RECEIPT,
        self::TYPE_DEED,
        self::TYPE_OTHER,
    ];

    protected $fillable = [
        'booking_id',
        'installment_id',
        'type',
        'title',
        'file_path',
        'original_filename',
        'amount',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }
}
