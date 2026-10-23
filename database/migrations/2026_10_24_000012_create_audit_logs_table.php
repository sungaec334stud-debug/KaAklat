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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // The user who performed the action; null when triggered by the system/scheduler.
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Dot-namespaced action code, e.g. "user.deactivated", "loan.checked_out",
            // "setting.updated", "hold.cancelled".
            $table->string('action');

            // Polymorphic reference to the affected model.
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');

            // State snapshots for update events; null for create/delete events.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Audit log rows are append-only; there is intentionally no updated_at column.
            $table->timestamp('created_at')->useCurrent();

            // Indexes for admin log browsing and per-record history.
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
