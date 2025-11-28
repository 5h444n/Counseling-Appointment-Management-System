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
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_recurring')->default(false);
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};
