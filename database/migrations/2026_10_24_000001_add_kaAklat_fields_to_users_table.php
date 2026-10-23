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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'librarian', 'admin'])
                  ->default('student')
                  ->after('remember_token');

            // Unique student identifier; format validated at the application layer.
            // Null for librarian and admin accounts.
            $table->string('student_id')->nullable()->unique()->after('role');

            $table->boolean('is_active')->default(true)->after('student_id');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');

            // Self-referential FK: the admin who deactivated this account.
            $table->foreignId('deactivated_by')
                  ->nullable()
                  ->after('deactivated_at')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->softDeletes();
        });

        // Index role for role-guard and report queries.
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'student_id',
                'is_active',
                'deactivated_at',
                'deactivated_by',
                'deleted_at',
            ]);
        });
    }
};
