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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('event_type', 30)->nullable()->after('type')->index();
            $table->foreignId('work_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('work_mode', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('distance_meters')->nullable();
            $table->boolean('in_geofence')->nullable()->index();
            $table->string('verification_method', 30)->nullable();
            $table->string('station_nonce', 64)->nullable()->unique();
            $table->string('punctuality', 20)->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('device_sn', 100)->nullable();
            $table->ipAddress('device_ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['work_location_id']);
            $table->dropColumn(['event_type','work_location_id','work_mode','latitude','longitude','distance_meters','in_geofence','verification_method','station_nonce','punctuality','late_minutes','notes','device_sn','device_ip']);
        });
    }
};
