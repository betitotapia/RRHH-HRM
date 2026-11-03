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
        if (!Schema::hasColumn('positions', 'level')) {
            Schema::table('positions', function (Blueprint $table) { $table->string('level', 50)->nullable()->after('code'); });
        }
        if (Schema::hasColumn('employees', 'level')) {
            Schema::table('employees', function (Blueprint $table) { $table->dropColumn('level'); });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('employees', 'level')) {
            Schema::table('employees', function (Blueprint $table) { $table->string('level', 50)->nullable()->after('first_names'); });
        }
        if (Schema::hasColumn('positions', 'level')) {
            Schema::table('positions', function (Blueprint $table) { $table->dropColumn('level'); });
        }
    }
};
