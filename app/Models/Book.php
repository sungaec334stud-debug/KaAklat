<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'category',
        'isbn',
        'call_number',
        'publication_year',
        'description',
        'cover_image_path',
        'total_copies',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'total_copies'     => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }
}
