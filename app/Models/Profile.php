<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'course_or_position',
        'year_level',
        'contact_number',
        'avatar_path',
        'bio',
    ];

    /**
     * Defense in depth: contact_number is private (never shown publicly)
     * per CLAUDE.md. Direct property/Blade access is unaffected.
     */
    protected $hidden = ['contact_number'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
