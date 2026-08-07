<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'base_price',
        'status',
        'share_value',
        'total_shares',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'share_value' => 'decimal:2',
    ];

    public const STATUSES = ['active', 'sold_out', 'archived'];

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name) . '-' . Str::lower(Str::random(4));
            }
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class)->orderBy('floor_number')->orderBy('apartment_number');
    }

    public function shareAssignments(): HasMany
    {
        return $this->hasMany(ShareAssignment::class);
    }

    public function sharesAssigned(): int
    {
        return (int) $this->shareAssignments()->where('status', ShareAssignment::STATUS_ACTIVE)->sum('shares_count');
    }

    public function sharesRemaining(): ?int
    {
        if ($this->total_shares === null) {
            return null;
        }

        return $this->total_shares - $this->sharesAssigned();
    }
}
