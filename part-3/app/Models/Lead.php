<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'title',
        'assigned_user_id',
        'client_id',
    ];

    /**
     * Get the user that is assigned to the lead.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the notes for the lead.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }
}
