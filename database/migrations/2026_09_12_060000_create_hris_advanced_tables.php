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
        // 1. announcements table
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['info', 'warning', 'success', 'primary'])->default('info');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. holidays table
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 4. payrolls table
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('month'); // YYYY-MM
            $table->decimal('basic_salary', 14, 2)->default(0);
            $table->decimal('daily_allowance', 14, 2)->default(0);
            $table->decimal('late_deduction', 14, 2)->default(0);
            $table->decimal('absent_deduction', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('announcements');
    }
};
