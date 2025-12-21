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
        // Add index on users.role for faster role-based queries
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        // Add composite indexes on appointment_slots for common queries
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->index(['advisor_id', 'status', 'start_time'], 'idx_advisor_status_start');
            $table->index(['status', 'start_time'], 'idx_status_start');
        });

        // Add indexes on appointments for common queries
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['student_id', 'status'], 'idx_student_status');
            $table->index(['slot_id', 'status'], 'idx_slot_status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->dropIndex('idx_advisor_status_start');
            $table->dropIndex('idx_status_start');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_student_status');
            $table->dropIndex('idx_slot_status');
            $table->dropIndex(['status']);
        });
    }
};
