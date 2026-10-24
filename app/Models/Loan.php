<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'user_id',
        'book_copy_id',
        'borrow_request_id',
        'checked_out_by',
        'checked_in_by',
        'checked_out_at',
        'due_date',
        'returned_at',
        'condition_on_return',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'due_date'       => 'date',
            'returned_at'    => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isOverdue(): bool
    {
        return $this->status === 'Active'
            && $this->due_date->lt(today(config('app.timezone')));
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class);
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
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
