<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Machine-readable key, e.g. "loan_duration_days".
            $table->string('key')->unique();

            // All values stored as strings; casted to correct type in application code.
            $table->string('value');

            // Human-readable label shown in the admin settings UI.
            $table->string('description')->nullable();

            // Last admin who changed this setting (recorded in audit_log separately).
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });

        // Seed default business-rule values.
        $now = now();
        DB::table('settings')->insert([
            [
                'key'         => 'loan_duration_days',
                'value'       => '7',
                'description' => 'Number of days a borrowed book may be kept before it is due.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'max_active_loans',
                'value'       => '3',
                'description' => 'Maximum number of books a patron may have on loan at one time.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'request_expiry_hours',
                'value'       => '48',
                'description' => 'Hours after which a Pending borrow request automatically expires.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'hold_pickup_hours',
                'value'       => '24',
                'description' => 'Hours a patron has to collect a book after their hold becomes Ready.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'due_soon_days',
                'value'       => '2',
                'description' => 'Days before due date at which a loan is classified as Due Soon.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'severely_overdue_days',
                'value'       => '7',
                'description' => 'Days past due date at which a loan is classified as Severely Overdue.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'severe_overdue_repeat_days',
                'value'       => '3',
                'description' => 'Days between repeated Severely Overdue reminder notifications.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'daily_reminder_time',
                'value'       => '08:00',
                'description' => 'Time of day (HH:MM, 24-hour) at which the automated daily loan reminder runs.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
