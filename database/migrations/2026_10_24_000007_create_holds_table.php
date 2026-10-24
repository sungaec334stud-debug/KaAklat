<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // A hold is placed on a title, not a specific copy.
            // A copy is assigned only when the hold becomes Ready.
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->restrictOnDelete();

            // Assigned when the hold becomes Ready. Restrict delete so a reserved
            // copy cannot disappear while this hold still points at it.
            $table->foreignId('book_copy_id')
                  ->nullable()
                  ->constrained('book_copies')
                  ->restrictOnDelete();

            // FIFO position within the queue for this title.
            // Assigned at insert: MAX(queue_position) + 1 for the book, inside a transaction.
            $table->unsignedSmallInteger('queue_position');

            // Status lifecycle:
            //   Waiting    → in the FIFO queue; no copy assigned yet
            //   Ready      → copy assigned; patron has until pickup_deadline to collect
            //   Checked Out → patron checked out the held copy; hold is fulfilled
            //   Cancelled  → patron cancelled the hold
            //   Expired    → pickup_deadline passed without collection
            $table->enum('status', ['Waiting', 'Ready', 'Checked Out', 'Cancelled', 'Expired'])
                  ->default('Waiting');

            // Anchor timestamp for FIFO ordering. Established when the hold is placed.
            $table->timestamp('placed_at')->useCurrent();

            // Set when a copy is offered to this hold.
            $table->timestamp('ready_at')->nullable();

            // ready_at + hold_pickup_hours (from settings).
            $table->timestamp('pickup_deadline')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Primary FIFO advancement index: find the next Waiting hold for a title.
            $table->index(['book_id', 'status', 'queue_position']);

            // Patron hold activity view.
            $table->index(['user_id', 'status']);

            // Scheduler: find holds whose pickup deadline has lapsed.
            $table->index('pickup_deadline');

            // MySQL has no partial unique indexes. Nullable stored generated
            // columns enforce uniqueness only for active rows; inactive history
            // stores NULL and does not block new holds.
            $table->unsignedBigInteger('active_user_id')
                  ->nullable()
                  ->storedAs("CASE WHEN `status` IN ('Waiting', 'Ready') THEN `user_id` ELSE NULL END");
            $table->unsignedBigInteger('active_book_id')
                  ->nullable()
                  ->storedAs("CASE WHEN `status` IN ('Waiting', 'Ready') THEN `book_id` ELSE NULL END");
            $table->unique(['active_user_id', 'active_book_id'], 'holds_one_active_per_user_book');

            $table->unsignedBigInteger('waiting_book_id')
                  ->nullable()
                  ->storedAs("CASE WHEN `status` = 'Waiting' THEN `book_id` ELSE NULL END");
            $table->unsignedSmallInteger('waiting_queue_position')
                  ->nullable()
                  ->storedAs("CASE WHEN `status` = 'Waiting' THEN `queue_position` ELSE NULL END");
            $table->unique(['waiting_book_id', 'waiting_queue_position'], 'holds_waiting_fifo_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
