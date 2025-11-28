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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('slot_id')->constrained('appointment_slots')->onDelete('cascade');
            $table->string('token')->unique()->nullable();
            $table->text('purpose');
            // Pending is default, No Show added for auto-cancellation logic [cite: 42]
            $table->enum('status', ['pending', 'approved', 'declined', 'completed', 'no_show', 'cancelled'])->default('pending');
            $table->text('meeting_notes')->nullable(); // MOM
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
