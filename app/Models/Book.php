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

    /** Physical copies currently available to circulate. */
    public function availableCopies(): HasMany
    {
        return $this->copies()->where('status', 'Available');
    }

    public function availableCopiesCount(): int
    {
        return $this->availableCopies()->count();
    }

    public function totalCopiesCount(): int
    {
        return $this->copies()->count();
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
