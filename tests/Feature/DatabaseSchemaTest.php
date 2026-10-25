<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertSafeTestDatabase();
    }

    protected function setUp(): void
    {
        $connection = getenv('DB_CONNECTION') ?: '';
        $database = strtolower(getenv('DB_DATABASE') ?: '');

        if ($connection !== 'mysql' || !str_ends_with($database, '_test')) {
            throw new RuntimeException(
                'Database schema tests require MySQL and a dedicated database whose name ends in _test.'
            );
        }

        parent::setUp();
    }

    public function test_total_and_available_copy_counts_follow_copy_rows_and_status(): void
    {
        $book = $this->createBook();
        $available = $this->createCopy($book, 'ACC-AVAILABLE');
        $this->createCopy($book, 'ACC-DAMAGED', 'Damaged');
        $this->createCopy($book, 'ACC-LOST', 'Lost');

        $this->assertSame(3, $book->fresh()->total_copies);
        $this->assertSame(3, $book->totalCopiesCount());
        $this->assertSame(1, $book->availableCopiesCount());

        $available->update(['status' => 'Damaged']);
        $this->assertSame(3, $book->fresh()->total_copies);
        $this->assertSame(0, $book->availableCopiesCount());

        $available->delete();
        $this->assertSame(2, $book->fresh()->total_copies);

        $available->restore();
        $this->assertSame(3, $book->fresh()->total_copies);

        $newBook = $this->createBook();
        $available->update(['book_id' => $newBook->id]);

        $this->assertSame(2, $book->fresh()->total_copies);
        $this->assertSame(1, $newBook->fresh()->total_copies);
    }

    public function test_a_borrow_request_cannot_be_linked_to_more_than_one_loan(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook();
        $firstCopy = $this->createCopy($book, 'ACC-LOAN-1', 'Borrowed');
        $secondCopy = $this->createCopy($book, 'ACC-LOAN-2', 'Borrowed');
        $request = BorrowRequest::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'CheckedOut',
            'requested_at' => now(),
        ]);

        $loanAttributes = [
            'user_id' => $user->id,
            'borrow_request_id' => $request->id,
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
            'due_date' => today()->addDays(7),
            'returned_at' => now(),
            'condition_on_return' => 'Good',
            'status' => 'Returned',
        ];

        Loan::create($loanAttributes + ['book_copy_id' => $firstCopy->id]);

        $this->expectException(QueryException::class);
        Loan::create($loanAttributes + ['book_copy_id' => $secondCopy->id]);
    }

    public function test_automated_loan_notifications_are_unique_per_type_and_local_day(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook();
        $copy = $this->createCopy($book, 'ACC-NOTICE', 'Borrowed');
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_copy_id' => $copy->id,
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
            'due_date' => today()->addDays(7),
            'status' => 'Active',
        ]);

        $notice = Notification::create([
            'user_id' => $user->id,
            'loan_id' => $loan->id,
            'type' => 'due_soon',
            'title' => 'Due soon',
            'body' => 'Reminder',
            'is_automated' => true,
        ]);

        $this->assertSame(now(config('app.timezone'))->toDateString(), $notice->notification_day->toDateString());

        $this->expectException(QueryException::class);
        Notification::create([
            'user_id' => $user->id,
            'loan_id' => $loan->id,
            'type' => 'due_soon',
            'title' => 'Due soon again',
            'body' => 'Duplicate reminder',
            'is_automated' => true,
        ]);
    }

    public function test_manual_loan_notices_can_be_resent_on_the_same_day(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook();
        $copy = $this->createCopy($book, 'ACC-MANUAL-NOTICE', 'Borrowed');
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_copy_id' => $copy->id,
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
            'due_date' => today()->addDays(7),
            'status' => 'Active',
        ]);

        foreach (['First staff notice', 'Resent after warning'] as $body) {
            Notification::create([
                'user_id' => $user->id,
                'loan_id' => $loan->id,
                'type' => 'overdue',
                'title' => 'Overdue notice',
                'body' => $body,
            ]);
        }

        $this->assertSame(2, Notification::where('loan_id', $loan->id)->count());
    }

    public function test_a_loan_with_notifications_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $book = $this->createBook();
        $copy = $this->createCopy($book, 'ACC-HISTORY', 'Borrowed');
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_copy_id' => $copy->id,
            'checked_out_by' => $user->id,
            'checked_out_at' => now(),
            'due_date' => today()->addDays(7),
            'status' => 'Active',
        ]);
        Notification::create([
            'user_id' => $user->id,
            'loan_id' => $loan->id,
            'type' => 'due_soon',
            'title' => 'Due soon',
            'body' => 'Reminder',
            'is_automated' => true,
        ]);

        $this->expectException(QueryException::class);
        $loan->delete();
    }

    public function test_multiple_non_loan_notifications_of_the_same_type_are_allowed(): void
    {
        $user = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'suspended',
            'title' => 'Suspended',
            'body' => 'First message',
        ]);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'suspended',
            'title' => 'Suspended',
            'body' => 'Second message',
        ]);

        $this->assertSame(2, Notification::where('user_id', $user->id)->count());
    }

    public function test_due_today_is_not_classified_as_overdue(): void
    {
        Carbon::setTestNow('2026-10-24 09:00:00');

        try {
            $dueToday = new Loan(['status' => 'Active', 'due_date' => '2026-10-24']);
            $overdue = new Loan(['status' => 'Active', 'due_date' => '2026-10-23']);
            $returned = new Loan(['status' => 'Returned', 'due_date' => '2026-10-23']);

            $this->assertFalse($dueToday->isOverdue());
            $this->assertTrue($overdue->isOverdue());
            $this->assertFalse($returned->isOverdue());
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createBook(): Book
    {
        return Book::create([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'call_number' => 'TEST-' . fake()->unique()->numerify('####'),
        ]);
    }

    private function createCopy(Book $book, string $accession, string $status = 'Available'): BookCopy
    {
        return BookCopy::create([
            'book_id' => $book->id,
            'accession_number' => $accession,
            'status' => $status,
        ]);
    }
}
