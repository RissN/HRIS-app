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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('region')->default('jakarta_pusat')->after('department')->index();
            $table->string('employment_status')->default('tetap')->after('region')->index();
            $table->string('pool_depot')->nullable()->after('employment_status');

            $table->index(['region', 'employment_status'], 'emp_region_status_idx');
            $table->index(['region', 'position'], 'emp_region_position_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('emp_region_status_idx');
            $table->dropIndex('emp_region_position_idx');
            $table->dropColumn(['region', 'employment_status', 'pool_depot']);
        });
    }
};
