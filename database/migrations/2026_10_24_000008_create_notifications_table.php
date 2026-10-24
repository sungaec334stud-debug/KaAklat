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
                  // loan_id is the base of automated_loan_id, a stored generated
                  // column. MySQL does not allow SET NULL on such a FK column.
                  // Restrict deletion to preserve notice history and FK integrity.
                  ->restrictOnDelete();

            $table->foreignId('hold_id')
                  ->nullable()
                  ->constrained('holds')
                  ->nullOnDelete();

            $table->string('title');
            $table->text('body');

            // NULL = unread.
            $table->timestamp('read_at')->nullable();

            // Manual notices may be resent after the UI warns the staff member.
            $table->boolean('is_automated')->default(false);

            // Application-local calendar day used for deterministic reminder
            // deduplication; populated by the Notification model on creation.
            $table->date('notification_day');

            // MySQL lacks partial unique indexes. These generated values are
            // populated only for automated loan notices, allowing manual
            // resends and non-loan notices while preventing daily duplicates.
            $table->unsignedBigInteger('automated_loan_id')
                  ->nullable()
                  ->storedAs("CASE WHEN `is_automated` = 1 AND `loan_id` IS NOT NULL THEN `loan_id` ELSE NULL END");
            $table->string('automated_type')
                  ->nullable()
                  ->storedAs("CASE WHEN `is_automated` = 1 AND `loan_id` IS NOT NULL THEN `type` ELSE NULL END");
            $table->date('automated_notification_day')
                  ->nullable()
                  ->storedAs("CASE WHEN `is_automated` = 1 AND `loan_id` IS NOT NULL THEN `notification_day` ELSE NULL END");

            $table->timestamps();

            // Unread badge count query: user_id + read_at IS NULL.
            $table->index(['user_id', 'read_at']);

            // Lookup index for loan history and the 24-hour resend warning.
            $table->index(['loan_id', 'type', 'created_at']);
            $table->unique(
                ['automated_loan_id', 'automated_type', 'automated_notification_day'],
                'notifications_one_automated_loan_type_per_day',
            );
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
