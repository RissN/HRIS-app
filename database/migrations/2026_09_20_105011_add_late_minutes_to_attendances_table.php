<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedInteger('late_minutes')->default(0)->after('status');
        });

        // Sync existing late minutes from employee_daily_scores if available
        if (Schema::hasTable('employee_daily_scores')) {
            DB::statement("
                UPDATE attendances a
                INNER JOIN employee_daily_scores eds
                    ON a.employee_id = eds.employee_id
                    AND a.date = eds.date
                SET a.late_minutes = eds.late_minutes
                WHERE a.status = 'late' AND eds.late_minutes > 0
            ");
        }

        // For any remaining attendances with status 'late' that have 0 or null, set default 15 minutes
        DB::table('attendances')
            ->where('status', 'late')
            ->where('late_minutes', 0)
            ->update(['late_minutes' => 15]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('late_minutes');
        });
    }
};
