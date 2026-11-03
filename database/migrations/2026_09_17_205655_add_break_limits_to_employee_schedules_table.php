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
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->unsignedSmallInteger('breakfast_minutes')->default(30)->after('tolerance_minutes');
            $table->unsignedSmallInteger('lunch_minutes')->default(60)->after('breakfast_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->dropColumn(['breakfast_minutes', 'lunch_minutes']);
        });
    }
};
