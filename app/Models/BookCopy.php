<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BookCopy extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::created(fn (self $copy) => $copy->syncBookTotalCopies((int) $copy->book_id));
        static::deleted(fn (self $copy) => $copy->syncBookTotalCopies((int) $copy->book_id));
        static::restored(fn (self $copy) => $copy->syncBookTotalCopies((int) $copy->book_id));

        static::updated(function (self $copy): void {
            if ($copy->wasChanged('book_id')) {
                $oldBookId = (int) $copy->getOriginal('book_id');
                $newBookId = (int) $copy->book_id;

                $copy->syncBookTotalCopies($oldBookId);
                $copy->syncBookTotalCopies($newBookId);
            }
        });
    }

    protected $fillable = [
        'book_id',
        'accession_number',
        'status',
        'condition_note',
    ];

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAvailable(): bool
    {
        return $this->status === 'Available';
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /** The current active loan for this copy, if any. */
    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->where('status', 'Active');
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    private function syncBookTotalCopies(int $bookId): void
    {
        DB::transaction(function () use ($bookId): void {
            $book = Book::withTrashed()->lockForUpdate()->find($bookId);

            if ($book !== null) {
                $book->forceFill(['total_copies' => $book->copies()->count()])->saveQuietly();
            }
        });
    }
}
