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
           Schema::create('departaments', function (Blueprint $table) {
        $table->id();
        $table->string('name');               // Nombre del departamento (Recursos Humanos, Almacén, etc.)
        $table->string('code')->nullable();   // Clave interna opcional (RH, ALM, FIN)
        $table->text('description')->nullable(); // Descripción del depto
        $table->boolean('active')->default(true); // Activo / Inactivo
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departaments');
    }
};
