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
     Schema::create('employees', function (Blueprint $table) {
        $table->id();

        // IMPORTANTE: usamos departament_id porque así lo tienes en tus otras tablas
        $table->foreignId('departament_id')
              ->constrained('departaments')
              ->cascadeOnDelete();

        $table->foreignId('position_id')
              ->constrained('positions')
              ->cascadeOnDelete();

        $table->string('employee_number')->unique();          // Número de Empleado

        $table->string('last_name_1');                        // Primer Apellido
        $table->string('last_name_2')->nullable();            // Segundo Apellido
        $table->string('first_names');                        // Nombres(s)

        $table->string('site')->nullable();                   // Sede

        $table->date('hire_date')->nullable();                // Ingreso
        $table->date('imss_date')->nullable();                // Alta al IMSS

        $table->decimal('net_salary', 10, 2)->nullable();     // Salario neto

        $table->string('gender', 20)->nullable();             // Sexo
        $table->string('disability')->nullable();             // Discapacidad

        $table->date('birth_date')->nullable();               // Fecha de Nacimiento
        $table->unsignedInteger('age')->nullable();           // Edad (puedes calcularla después por código)

        $table->string('phone', 20)->nullable();              // No. Telefónico
        $table->string('email')->nullable();                  // Correo Electrónico

        $table->string('curp', 18)->nullable();               // CURP
        $table->string('rfc', 13)->nullable();                // RFC
        $table->string('nss', 15)->nullable();                // NSS

        $table->string('marital_status')->nullable();         // Estado Civil
        $table->string('blood_type', 10)->nullable();         // Tipo de Sangre

        // Dirección
        $table->string('street')->nullable();                 // Calle (Dirección)
        $table->string('ext_number')->nullable();             // Número Exterior
        $table->string('int_number')->nullable();             // Número Interior
        $table->string('neighborhood')->nullable();           // Colonia
        $table->string('city')->nullable();                   // Municipio/Localidad
        $table->string('postal_code', 10)->nullable();        // Código Postal
        $table->string('state')->nullable();                  // Estado

        // Contactos de emergencia
        $table->string('emergency_contact_name_1')->nullable();
        $table->string('emergency_contact_phone_1', 20)->nullable();
        $table->string('emergency_contact_name_2')->nullable();
        $table->string('emergency_contact_phone_2', 20)->nullable();

        // Como en tus otros catálogos
        $table->boolean('active')->default(true);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
