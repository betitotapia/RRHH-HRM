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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('code', 30)->unique(); $table->text('description')->nullable(); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id(); $table->foreignId('work_schedule_id')->constrained()->cascadeOnDelete(); $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time'); $table->time('end_time'); $table->unsignedSmallInteger('tolerance_minutes')->default(10);
            $table->unsignedSmallInteger('breakfast_minutes')->default(30); $table->unsignedSmallInteger('lunch_minutes')->default(60); $table->timestamps();
            $table->unique(['work_schedule_id','day_of_week']);
        });
        Schema::table('employees', function (Blueprint $table) { $table->foreignId('work_schedule_id')->nullable()->after('work_location_id')->constrained()->nullOnDelete(); });
        Schema::dropIfExists('employee_schedules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id(); $table->foreignId('employee_id')->constrained()->cascadeOnDelete(); $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('start_time'); $table->time('end_time')->nullable(); $table->unsignedSmallInteger('tolerance_minutes')->default(10);
            $table->unsignedSmallInteger('breakfast_minutes')->default(30); $table->unsignedSmallInteger('lunch_minutes')->default(60);
            $table->boolean('active')->default(true); $table->timestamps(); $table->unique(['employee_id','day_of_week']);
        });
        Schema::table('employees', function (Blueprint $table) { $table->dropForeign(['work_schedule_id']); $table->dropColumn('work_schedule_id'); });
        Schema::dropIfExists('work_schedule_days'); Schema::dropIfExists('work_schedules');
    }
};
