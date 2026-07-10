<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'project_slug',
        'project_name',
        'preferred_visit_at',
        'party_size',
        'message',
        'status',
    ];

    protected $casts = [
        'preferred_visit_at' => 'datetime',
    ];

    public const STATUSES = ['new', 'confirmed', 'completed', 'cancelled'];
}
