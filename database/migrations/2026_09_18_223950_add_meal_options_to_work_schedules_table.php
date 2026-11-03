<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->boolean('has_breakfast')->default(true)->after('description');
            $table->boolean('has_lunch')->default(true)->after('has_breakfast');
        });
    }

    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn(['has_breakfast', 'has_lunch']);
        });
    }
};
