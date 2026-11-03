<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('termination_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('termination_type_id')->constrained()->restrictOnDelete();
            $table->date('termination_date');
            $table->date('last_working_day')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_original_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();
        DB::table('termination_types')->insert(array_map(fn ($name) => [
            'name' => $name, 'active' => true, 'created_at' => $now, 'updated_at' => $now,
        ], [
            'ABANDONO DE TRABAJO', 'DESPIDO', 'FINADO', 'RENUNCIA VOLUNTARIA',
            'RESCISIÓN DE CONTRATO', 'SIN DATOS', 'TÉRMINO DE CONTRATO', 'TÉRMINO DE LICITACIÓN',
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_terminations');
        Schema::dropIfExists('termination_types');
    }
};
