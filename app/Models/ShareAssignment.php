<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ShareAssignment extends Model
{
    protected $fillable = [
        'reference',
        'project_id',
        'client_id',
        'shares_count',
        'share_value',
        'total_amount',
        'assigned_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'share_value' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'assigned_date' => 'date',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_CANCELLED];

    protected static function booted(): void
    {
        static::creating(function (ShareAssignment $assignment) {
            if (empty($assignment->reference)) {
                $assignment->reference = 'ARM-SHR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
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
}
