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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('book_copy_id')
                  ->constrained('book_copies')
                  ->restrictOnDelete();

            // Originating request, if the loan came from a borrow_request workflow.
            $table->foreignId('borrow_request_id')
                  ->nullable()
                  ->constrained('borrow_requests')
                  ->nullOnDelete();

            // A checked-out request creates at most one loan. Multiple NULLs
            // remain valid for staff-created loans without a borrow request.
            $table->unique('borrow_request_id', 'loans_one_per_borrow_request');

            $table->foreignId('checked_out_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('checked_in_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('checked_out_at');

            // due_date = checked_out_at + loan_duration_days (from settings).
            // Stored as DATE so overdue classification is straightforward.
            $table->date('due_date');

            // NULL means the copy is still on loan.
            $table->timestamp('returned_at')->nullable();

            // Recorded by librarian at check-in; informs copy status update.
            $table->enum('condition_on_return', ['Good', 'Damaged', 'Lost'])->nullable();

            $table->enum('status', ['Active', 'Returned', 'Lost'])->default('Active');

            $table->timestamps();

            // Indexes used by the overdue scheduler and patron activity views.
            $table->index(['user_id', 'status']);
            $table->index(['book_copy_id', 'status']);
            $table->index('due_date');
            $table->index('returned_at');

            // One Active loan per copy. Returned and Lost rows store NULL here
            // so historical loans do not block a later Active loan on the copy.
            $table->unsignedBigInteger('active_copy_id')
                  ->nullable()
                  ->storedAs("CASE WHEN `status` = 'Active' THEN `book_copy_id` ELSE NULL END");
            $table->unique('active_copy_id', 'loans_one_active_per_copy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
