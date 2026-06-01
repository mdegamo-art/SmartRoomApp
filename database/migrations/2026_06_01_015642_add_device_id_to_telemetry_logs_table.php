<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('telemetry_logs') || Schema::hasColumn('telemetry_logs', 'device_id')) {
            return;
        }

        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('telemetry_logs') || !Schema::hasColumn('telemetry_logs', 'device_id')) {
            return;
        }

        Schema::table('telemetry_logs', function (Blueprint $table) {
            $table->dropColumn('device_id');
        });
    }
};
