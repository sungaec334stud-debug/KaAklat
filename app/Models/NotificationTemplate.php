<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'type',
        'subject',
        'body_template',
        'updated_by',
    ];

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Render the body template with the given replacement tokens.
     *
     * @param  array<string, string>  $tokens  e.g. ['patron_name' => 'Juan', 'book_title' => 'Noli Me Tangere']
     */
    public function render(array $tokens): string
    {
        $body = $this->body_template;

        foreach ($tokens as $placeholder => $value) {
            $body = str_replace('{{' . $placeholder . '}}', $value, $body);
        }

        return $body;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
