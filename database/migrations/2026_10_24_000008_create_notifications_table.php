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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Type codes: due_soon | overdue | severely_overdue | hold_ready
            //             request_approved | request_rejected | request_expired
            //             suspended | reinstated | loan_returned
            $table->string('type');

            // Optional links to the associated record for deep-linking in the UI.
            $table->foreignId('loan_id')
                  ->nullable()
                  ->constrained('loans')
                  ->nullOnDelete();

            $table->foreignId('hold_id')
                  ->nullable()
                  ->constrained('holds')
                  ->nullOnDelete();

            $table->string('title');
            $table->text('body');

            // NULL = unread.
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Unread badge count query: user_id + read_at IS NULL.
            $table->index(['user_id', 'read_at']);

            // Deduplication: prevent sending the same type for the same loan on the same day.
            $table->index(['loan_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
