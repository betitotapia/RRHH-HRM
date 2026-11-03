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
       Schema::create('employee_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->onDelete('cascade');

    // 0=Domingo ... 6=Sábado
    $table->unsignedTinyInteger('day_of_week')->index();

    $table->time('start_time');          // hora de entrada
    $table->time('end_time')->nullable(); // hora de salida (opcional)
    $table->unsignedSmallInteger('tolerance_minutes')->default(10);

    $table->boolean('active')->default(true);
    $table->timestamps();

    $table->unique(['employee_id','day_of_week'], 'uniq_emp_day');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
    }
};
