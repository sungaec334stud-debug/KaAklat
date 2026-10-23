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
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('book_id')
                  ->constrained('books')
                  ->restrictOnDelete();

            // Physical identifier stamped or labelled on the book.
            $table->string('accession_number')->unique();

            // Status lifecycle:
            //   Available  → can be loaned or reserved
            //   Reserved   → held for an approved borrow_request or a ready hold
            //   Borrowed   → currently on loan
            //   Damaged    → out of circulation for repair
            //   Lost       → permanently removed from available stock
            $table->enum('status', ['Available', 'Reserved', 'Borrowed', 'Damaged', 'Lost'])
                  ->default('Available');

            // Recorded by librarian during check-in.
            $table->text('condition_note')->nullable();

            $table->timestamps();

            // Soft delete: history preserved; hard delete blocked when active loan exists.
            $table->softDeletes();

            // Primary lookup: find available copies for a given title.
            $table->index(['book_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
