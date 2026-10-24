<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Custom in-app notification model.
 *
 * This is distinct from Laravel's built-in database notification channel.
 * Do not import Illuminate\Notifications\Notification in the same file.
 * Load a user's in-app notices via User::appNotifications(), never via
 * `$user->notifications` (Laravel's Notifiable database-notification relation).
 */
class Notification extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            if (!$notification->notification_day) {
                $notification->notification_day = now(config('app.timezone'))->toDateString();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'type',
        'loan_id',
        'hold_id',
        'title',
        'body',
        'read_at',
        'is_automated',
    ];

    protected function casts(): array
    {
        return [
            'read_at'          => 'datetime',
            'is_automated'     => 'boolean',
            'notification_day' => 'date',
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
