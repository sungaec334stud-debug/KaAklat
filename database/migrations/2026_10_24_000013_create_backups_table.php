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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();

            // Original filename of the dump, e.g. kaAklat_2026-10-24_08-00-00.sql.gz
            $table->string('filename');

            // Absolute local path to the dump file on the server.
            $table->string('path');

            // File size recorded after successful creation; null if the backup failed.
            $table->unsignedBigInteger('size_bytes')->nullable();

            // Null = initiated by the scheduler rather than a manual admin action.
            $table->foreignId('triggered_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->enum('status', ['Success', 'Failed'])->default('Success');

            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
