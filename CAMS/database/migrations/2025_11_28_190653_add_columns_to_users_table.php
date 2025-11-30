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
            // Adding columns to default Laravel users table
            $table->enum('role', ['student', 'advisor', 'admin'])->default('student')->after('email');
            $table->string('university_id')->nullable()->unique()->after('role'); // Student ID
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('university_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['role', 'university_id', 'department_id']);
        });
    }
};
