<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadNote extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'note',
    ];

    /**
     * Get the lead that owns the note.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user that wrote the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
