<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landowner extends Model
{
    protected $fillable = [
        'land_locality',
        'land_address',
        'land_size_katha',
        'road_width_ft',
        'land_facing',
        'attractive_feature',
        'name',
        'email',
        'phone',
        'status',
    ];

    public const STATUSES = ['new', 'contacted', 'closed'];
}
