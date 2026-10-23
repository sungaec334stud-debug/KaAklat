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
        Schema::create('suspensions', function (Blueprint $table) {
            $table->id();

            // The patron whose borrowing privileges are suspended.
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            // The librarian who issued the suspension.
            $table->foreignId('suspended_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->text('reason');
            $table->timestamp('suspended_at');

            // NULL means the suspension is still active.
            $table->timestamp('reinstated_at')->nullable();

            $table->foreignId('reinstated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('reinstatement_reason')->nullable();

            $table->timestamps();

            // Primary lookup: find any active suspension for a patron (reinstated_at IS NULL).
            $table->index(['user_id', 'reinstated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensions');
    }
};
