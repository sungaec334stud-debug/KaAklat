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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');

            // Category / genre stored as free text on the book record.
            $table->string('category')->nullable();

            // ISBN-10 or ISBN-13; nullable for older titles without one.
            $table->string('isbn', 20)->nullable()->unique();

            // Library classification call number (e.g. Dewey Decimal or LC).
            $table->string('call_number')->unique();

            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->text('description')->nullable();

            // Path to a locally stored cover image; no external URLs.
            $table->string('cover_image_path')->nullable();

            // Stored for display. Updated by staff when copies are added/removed.
            // Available copies are DERIVED: total_copies minus count of Reserved/Borrowed copies.
            $table->unsignedSmallInteger('total_copies')->default(0);

            $table->timestamps();

            // Soft delete: prevents deletion when active loans or holds exist.
            $table->softDeletes();
        });

        // Full-text index for catalog search across title and author.
        Schema::table('books', function (Blueprint $table) {
            $table->fullText(['title', 'author']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
