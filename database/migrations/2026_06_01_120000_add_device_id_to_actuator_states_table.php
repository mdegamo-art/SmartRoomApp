<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('actuator_states')) {
            return;
        }

        if (!Schema::hasColumn('actuator_states', 'device_id')) {
            Schema::table('actuator_states', function (Blueprint $table) {
                $table->string('device_id')->default('default')->after('id');
            });
        }

        try {
            Schema::table('actuator_states', function (Blueprint $table) {
                $table->dropUnique(['actuator_name']);
            });
        } catch (\Throwable) {
            // Index may already be dropped.
        }

        try {
            Schema::table('actuator_states', function (Blueprint $table) {
                $table->unique(['device_id', 'actuator_name']);
            });
        } catch (\Throwable) {
            // Composite unique may already exist.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('actuator_states') || !Schema::hasColumn('actuator_states', 'device_id')) {
            return;
        }

        try {
            Schema::table('actuator_states', function (Blueprint $table) {
                $table->dropUnique(['device_id', 'actuator_name']);
            });
        } catch (\Throwable) {
        }

        Schema::table('actuator_states', function (Blueprint $table) {
            $table->unique('actuator_name');
            $table->dropColumn('device_id');
        });
    }
};
