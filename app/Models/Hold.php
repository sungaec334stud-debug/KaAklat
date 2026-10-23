<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hold extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'book_copy_id',
        'queue_position',
        'status',
        'placed_at',
        'ready_at',
        'pickup_deadline',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'placed_at'       => 'datetime',
            'ready_at'        => 'datetime',
            'pickup_deadline' => 'datetime',
            'cancelled_at'    => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return in_array($this->status, ['Waiting', 'Ready']);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function emailDeliveryLogs(): HasMany
    {
        return $this->hasMany(EmailDeliveryLog::class);
    }
}
