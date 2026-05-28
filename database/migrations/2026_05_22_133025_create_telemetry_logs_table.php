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
    Schema::table('telemetry_logs', function (Blueprint $table) {
        $table->float('temperature')->nullable();
        $table->float('humidity')->nullable();
        $table->integer('light_level')->nullable();
        $table->boolean('motion_detected')->default(false);
        $table->timestamp('logged_at')->nullable();

    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_logs');
    }
};
