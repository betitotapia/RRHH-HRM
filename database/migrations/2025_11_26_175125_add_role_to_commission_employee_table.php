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
    Schema::table('commission_employee', function (Blueprint $table) {
        $table->string('role', 50)->nullable()->after('employee_id');
        // Opcional: fecha de incorporación
        // $table->date('joined_at')->nullable()->after('role');
    });
}

public function down(): void
{
    Schema::table('commission_employee', function (Blueprint $table) {
        $table->dropColumn('role');
        // $table->dropColumn('joined_at');
    });
}   
};