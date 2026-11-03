<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('positions', 'department_id') && ! Schema::hasColumn('positions', 'departament_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->renameColumn('department_id', 'departament_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('positions', 'departament_id') && ! Schema::hasColumn('positions', 'department_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->renameColumn('departament_id', 'department_id');
            });
        }
    }
};
