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
        // 1. settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 2. employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->string('position')->default('Staff');
            $table->string('department')->default('General');
            $table->string('bank_name')->nullable();
            $table->text('account_number')->nullable(); // encrypted
            $table->date('joined_date')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        // 3. work_schedules table
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days'); // ["monday", "tuesday", ...]
            $table->integer('tolerance_minutes')->default(15);
            $table->timestamps();
        });

        // 4. employee_schedules table
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('work_schedules')->cascadeOnDelete();
            $table->date('effective_date');
            $table->timestamps();
        });

        // 5. attendances table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_lng', 11, 8)->nullable();
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_lng', 11, 8)->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'sick', 'permission', 'wfh'])->default('present');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // 6. leave_requests table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['sick', 'permission', 'annual_leave', 'emergency_leave']);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->default(1);
            $table->text('reason');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });

        // 7. complaints table
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('date');
            $table->enum('type', ['wrong_time', 'location_error', 'forgot_checkout', 'system_error', 'other']);
            $table->text('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'in_review', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('settings');
    }
};
