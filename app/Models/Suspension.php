<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suspension extends Model
{
    protected $fillable = [
        'user_id',
        'suspended_by',
        'reason',
        'suspended_at',
        'reinstated_at',
        'reinstated_by',
        'reinstatement_reason',
    ];

    protected function casts(): array
    {
        return [
            'suspended_at'  => 'datetime',
            'reinstated_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Returns true when this suspension is still active (not yet reinstated). */
    public function isActive(): bool
    {
        return $this->reinstated_at === null;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function reinstatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reinstated_by');
    }
}
