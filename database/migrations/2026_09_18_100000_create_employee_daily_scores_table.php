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
        Schema::create('employee_daily_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->string('attendance_status', 20)->default('present'); // present, late, leave, absent
            $table->time('check_in_time')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('attendance_score')->default(100);
            $table->integer('appreciation_score')->default(0);
            $table->integer('total_score')->default(100);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'total_score']);
            $table->index(['employee_id', 'date', 'total_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_daily_scores');
    }
};
