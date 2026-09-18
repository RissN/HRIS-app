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
        Schema::create('employee_appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 30)->default('sosmed'); // sosmed, customer, service, extra_mile
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('evidence_url')->nullable();
            $table->integer('points')->default(100);
            $table->date('date');
            $table->timestamps();

            $table->index(['employee_id', 'date']);
            $table->index(['date', 'points']);
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_appreciations');
    }
};
