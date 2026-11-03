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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('break_duration_minutes')->nullable()->after('late_minutes');
            $table->unsignedSmallInteger('break_limit_minutes')->nullable()->after('break_duration_minutes');
            $table->unsignedSmallInteger('break_excess_minutes')->default(0)->after('break_limit_minutes');
            $table->string('break_compliance', 20)->nullable()->after('break_excess_minutes')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['break_duration_minutes', 'break_limit_minutes', 'break_excess_minutes', 'break_compliance']);
        });
    }
};
