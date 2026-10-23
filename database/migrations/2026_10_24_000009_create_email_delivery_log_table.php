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
        Schema::create('email_delivery_log', function (Blueprint $table) {
            $table->id();

            // Nullable: some transactional emails may target non-user addresses.
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('to_address');

            // Mirrors notification type codes.
            $table->string('type');

            $table->foreignId('loan_id')
                  ->nullable()
                  ->constrained('loans')
                  ->nullOnDelete();

            $table->foreignId('hold_id')
                  ->nullable()
                  ->constrained('holds')
                  ->nullOnDelete();

            $table->enum('status', ['Sent', 'Failed'])->default('Sent');

            // Mailer error detail when status = Failed.
            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->useCurrent();

            $table->timestamps();

            // Used by the "warn before resending within 24 h" check.
            $table->index(['loan_id', 'type', 'sent_at']);
            $table->index(['user_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_delivery_log');
    }
};
