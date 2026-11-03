<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dateTime('requested_start_at')->nullable()->after('end_date');
            $table->dateTime('requested_end_at')->nullable()->after('requested_start_at');
            $table->string('evidence_path')->nullable()->after('observations');
            $table->string('evidence_original_name')->nullable()->after('evidence_path');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['requested_start_at', 'requested_end_at', 'evidence_path', 'evidence_original_name']);
        });
    }
};
