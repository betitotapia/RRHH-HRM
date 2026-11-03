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
         Schema::create('commission_employee', function (Blueprint $table) {
        $table->id();
        $table->foreignId('commission_id')->constrained()->onDelete('cascade');
        $table->foreignId('employee_id')->constrained()->onDelete('cascade');

        // Campos opcionales para futuro:
        // $table->string('role')->nullable();       // Presidente, Secretario, Vocal...
        // $table->date('joined_at')->nullable();    // Desde cuándo está en la comisión

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_employee');
    }
};
