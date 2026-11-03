<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn')->nullable();
            $table->string('device_ip')->nullable();
            $table->string('table_name')->nullable(); // "ATTLOG", "OPERLOG", etc.
            $table->longText('payload'); // contenido crudo que envía el equipo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
