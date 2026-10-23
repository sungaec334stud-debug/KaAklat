<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BorrowRequest extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'book_copy_id',
        'status',
        'rejection_reason',
        'processed_by',
        'requested_at',
        'expires_at',
        'approved_at',
        'pickup_deadline',
    ];

    protected function casts(): array
    {
        return [
            'requested_at'    => 'datetime',
            'expires_at'      => 'datetime',
            'approved_at'     => 'datetime',
            'pickup_deadline' => 'datetime',
        ];
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

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** The loan that was created when this request was checked out. */
    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }
}
