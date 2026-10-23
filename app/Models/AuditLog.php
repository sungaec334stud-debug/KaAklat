<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit log.
 *
 * Rows in this table must never be updated or deleted.
 * There is no updated_at column by design.
 */
class AuditLog extends Model
{
    /**
     * Disable automatic updated_at management — audit rows are immutable.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** The user who performed the audited action (null = system). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The model instance that was affected. */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
