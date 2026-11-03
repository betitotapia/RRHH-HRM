<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // tipo de registro
            $table->enum('type', ['in', 'out'])->index();

            // fecha/hora real del marcaje
            $table->dateTime('registered_at')->index();

            // origen (fase 1 será 'web')
            $table->string('source', 30)->default('web')->index();

            // evidencia opcional para fase 1 (puede quedar null)
            $table->string('photo_path')->nullable();

            // info para auditoría
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            // evitar duplicados exactos
            $table->unique(['employee_id', 'type', 'registered_at'], 'uniq_employee_type_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};

