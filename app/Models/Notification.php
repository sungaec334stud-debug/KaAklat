<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Custom in-app notification model.
 *
 * This is distinct from Laravel's built-in database notification channel.
 * Do not import Illuminate\Notifications\Notification in the same file.
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'loan_id',
        'hold_id',
        'title',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }
}
