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
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
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
}
