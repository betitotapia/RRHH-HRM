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
       Schema::create('positions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('department_id')
              ->constrained()
              ->cascadeOnDelete(); // si borras el depto, borra sus puestos

        $table->string('name');                 // Nombre del puesto (Auxiliar RH, Chofer, etc.)
        $table->string('code')->nullable();     // Clave interna (AUX-RH, CHOFER-01, etc.)
        $table->string('level', 50)->nullable();
        $table->decimal('base_salary', 10, 2)->nullable(); // Sueldo base aproximado
        $table->text('description')->nullable();
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
