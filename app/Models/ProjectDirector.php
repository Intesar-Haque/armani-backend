<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDirector extends Model
{
    protected $fillable = [
        'project_id',
        'client_id',
        'designation',
        'appointed_date',
        'notes',
    ];

    protected $hidden = ['notes'];

    protected $casts = [
        'appointed_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
