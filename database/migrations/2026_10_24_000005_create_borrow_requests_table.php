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
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Patron requests a title; a specific copy is assigned only on approval.
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->restrictOnDelete();

            $table->foreignId('book_copy_id')
                  ->nullable()
                  ->constrained('book_copies')
                  ->nullOnDelete();

            // Status lifecycle:
            //   Pending    → awaiting librarian action; copy not yet reserved
            //   Approved   → copy reserved; patron has until pickup_deadline to collect
            //   Rejected   → librarian rejected (rejection_reason required)
            //   Cancelled  → patron cancelled before approval
            //   Expired    → request not acted on within expires_at window (Pending)
            //              → or not picked up within pickup_deadline window (Approved)
            //   CheckedOut → copy has been handed to the patron; loan record created
            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Cancelled',
                'Expired',
                'CheckedOut',
            ])->default('Pending');

            $table->text('rejection_reason')->nullable();

            // Librarian who acted on the request.
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Canonical timestamp for FIFO display ordering (requests in received order).
            $table->timestamp('requested_at')->useCurrent();

            // Pending requests expire after the configured window (default 48 h).
            $table->timestamp('expires_at')->nullable();

            // Set when status transitions to Approved.
            $table->timestamp('approved_at')->nullable();

            // Deadline for patron to collect an approved request.
            $table->timestamp('pickup_deadline')->nullable();

            $table->timestamps();

            // Lookup indexes for patron activity, per-title status, and scheduler jobs.
            $table->index(['user_id', 'status']);
            $table->index(['book_id', 'status']);
            $table->index('expires_at');
            $table->index('requested_at');
            $table->index('pickup_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};
