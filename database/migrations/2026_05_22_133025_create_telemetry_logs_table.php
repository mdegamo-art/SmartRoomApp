<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('telemetry_logs')) {
            return;
        }

        Schema::create('telemetry_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->integer('light_level')->nullable();
            $table->boolean('motion_detected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_logs');
    }
};
